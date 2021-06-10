// Initialization
const express = require('express');
const http = require('http');
const cluster = require('cluster');
const phpjs = require('./phpjs');
const util = require('util');
const fs = require('fs');
const crypto = require('crypto');
const sio = require('socket.io');
const emoticons = require('./emoticons');
const config = require('./config');
const db = require('./db');

// Chat variables
const players = {}
const playersByName = {}
const channels = {}
const privates = {}
const counters = {}
let blacklist = []
let banned = []
const lastMessages = {}

// How many messages the chat should have
const maxMessages = 100
let userMessageSize = 140

// Global server variables
const expressServer = express();
const server = http.createServer(expressServer);
const io = sio.listen(server);


const bootstrap = () => {
	channels.system = [];
	channels.world = [];

	db.connect(config.db);

	server.listen(2934);

	console.log(`Process started with PID ${process.pid}`);
};

const decryptJson = (encrypted) => {
	const key = 'YAn8yK930907L2KUTnnSqLDuI6jl0G9N'
	const iv = key.substr(0, 16);

	let output;
	try {
		const decipher = crypto.createDecipheriv('aes-256-cbc', key, iv);
		let decrypted = decipher.update(encrypted, 'base64', 'utf8');
		decrypted += decipher.final('utf8');

		output = JSON.parse(decrypted);
	} catch (e) {
		console.lo('Decrypt ERROR: ' + e);
		output = false
	}

	return output;
};

const diffInSeconds = (d1, d2) => {
	let diff = d2 - d1;
	let milliseconds = 0;
	let seconds = 0;
	let minutes = 0;
	let hours = 0;
	let days = 0;
	const sign = diff < 0 ? -1 : 1;

	diff /= sign;
	diff = (diff - (milliseconds = diff % 1000)) / 1000;
	diff = (diff - (seconds = diff % 60)) / 60;
	diff = (diff - (minutes = diff % 60)) / 60;
	days = (diff - (hours = diff % 24)) / 24;

	return {
		milliseconds,
		seconds,
		minutes,
		hours,
		days,
	};
};
const uniqueID = () => {
	return Math.floor(Math.random() * Math.floor(Math.random() * Date.now()));
}

