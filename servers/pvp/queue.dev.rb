# Gem dependent
require 'bunny'					# bunny
require 'mysql2'				# mysql2
require 'active_support/all'	# active_support

# Internal extensions
require 'thread'
require 'json'
require 'securerandom'

@semaphore		= Mutex.new
@connection		= Bunny.new
@connection.start

@channel		= @connection.create_channel
@queue			= @channel.queue('allstars_queue')
@match_size		= 2
@players		= {}
@queues			= {}

# Database settings
@db				= {
	'host'	=> 'localhost',
	'user'	=> 'root',
	'pass'	=> '',
	'name'	=> 'allstars_db'
}

# PvP range settings
level_range		= 5;

Thread.abort_on_exception = true

begin
	@mysql		= Mysql2::Client.new host: @db['host'], username: @db['user'], password: @db['pass'], reconnect: true, init_command: "USE `#{@db['name']}`;"
	@mysql.query "USE `#{@db['name']}`;"
rescue Mysql2::Error => e
	puts "MySQL Error #{e.errno} - #{e.error}"
end

puts "PvP Battle queue was successfully started!"
puts "Waiting for players..."

@queue.subscribe(block: true) do |delivery_info, properties, body|
	current_player	= JSON.parse(body)

	case current_player['method']
		when 'enter_queue'
			puts "[#{current_player['id']}]#{current_player['name']} entered the queue."

			@players[current_player['id']]	= current_player if @players[current_player['id']].nil?

			if @players.keys.length >= @match_size
				player		= nil
				choosen		= nil

				allplayers	= []
				outrange	= []
				availables	= []
				victorious	= []
				losers		= []

				min_level	= current_player['level'] - level_range
				max_level	= current_player['level'] + level_range

				battle_type	= current_player['battle_type_id'];

				@players.each do |key, player|
					next if player['id'] == current_player['id']

					allplayers.push(player)

					next unless player['queue_id'].nil?

					if player['level'] >= min_level && player['level'] <= max_level
						availables.push(player)

						if player['won'].to_i > 0
							victorious.push(player)
						elsif player['won'].to_i < 0
							losers.push(player)
						end
					else
						outrange.push(player)
					end
				end

				if current_player['won'].to_i > 0 && victorious.any?
					choosen = victorious.sample
				elsif current_player['won'].to_i < 0 && losers.any?
					choosen = losers.sample
				else
					choosen = availables.sample
				end

				unless choosen
					choosen = outrange.sample
				end

				if choosen
					puts "Match found! [#{choosen['id']}]#{choosen['name']} x [#{current_player['id']}]#{current_player['name']}"

					# Battle counter
					battle_counter1	= @mysql.query "SELECT COUNT(`id`) AS `max` FROM `player_battle_pvps` WHERE `player_id` = #{choosen['id']} AND `enemy_id` = #{current_player['id']} AND `created_at` >= DATE_SUB(NOW(), INTERVAL 1 HOUR)"
					battle_counter2	= @mysql.query "SELECT COUNT(`id`) AS `max` FROM `player_battle_pvps` WHERE `enemy_id` = #{choosen['id']} AND `player_id` = #{current_player['id']} AND `created_at` >= DATE_SUB(NOW(), INTERVAL 1 HOUR)"
					battle_total	= battle_counter1.to_a[0]['max'].to_i + battle_counter2.to_a[0]['max'].to_i

					if battle_total > 0
						puts "We found #{battle_total} battles between [#{choosen['id']}]#{choosen['name']} and [#{current_player['id']}]#{current_player['name']} in the last hour"
					end
					next if battle_total >= 5

					uuid = SecureRandom.uuid

					@queues[uuid]						= {}
					@queues[uuid][choosen['id']]		= {accepted: false, canceled: false}
					@queues[uuid][current_player['id']]	= {accepted: false, canceled: false}

					@players[current_player['id']]['queue_id']	= uuid
					@players[choosen['id']]['queue_id']			= uuid

					thread	= Thread.new do
						begin
							mysql	= Mysql2::Client.new host: @db['host'], username: @db['user'], password: @db['pass'], init_command: "USE `#{@db['name']}`;"
							mysql.query  "USE `#{@db['name']}`;"
						rescue Mysql2::Error => e
							puts "MySQL Error #{e.errno} - #{e.error}"
						end
						
						timer		= 30
						timeout		= true
						accepted	= false
						time_queue	= Time.now.to_i + timer

						mysql.query "UPDATE `players` SET `pvp_queue_found` = '#{time_queue}' WHERE `id` IN(#{current_player['id']}, #{choosen['id']})"

						while timer > 0 do
							sleep(1)
							should_break	= false

							@semaphore.synchronize do
								if @queues[uuid][choosen['id']][:accepted] && @queues[uuid][current_player['id']][:accepted]
									if current_player['init'] > choosen['init']
										who_start = current_player['id']
									elsif current_player['init'] < choosen['init']
										who_start = choosen['id']
									else
										who_start = [ choosen['id'], current_player['id']].sample
									end

									mysql.query "INSERT INTO `battle_pvps` (`battle_type_id`,`player_id`,`enemy_id`,`current_id`,`last_atk`) VALUES(#{battle_type},#{current_player['id']},#{choosen['id']},#{who_start},NOW())"

									battle_id		= mysql.last_id
									mysql.query	"UPDATE `players` SET `pvp_queue_found` = NULL, `is_pvp_queued` = 0, `battle_pvp_id` = #{battle_id} WHERE `id` IN(#{current_player['id']}, #{choosen['id']})"
									mysql.query	"INSERT INTO `player_battle_pvps` (`player_id`,`enemy_id`) VALUES(#{current_player['id']}, #{choosen['id']})"
									
									accepted		= true
									should_break	= true

									puts "Starting the battle (#{battle_id}): [#{current_player['id']}]#{current_player['name']} x [#{choosen['id']}]#{choosen['name']}"
								end

								if @queues[uuid][choosen['id']][:canceled] || @queues[uuid][current_player['id']][:canceled]
									if (@queues[uuid][choosen['id']][:canceled])
										@players[current_player['id']]['queue_id']	= nil
									elsif @queues[uuid][current_player['id']][:canceled]
										@players[choosen['id']]['queue_id']	= nil
									end

									timeout			= false
									should_break	= true
								end
							end

							break if should_break

							timer	-= 1
						end

						unless accepted
							mysql.query "UPDATE `players` SET `pvp_queue_found` = NULL WHERE `id` IN(#{current_player['id']}, #{choosen['id']})"

							@semaphore.synchronize do
								@queues[uuid].keys.each do |player_id|
									if timeout
										# Was timeout, but if player have choosend to accept, he'll be still queued
										if @queues[uuid][player_id][:accepted]
											begin
												@players[player_id]['queue_id']	= nil
											rescue
												puts "Queue key of #{player_id} not found after timeouting a match."
											end
										else
											@players.delete_if{ |k, v| k == player_id }

											puts "Player queue size: " + @players.keys.size.to_s

											mysql.query "UPDATE `players` SET `is_pvp_queued` = 0, `pvp_queue_found` = NULL WHERE `id` = #{player_id}"
										end
									else
										if @queues[uuid][player_id][:accepted]
											begin
												@players[player_id]['queue_id']	= nil
											rescue
												puts "Queue key of #{player_id} not found after accepting a match."
											end
										end
									end
								end

								@queues.delete_if { |key, v| key == uuid }
							end
						else
							@queues.delete_if { |k, v| k == uuid }
							@players.delete_if { |k, p| [current_player['id'], choosen['id']].include?(k) }
						end

						mysql.close
					end

					thread.run
				end
			end

		when 'exit_queue'
			begin
				player	= @players[current_player['id']]

				if player['queue_id']
					@queues[player['queue_id']][player['id']][:canceled]	= true

					puts "[#{player['id']}]#{player['name']} refused the battle!"
				else
					puts "[#{player['id']}]#{player['name']} left the queue."
				end

				@players.delete_if{ |k, v| k == player['id'] }
			rescue
				puts "Failure on exit"
			end

		when 'accept_queue'
			begin
				player													= @players[current_player['id']]
				@queues[player['queue_id']][player['id']][:accepted]	= true

				puts "[#{player['id']}]#{player['name']} accepted the battle!"
			rescue
				puts "Failure on accept"

				# @mysql.query "UPDATE `players` SET `pvp_queue_found` = NULL WHERE `id` = #{current_player['id']}"
			end
	end
end