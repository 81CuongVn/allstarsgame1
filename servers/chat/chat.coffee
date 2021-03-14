# Initialization --->
express				= require 'express'
http				= require 'http'
cluster				= require 'cluster'
phpjs				= require './phpjs'
util				= require 'util'
fs					= require 'fs'
crypto				= require 'crypto'
sio					= require('socket.io')
emoticons			= require './emoticons'
# db					= require('mysql-native').createTCPClient() # When using this one with node-cluster, this should stay in the forked code
# db_sync				= require './db_sync'

# Mysql config
# mysql_config		= db: 'narutoga_prod', user: 'narutoga_prod', password: 'xc88%a3j'

users				= {}
users_by_name		= {}
channels			= {}
privates			= {}
counters			= {}
blacklist			= []
banned				= []
last_messages		= {}

# How many messages the chat should have
max_messages		= 100
user_message_size	= 140

# Global server variables
express_server		= express()
server				= http.createServer express_server
io					= sio.listen server


Array::contains	= (k) ->
	for i, item of @
		if item == k
			return true
	
	return false

bootstrap	= ->
	channels['system']	= []
	channels['world']	= []
	
	# db.auth mysql_config.db, mysql_config.user, mysql_config.password
	# db_sync.connect mysql_config

	server.listen 2934

	console.log "+ Chat Thread Started on " + server.address().address + " at port " + server.address().port

decrypt_json	= (encrypted) ->
	key				= 'YAn8yK930907L2KUTnnSqLDuI6jl0G9N'
	iv				= key.substr 0, 16

	try
		decipher	= crypto.createDecipheriv 'aes-256-cbc', key, iv
		decrypted	= decipher.update encrypted, 'base64', 'utf8'
		decrypted	+= decipher.final 'utf8'

		output		= JSON.parse decrypted
	catch e
		console.log 'descrypt error:' + e
		output		= false

	return output

diff_in_secs	= (d1, d2) ->
	diff			= d2 - d1
	sign			= if diff < 0 then -1 else 1
	milliseconds	= 0
	seconds			= 0
	minutes			= 0
	hours			= 0
	days			= 0
	
	diff	/= sign
	diff	= (diff-(milliseconds = diff%1000))/1000
	diff	= (diff-(seconds = diff%60))/60
	diff	= (diff-(minutes = diff%60))/60
	days	= (diff-(hours = diff%24))/24

	return seconds