io.sockets.on('connection', (socket) => {
	console.log('[SIO] Connection');

	let uid;
	let player;

	// We got a user connection
	socket.on('register', (data) => {
		console.log('[CHAT] Register');

		// We should decrypt the user data
		const decryptedData = decryptJson(data.data);
		if (decryptedData) {
			// We subscribe the user to the channels
			socket.join(`faction_${decryptedData.faction}`);
			socket.join(`guild_${decryptedData.guild}`);
			socket.join(`battle_${decryptedData.battle}`);
			socket.join('world_0');
			socket.join('system_0');

			decryptedData.last_activity = new Date()
			players[decryptedData.uid] = decryptedData
			playersByName[decryptedData.name.toLowerCase()] = players[decryptedData.uid]
			lastMessages[decryptedData.user_id] = null

			uid = decryptedData.uid
			player = players[decryptedData.uid]

			counters[decryptedData.uid] = true;

			// For every new connection we should broadcast the message history for every channel(now it's aync bitch)
			['faction', 'guild', 'battle', 'world', 'system'].forEach((channel) => {
				let channelId;

				if (['world', 'system'].indexOf(channel) !== -1) {
					channelId = 0;
				} else {
					channelId = data[channel];
				}

				if (channels[channel] && channels[channel][channelId]) {
					const { messages } = channels[channel][channelId];

					Object.values(messages).forEach((message) => {
						socket.emit('broadcast', message);
					});
				}
			});
		} else {
			socket.emit('broadcast', {
				from: 'Sistema',
				message: 'Falha ao autenticar no chat!',
				channel: 'warn'
			});

			return;
		}
	});

	// Mark a private message as read
	socket.on('pvt-was-read', (data) => {
		if (privates[uid] && privates[uid][data.index]) {
			delete privates[uid][data.index];
		}
	});

	// User want to send a message
	socket.on('message', (data) => {
		const playerInstance = players[uid];

		if (!playerInstance) {
			console.log('invalid user trying to send a message');
			return;
		}

		// Word filter for non-gm users
		if (!player.gm) {
			let hasBlacklistedWord = false;

			blacklist.forEach((word) => {
				if (data.message.match(new RegExp(word, 'img')) && !hasBlacklistedWord) {
					hasBlacklistedWord = true;

					socket.emit('broadcast', {
						from: 'Sistema',
						message: 'A mensagem contem palavras impróprias',
						channel: 'warn',
					});
				}
			});

			if (hasBlacklistedWord || data.channel == 'system') {
				return;
			}

			data.message = phpjs.htmlspecialchars(data.message);
		}

		// User is sending a private message
		if (data.channel == 'private') {
			if (isNaN(data.dest)) {
				data.dest = data.dest.replace(/\s/, '');

				if (!playersByName[data.dest.toLowerCase()]) {
					socket.emit('broadcast', {
						from: 'Sistema',
						message: `Usuário "${data.dest}" indisponível para enviar mensagens`,
						channel: 'warn',
					});

					return;
				}

				data.dest = playersByName[data.dest.toLowerCase()].uid;
			}

			if (players[data.dest]) {
				if (players[data.dest].user_id == player.user_id) {
					socket.emit('broadcast', {
						from: 'Sistema',
						message: 'Você não pode enviar uma mensagem privada para você mesmo!',
						channel: 'warn'
					});

					return;
				} else {
					await db.promise().query(`select id from chat_blocked where user_id = ${players[data.dest].user_id} and user_blocked = ${player.user_id}`, (error, results, fields) => {
						if (results.length) {
							socket.emit('broadcast', {
								from: 'Sistema',
								message: 'Você não pode enviar mensagens para esse usuário!',
								channel: 'warn'
							});

							return;
						}
					});

					if (!privates[data.dest]) {
						privates[data.dest] = {};
					}

					privates[data.dest][uniqueID()] = {
						name: player.name,
						message: data.message,
						id: player.uid,
					};

					return;
				}
			}
		}

		// Block action
		if (data.channel === 'block') {
			const playerToBlock = playersByName[data.message.toLowerCase()];

			if (!playerToBlock) {
				socket.emit('broadcast', {
					from: 'Sistema',
					message: `O jogador "${data.message}" não foi encontrado`,
					channel: 'warn',
				});

				return;
			}

			if (playerToBlock.gm) {
				socket.emit('broadcast', {
					from: 'Sistema',
					message: 'Você não pode bloquear esse jogador pois ele pertence a STAFF',
					channel: 'warn',
				});

				return;
			}

			if (playerToBlock.user_id == player.user_id) {
				socket.emit('broadcast', {
					from: 'Sistema',
					message: 'Você não pode bloquear a sua propria conta!',
					channel: 'warn'
				});

				return;
			}

			db.query(`insert into chat_blocked (user_id, user_blocked) values (${player.user_id}, ${playerToBlock.user_id})`);

			socket.emit('broadcast', {
				from: 'Sistema',
				message: `O jogador "${data.message}" foi bloqueado com sucesso!`,
				channel: 'success'
			});

			return;
		}

		let channelId;
		switch (data.channel) {
			case 'faction': channelId = player.faction; break;
			case 'guild': channelId = player.guild; break;
			case 'battle': channelId = player.battle; break;
		}

		if (!channelId && ['world', 'system'].indexOf(data.channel) === -1) {
			console.log('channel error');
			return;
		}

		if (player.user_id && banned[player.user_id]) {
			socket.emit('broadcast', {
				from: 'Sistema',
				message: 'Você foi banido do chat',
				channel: 'warn',
			});

			return;
		}

		if (player.gm) {
			data.message = emoticons.parse(data.message, player.gm);
		} else {
			const now = new Date();
			const sendingTooOften = lastMessages[player.user_id] && diffInSeconds(lastMessages[player.user_id], now).seconds < 10;
			if (sendingTooOften) {
				socket.emit('broadcast', {
					from: 'Sistema',
					message: 'Você deve aguardar 10 segundos antes de enviar uma nova mensagem',
					channel: 'warn'
				});

				return;
			}

			lastMessages[player.user_id] = now;

			// Character limtit for non-gm users
			data.message = data.message.substr(0, userMessageSize);
			data.message = emoticons.parse(data.message, player.gm);
		}

		if (!channelId) {
			channelId = 0;
		}

		const broadcast = {
			from: player.name,
			message: data.message,
			channel: data.channel,
			channel_id: channelId,
			id: player.uid,
			user_id: player.user_id,
			gm: player.gm,
			when: new Date()
		};

		if (data.channel != 'faction') {
			broadcast.color = player.color;
			broadcast.icon = player.icon;
		}

		if (data.channel == 'guild' && player.guild_owner) {
			broadcast.color = '#BF2121';
		}

		// Channel allocation
		if (!channels[data.channel]) {
			channels[data.channel] = {};
		}

		if (!channels[data.channel][channelId]) {
			channels[data.channel][channelId] = {
				last: new Date(),
				messages: []
			}
		}

		// Updates the channel with the last message
		channels[data.channel][channelId].messages.last = new Date();
		channels[data.channel][channelId].messages.push(broadcast);

		// Message stack cleanup
		if (channels[data.channel][channelId].messages.length > maxMessages) {
			const messageDiff = channels[data.channel][channelId].messages.length - maxMessages;

			(() => {
				const results = [];
				for (let j = 0; messageDiff >= 0 ? j <= messageDiff : j >= messageDiff; messageDiff >= 0 ? j++ : j--) {
					results.push(j);
				}
				return results;
			}).apply(this).forEach(() => {
				channels[data.channel][channelId].messages.shift()
			});
		}

		io.sockets.in(`${data.channel}_${channelId}`).emit('broadcast', broadcast);
	});

	socket.on('blocked-query', () => {
		if (!players[uid]) {
			return;
		}
		const userId = players[uid].user_id

		const users = []
		db.query(`select user_blocked from chat_blocked where user_id = ${userId}`, (error, results, fields) => {
			results.forEach((row) => {
				users.push(row.user_blocked);
			});
		});

		socket.emit('blocked-broadcast', users);
	});

	// User queries for private messages
	socket.on('pvt-query', () => {
		if (!privates[uid] || !players[uid]) {
			return
		}
		console.log(`[CHAT] Get private messages - ${uid}`);

		const broadcast = [];
		const privateMessages = privates[uid];
		privateMessages.forEach((message, i) => {
			if (!message) {
				return;
			}

			broadcast.push({
				from: message.name,
				message: message.message,
				id: message.id,
				index: i
			});
		});

		if (broadcast.length) {
			socket.emit('pvt-broadcast', broadcast);
		}
	});

	socket.on('disconnect', () => {
		if (!players[uid]) {
			return;
		}

		counters[uid] = false;

		socket.leave(`faction_${players[uid].faction}`);
		socket.leave(`guild_${players[uid].guild}`);
		socket.leave(`battle_${players[uid].battle}`);
		socket.leave('world_0');
		socket.leave('system_0');
	});
});

