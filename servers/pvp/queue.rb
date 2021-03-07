# Gem dependent
require 'bunny'					# bunny
require 'mysql2'				#mysql2
require 'active_support/all'	#active_support

# Internal extensions
require 'thread'
require 'json'
require 'securerandom'

@semaphore	= Mutex.new
@connection	= Bunny.new
@connection.start

@channel		= @connection.create_channel
@queue			= @channel.queue('allstars_pvp_queue')
@match_size		= 2
@players		= {}
@queues			= {}

# Database settings
@db				= {
	'host'	=> 'localhost',
	'user'	=> 'root',
	'pass'	=> 'SugoiG@m3',
	'name'	=> 'aasg'
}

# PvP range settings
level_range		= 50;

Thread.abort_on_exception = true

begin
	@mysql		= Mysql2::Client.new host: @db['host'], username: @db['user'], password: @db['pass'], reconnect: true, init_command: "USE #{@db['name']};"
	@mysql.query "USE #{@db['name']};"

	puts "[+] Main mysql connection started"
rescue Mysql2::Error => e
	puts "[+] MySQL Error #{e.errno} - #{e.error}"
end

puts "[+] Waiting for players..."

@queue.subscribe(block: true) do |delivery_info, properties, body|
	current_player	= JSON.parse(body)

	case current_player['method']
		when 'enter_queue'
			@players[current_player['id']]	= current_player if @players[current_player['id']].nil?

			if @players.keys.length >= @match_size
				@players.keys.each do |key|
					player	= @players[key]

					next if key == current_player['id']
					next unless player['queue_id'].nil?

					# Battle counter
					battle_counter1	= @mysql.query "SELECT COUNT(id) AS max FROM player_battle_pvps WHERE player_id=#{player['id']} AND enemy_id=#{current_player['id']}"
					battle_counter2	= @mysql.query "SELECT COUNT(id) AS max FROM player_battle_pvps WHERE enemy_id=#{player['id']} AND player_id=#{current_player['id']}"
					battle_total	= battle_counter1.to_a[0]['max'].to_i + battle_counter2.to_a[0]['max'].to_i

					puts " - Found count of #{battle_total} against #{player['id']} and #{current_player['id']}"

					next if battle_total >= 5

					uuid	= SecureRandom.uuid
					
					if current_player['level'].to_i >= player['level'].to_i - level_range && current_player['level'].to_i <= player['level'].to_i + level_range
						puts 'Match found'

						@queues[uuid]						= {}
						@queues[uuid][player['id']]			= {accepted: false, canceled: false}
						@queues[uuid][current_player['id']]	= {accepted: false, canceled: false}

						@players[current_player['id']]['queue_id']	= uuid
						@players[player['id']]['queue_id']			= uuid

						thread	= Thread.new do
							puts 'Thread started'

							begin
								mysql	= Mysql2::Client.new host: @db['host'], username: @db['user'], password: @db['pass'], init_command: "USE #{@db['name']};"
								mysql.query  "USE #{@db['name']};"
								
								puts 'Theard mysql connection started'
							rescue Mysql2::Error => e
								puts "MySQL Error #{e.errno} - #{e.error}"
							end
							
							timer		= 30
							timeout		= true
							accepted	= false

							time_queue	= Time.now.to_i + timer

							mysql.query "UPDATE players SET pvp_queue_found='#{time_queue}' WHERE id IN(#{current_player['id']}, #{player['id']})"

							while timer > 0 do
								sleep(1)
								should_break	= false

								@semaphore.synchronize do
									if @queues[uuid][player['id']][:accepted] && @queues[uuid][current_player['id']][:accepted]
										puts "ACCEPT!"

										who_start	= current_player['init'] > player['init'] ? current_player['id'] : player['id']

										mysql.query "INSERT INTO battle_pvps(battle_type_id, player_id, enemy_id, current_id, last_atk) VALUES(#{current_player['battle_type_id']}, #{current_player['id']}, #{player['id']}, #{who_start}, NOW())"
										mysql.query "UPDATE players SET pvp_queue_found=NULL, is_pvp_queued=0, battle_pvp_id=#{mysql.last_id} WHERE id IN(#{current_player['id']}, #{player['id']})"
										mysql.query "INSERT INTO player_battle_pvps(player_id, enemy_id) VALUES(#{current_player['id']}, #{player['id']})"
										
										accepted		= true
										should_break	= true
									end

									if @queues[uuid][player['id']][:canceled] || @queues[uuid][current_player['id']][:canceled]
										puts "CANCEL!"

										timeout			= false
										should_break	= true
									end
								end

								break if should_break

								timer	-= 1

								#puts 'Timer ' + timer.to_s
							end

							unless accepted
								mysql.query "UPDATE players SET pvp_queue_found=NULL WHERE id IN(#{current_player['id']}, #{player['id']})"

								@semaphore.synchronize do
									@queues[uuid].keys.each do |queue_key|
										if timeout
											# Was timeout, but if player have choosend to accept or cancel, he'll be still queued
											if @queues[uuid][queue_key][:accepted] || @queues[uuid][queue_key][:canceled]
												begin
													@players[queue_key]['queue_id']	= nil
												rescue
													puts 'Player queue key not found after timeouting a match'
												end
											else
												@players.delete_if { |k, p|
													k == queue_key
												}

												mysql.query "UPDATE players SET is_pvp_queued=0 WHERE id=#{queue_key}"
											end
										else
											begin
												@players[queue_key]['queue_id']	= nil
											rescue
												puts 'Player queue key not found after accepting a match'
											end
										end
									end

									@queues.delete_if { |key, v| key == uuid }
								end

								if timeout
									puts 'Timeout'
								else
									puts 'Cancel'
								end
							else
								@queues.delete_if { |k, v| k == uuid }
								@players.delete_if { |k, p| [current_player['id'], player['id']].include?(k) }
							end

							#@semaphore.synchronize do
							#	puts @players.inspect
							#	puts @queues.inspect
							#end

							mysql.close
						end

						thread.run
					end
				end
			end

		when 'exit_queue'
			begin
				player	= @players[current_player['id']]

				if player['queue_id']
					@queues[player['queue_id']][player['id']][:canceled]	= true
				else
					@players.delete_if{ |k, v| k == current_player['id'] }
				end

				@mysql.query "UPDATE players SET pvp_queue_found=NULL, is_pvp_queued=0 WHERE id=#{current_player['id']}"
				puts "Player cancelled"
			rescue
				@mysql.query "UPDATE players SET pvp_queue_found=NULL, is_pvp_queued=0 WHERE id=#{current_player['id']}"
				puts "Failure on exit"
			end

		when 'accept_queue'
			begin
				player													= @players[current_player['id']]
				@queues[player['queue_id']][player['id']][:accepted]	= true

				puts "Player aceepted"
			rescue
				@mysql.query "UPDATE players SET pvp_queue_found=NULL, is_pvp_queued=0 WHERE id=#{current_player['id']}"
				puts "Failure on accept"
			end
	end

	puts "Player queue size: " + @players.keys.size.to_s
end