io.sockets.on 'connection', (socket) ->
	# We got a user connection
	socket.on 'register', (data) ->
		# console.log data

		# We should decrypt the user data
		data	= decrypt_json data.data
		# console.log data

		# If the user data is valid
		if data
			# We subscribe the user to the channels
			socket.join 'faction_' + data.faction
			socket.join 'guild_' + data.guild
			socket.join 'battle_' + data.battle
			socket.join 'world_0'
			socket.join 'system_0'
			
			data.last_activity						= new Date()
			users[data.uid]							= data
			users_by_name[data.name.toLowerCase()]	= users[data.uid]

			socket._uid								= data.uid
			socket._user							= users[data.uid]

			counters[data.uid]						= true;

			# For every new connection we should broadcast the message history for every channel(now it's aync bitch)
			process.nextTick ->
				['faction', 'guild', 'battle', 'world', 'system'].forEach (channel) ->
					if ['world', 'system'].contains(channel)
						channel_id	= 0
					else
						channel_id	= data[channel]
	
					if channels[channel] && channels[channel][channel_id]
						for i, message of channels[channel][channel_id].messages
							return if message instanceof Function
	
							socket.emit 'broadcast', message

	# Mark a private message as read
	socket.on 'pvt-was-read', (data) ->
		if privates[@_uid] && privates[@_uid][data.index]
			delete privates[@_uid][data.index] 

	# User want to send a message
	socket.on 'message', (data) ->
		user	= users[@_uid]
		
		unless user
			console.log 'invalid user trying to send a message'
			return

		# Word filter for non-gm users
		unless user.gm		
			for i, word of blacklist
				if data.message.match new RegExp word, 'img'
					socket.emit 'broadcast',
						from: 'Sistema',
						message: 'A mensagem contem palavras impróprias',
						channel: 'warn'
					
					return

		data.message = phpjs.htmlspecialchars data.message unless user.gm

		if data.channel == 'system' && !user.gm
			return

		# User is sending a private message
		if data.channel == 'private'
			if isNaN(data.dest)
				data.dest	= data.dest.replace /\s/, ''
			
				unless users_by_name[data.dest.toLowerCase()]
					socket.emit 'broadcast',
						from: 'Sistema',
						message: 'Usuário "' + data.dest + '" indisponível para enviar mensagens',
						channel: 'warn'					
					
					return

				data.dest	= users_by_name[data.dest.toLowerCase()].uid
			
			# if users[data.dest]
			# 	if(db_sync.row_of('SELECT id FROM chat_blocked WHERE id_user=' + users[data.dest].user_id + ' AND id_user_blocked=' + user.user_id))
			# 		socket.emit 'broadcast',
			# 			from: 'Sistema',
			# 			message: 'Você não pode enviar mensagens para esse usuário',
			# 			channel: 'warn'					
				
			# 		return					

			privates[data.dest]	= {} unless privates[data.dest]
			privates[data.dest][Math.random() * 512384] = name: user.name, message: data.message, id: user.uid
			
			return
		
		if data.channel == 'block'
			u	= users_by_name[data.message.toLowerCase()]
		
			unless u
				socket.emit 'broadcast',
					from: 'Sistema',
					message: 'Usuário "' + data.message + '" não encontrado',
					channel: 'warn'					
				
				return

			if u.gm
				socket.emit 'broadcast',
					from: 'Sistema',
					message: 'Você não pode bloquear esse jogador pois ele pertence a STAFF',
					channel: 'warn'					
				
				return
				
			# db_sync.query_only('INSERT INTO chat_blocked(player_id, player_blocked) VALUES(' + user.user_id + ', ' + u.user_id + ')');

			socket.emit 'broadcast',
				from: 'Sistema',
				message: 'Usuário "' + data.message + '" bloqueado com sucesso',
				channel: 'warn'					

		switch(data.channel)
			when 'faction'	then channel_id = user.faction
			when 'guild'	then channel_id = user.guild
			when 'battle'	then channel_id = user.battle

		if !channel_id and !['world', 'system'].contains(data.channel)
			console.log 'channel error'
			
			return

		if user.user_id && banned[user.user_id]
			socket.emit 'broadcast',
				from: 'Sistema',
				message: 'Você foi banido do chat',
				channel: 'warn'
			
			return
		
		if user.gm
			data.message	= emoticons.parse(data.message, user.gm)			
		else
			now	= new Date()

			if(last_messages[user.user_id] && diff_in_secs(last_messages[user.user_id], now) < 10)
				socket.emit 'broadcast',
					from: 'Sistema',
					message: 'Você deve aguardar 10 segundos antes de enviar uma nova mensagem',
					channel: 'warn'

				return

			last_messages[user.user_id]	= now;

			if user.gm
				user_message_size = 500

			# Character limtit for non-gm users
			data.message	= emoticons.parse(data.message.substr(0, user_message_size), user.gm)

		channel_id		= 0 unless channel_id
		broadcast		= from: user.name, message: data.message, channel: data.channel, channel_id: channel_id, id: user.uid, user_id: user.user_id, gm: user.gm, when: new Date()

		if data.channel == 'faction'
			broadcast.color	= user.color
			broadcast.icon	= user.icon

		if (data.channel == 'guild' && user.guild_owner)
			broadcast.color	= '#BF2121'

		# Channel allocation
		channels[data.channel]				= {} unless channels[data.channel]
		channels[data.channel][channel_id]	= {last: new Date(), messages: []} unless channels[data.channel][channel_id]
		
		# Updates the channel with the last message
		channels[data.channel][channel_id].messages.last	= new Date()
		channels[data.channel][channel_id].messages.push broadcast

		# Message stack cleanup
		if channels[data.channel][channel_id].messages.length > max_messages
			message_diff	= channels[data.channel][channel_id].messages.length - max_messages

			[0..message_diff].forEach -> channels[data.channel][channel_id].messages.shift()

		io.sockets.in(data.channel + '_' + channel_id).emit('broadcast', broadcast)
	
	# socket.on 'blocked-query', (data) ->
	# 	return unless users[@_uid]

	# 	players	= []
		
	# 	db_sync.result_of('SELECT player_blocked FROM chat_blocked WHERE player_id=' + users[@_uid].user_id).forEach (row) ->
	# 		players.push row.player_blocked
		
	# 	socket.emit 'blocked-broadcast', players
	
	# User queries for private messages
	socket.on 'pvt-query', (data) ->
		return unless users[@_uid]

		broadcast	= []
		
		for i, message of privates[@_uid]
			return unless message
		
			broadcast.push
				from:		message.name,
				message:	message.message,
				id:			message.id,
				index:		i

		if broadcast.length
			@emit 'pvt-broadcast', broadcast
	
	socket.on 'disconnect', ->
		return unless users[@_uid]

		counters[@_uid]	= false
	
		socket.leave 'faction_' + users[@_uid].faction
		socket.leave 'guild' + users[@_uid].guild
		socket.leave 'battle_' + users[@_uid].battle
		socket.leave 'world_0'
		socket.leave 'system_0'

# setInterval -> # Blacklist timer
# 	blacklist	= []

# 	words	= db.query 'SELECT * FROM chat_word_blacklist'
# 	words.addListener 'row', (row) ->
# 		blacklist.push row.expr
# , 2000

# setInterval -> # Ban Hammer Timer =D
# 	banned	= []

# 	banlist	= db.query 'SELECT * FROM chat_banned'
# 	banlist.addListener 'row', (row) ->
# 		banned[row.player_id]	= true	
# , 5000

setInterval -> # Channel GC timer
	return unless channels['battle']?

	proc	= (key, channel) ->
		process.nextTick -> delete channels['battle'][key]
	
	for key, channel of channels['battle']
		past	= new Date((new Date()).setMinutes((new Date()).getMinutes() - 30));
		
		proc(key, channel) if channel.last < past
, 5000

# setInterval -> # Played time
# 	console.log 'Played time'

# 	proc	= (user) ->
# 		process.nextTick ->
# 			db.query('UPDATE played_time SET minutes=minutes+1 WHERE player_id=' + user)

# 	for user, state of counters
# 		proc user if state
# , 60000


# Startup =)
bootstrap()