// Wold blacklist checking
setInterval(() => {
	console.log('[INTERVAL] Update word blacklist');

	blacklist = [];
	db.query('select * from chat_word_blacklist', (error, words, fields) => {
		words.forEach((row) => {
			blacklist.push(row.expr);
		});
	});
}, 2000);

// Banned user check
setInterval(() => {
	console.log('[INTERVAL] Updating banned user list');

	banned = {};
	db.query('select * from chat_banned', (error, users, fields) => {
		users.forEach((row) => {
			banned[row.user_id] = true;
		});
	});
}, 5000);

// Clear battle channels
setInterval(() => {
	if (!channels.battle) {
		return;
	}

	console.log('[INTERVAL] Clearing battles');

	Object.entries(channels.battle).forEach(([key, battle]) => {
		const isPast = new Date((new Date()).setMinutes((new Date()).getMinutes() - 30));

		if (battle.past < isPast) {
			delete channels.battle[key];
		}
	});
}, 5000);

// Activity timer
setInterval(() => {
	console.log('[INTERVAL] Updating time played');

	Object.entries(counters).forEach(([user, counter]) => {
		if (!counter) {
			return;
		}
		db.query(`update played_time set minutes = minutes + 1 where player_id = ${user}`);
	});
}, 60000);

// Startup =)
bootstrap();
