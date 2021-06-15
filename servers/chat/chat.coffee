# Initialization
express				= require 'express'
http				= require 'http'
cluster				= require 'cluster'
phpjs				= require './phpjs'
util				= require 'util'
fs					= require 'fs'
crypto				= require 'crypto'
sio					= require 'socket.io'
emoticons			= require './emoticons'
bbcodes	    		= require './bbcodes'
config				= require './config'
db					= require './db'

# Chat variables
players				= {}
players_by_name		= {}
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

	db.connect config.db

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
	diff	= (diff - (milliseconds = diff%1000)) / 1000
	diff	= (diff - (seconds = diff%60)) / 60
	diff	= (diff - (minutes = diff%60)) / 60
	days	= (diff - (hours = diff%24)) / 24

	return seconds

io.sockets.on 'connection', (socket) ->
	# We got a user connection
	socket.on 'register', (data) ->
		# We should decrypt the user data
		data	= decrypt_json data.data

		# If the user data is valid
		if data
			# We subscribe the user to the channels
			socket.join 'faction_'	+ data.faction
			socket.join 'guild_'	+ data.guild
			socket.join 'battle_'	+ data.battle
			socket.join 'world_0'
			socket.join 'system_0'

			data.last_activity							= new Date()
			players[data.uid]							= data
			players_by_name[data.name.toLowerCase()]	= players[data.uid]
			last_messages[data.user_id]					= null

			socket._uid									= data.uid
			socket._player								= players[data.uid]

			counters[data.uid]							= true;

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
		else
			socket.emit 'broadcast',
				from: 'Sistema',
				message: 'Falha ao autenticar no chat!',
				channel: 'warn'

			return

	# Mark a private message as read
	socket.on 'pvt-was-read', (data) ->
		if privates[@_uid] && privates[@_uid][data.index]
			delete privates[@_uid][data.index]

	# User want to send a message
	socket.on 'message', (data) ->
		player	= players[@_uid]

		unless player
			console.log 'invalid user trying to send a message'
			return

		# Word filter for non-gm users
		unless player.gm
			for i, word of blacklist
				if data.message.match new RegExp word, 'img'
					socket.emit 'broadcast',
						from: 'Sistema',
						message: 'A mensagem contem palavras impróprias!',
						channel: 'warn'

					return

		data.message = phpjs.htmlspecialchars data.message unless player.gm

		if data.channel == 'system' && !player.gm
			return

		# User is sending a private message
		if data.channel == 'private'
			if isNaN(data.dest)
				data.dest	= data.dest.replace /\s/, ''

				unless players_by_name[data.dest.toLowerCase()]
					socket.emit 'broadcast',
						from: 'Sistema',
						message: 'Usuário "' + data.dest + '" indisponível para enviar mensagens',
						channel: 'warn'

					return

				data.dest	= players_by_name[data.dest.toLowerCase()].uid

			if players[data.dest]
				if players[data.dest].user_id == player.user_id
					socket.emit 'broadcast',
						from: 'Sistema',
						message: 'Você não pode enviar uma mensagem privada para você mesmo!',
						channel: 'warn'

					return
				else
					db.query "SELECT `id` FROM `chat_blocked` WHERE `user_id` = " + players[data.dest].user_id + " AND `user_blocked` = " + player.user_id, (error, results, fields) ->
						if results.length
							socket.emit 'broadcast',
								from: 'Sistema',
								message: 'Você não pode enviar mensagens para esse usuário!',
								channel: 'warn'

							return
						else
							privates[data.dest]	= {} unless privates[data.dest]
							privates[data.dest][Math.random() * 512384] = name: player.name, message: data.message, id: player.uid

							return


		if data.channel == 'block'
			p	= players_by_name[data.message.toLowerCase()]

			unless p
				socket.emit 'broadcast',
					from: 'Sistema',
					message: 'Usuário "' + data.message + '" não encontrado!',
					channel: 'warn'

				return

			if p.gm
				socket.emit 'broadcast',
					from: 'Sistema',
					message: 'Você não pode bloquear um membro da Administração!',
					channel: 'warn'

				return

			if p.user_id == player.user_id
				socket.emit 'broadcast',
					from: 'Sistema',
					message: 'Você não pode bloquear a sua propria conta!',
					channel: 'warn'

				return

			db.query 'INSERT INTO `chat_blocked` (`user_id`, `user_blocked`) VALUES (' + player.user_id + ', ' + p.user_id + ')'

			socket.emit 'broadcast',
				from: 'Sistema',
				message: 'Usuário "' + data.message + '" bloqueado com sucesso!',
				channel: 'success'
			return

		switch (data.channel)
			when 'faction'	then channel_id = player.faction
			when 'guild'	then channel_id = player.guild
			when 'battle'	then channel_id = player.battle

		if !channel_id and !['world', 'system'].contains(data.channel)
			console.log 'channel error'

			return

		if player.user_id && banned[player.user_id]
			socket.emit 'broadcast',
				from: 'Sistema',
				message: 'Você foi banido do chat!',
				channel: 'warn'

			return

		if !player.gm
			now	= new Date()

			if (last_messages[player.user_id] && diff_in_secs(last_messages[player.user_id], now) < 10)
				socket.emit 'broadcast',
					from: 'Sistema',
					message: 'Você deve aguardar 10 segundos antes de enviar uma nova mensagem',
					channel: 'warn'

				return

			last_messages[player.user_id]	= now;

			# Character limtit for non-gm users
			data.message	= data.message.substr(0, user_message_size)

		data.message	= bbcodes.parse(data.message, player.gm)
		data.message	= emoticons.parse(data.message, player.gm)

		channel_id		= 0 unless channel_id
		broadcast		= from: player.name, message: data.message, channel: data.channel, channel_id: channel_id, id: player.uid, user_id: player.user_id, gm: player.gm, when: new Date()

		if data.channel != 'faction'
			broadcast.color	= player.color
			broadcast.icon	= player.icon

		if (data.channel == 'guild' && player.guild_owner)
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

	socket.on 'blocked-query', (data) ->
		return unless players[@_uid]
		user_id = players[@_uid].user_id

		users	= []
		db.query 'SELECT `user_blocked` FROM `chat_blocked` WHERE `user_id` = ' + user_id, (error, results, fields) ->
			for row in results
				users.push row.user_blocked

		socket.emit 'blocked-broadcast', users

	# User queries for private messages
	socket.on 'pvt-query', (data) ->
		return unless players[@_uid]

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
		return unless players[@_uid]

		counters[@_uid]	= false

		socket.leave 'faction_'	+ players[@_uid].faction
		socket.leave 'guild'	+ players[@_uid].guild
		socket.leave 'battle_'	+ players[@_uid].battle
		socket.leave 'world_0'
		socket.leave 'system_0'

setInterval -> # Blacklist timer
	blacklist	= []
	db.query 'SELECT * FROM `chat_word_blacklist`', (error, words, fields) ->
		for row in words
			blacklist.push row.expr
, 2000

setInterval -> # Ban Hammer Timer =D
	banned	= []
	db.query 'SELECT * FROM `chat_banned`', (error, users, fields) ->
		for row in users
			banned[row.user_id]	= true
, 5000

setInterval -> # Channel GC timer
	return unless channels['battle']?

	proc	= (key, channel) ->
		process.nextTick -> delete channels['battle'][key]

	for key, channel of channels['battle']
		past	= new Date((new Date()).setMinutes((new Date()).getMinutes() - 30));

		proc(key, channel) if channel.last < past
, 5000

# setInterval -> # Played time
# 	proc	= (user) ->
# 		process.nextTick ->
# 			db.query 'UPDATE `played_time` SET `minutes` = `minutes` + 1 WHERE `player_id` = ' + user

# 	for user, state of counters
# 		proc user if state
# , 60000

# Startup =)
bootstrap()