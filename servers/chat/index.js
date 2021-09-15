// Initialization
const express		= require('express');
const phpjs			= require('./phpjs');
const crypto		= require('crypto');
const sio			= require('socket.io');
const cors			= require('cors');
const emoticons		= require('./emoticons');
const bbcodes		= require('./bbcodes');
const config		= require('./config');
const db			= require('./db');

// Chat variables
const players			= {};
const playersByName		= {};
const channels			= {};
const privates			= {};
const counters			= {};
const lastMessages		= {};

// Moderation
let blacklist			= [];
let banned				= [];

// How many messages the chat should have
const maxMessages		= 100;
const userMessageSize	= 140;

// Global server variables
const app	= express();
app.use(cors());

let server;

// SSL settings
if (config.ssl.active) {
	const https	= require('https');
	server		= https.createServer({
		key:	config.ssl.key,			// Path to SSL Key
		cert:	config.ssl.certificate,	// Path to SSL Cert
	}, app);
} else {
	const http	= require('http');
	server		= http.createServer(app);
}

const io = sio(server, {
	cors: {
		origin: '*',
		methods: ['GET', 'POST'],
	},
});

const bootstrap = () => {
	channels.world	= [];
	channels.system	= [];

	db.connect(config.db);
	server.listen(config.port);

	console.log(`+ Chat Thread Started on ${server.address().address} at port ${server.address().port}`);
	console.log(`- Process started with PID ${process.pid}`);
};

const isUrl = (url) => {
	return url.match(new RegExp(/https?:\/\/(www\.)?[-a-zA-Z0-9@:%._+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_+.~#?&/=]*)/gi))
};

const isGame = (url) => {
	return url.match(new RegExp(/https?:\/\/(www\.)?allstarsgame.com.br/, 'gi'));
};

const haveUrl = (string) => {
	const urlRegex = /(https?:\/\/[^\s]+)/g;
	return string.replace(urlRegex, (url) => {
		if (isGame(url)) {
			return `<a href="${url}">${url}</a>`;
		} else {
			return `<a href="${url}" target="_blank">${url}</a>`;
		}
	});
};

const decryptJson = (encrypted) => {
	const key	= config.key;
	const iv	= key.substr(0, 16);

	let output	= false;
	try {
		const decipher	= crypto.createDecipheriv('aes-256-cbc', key, iv);

		let decrypted	= decipher.update(encrypted, 'base64', 'utf8');
		decrypted	+= decipher.final('utf8');

		output		= JSON.parse(decrypted);
	} catch (err) {
		console.log(`Decrypt Error: ${err}`);
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
		days
	};
};

io.sockets.on('connection', (socket) => {
	let _uid;
	let _player;

	// We got a player connection
	console.log('[SIO] Connection');

	socket.on('register', (data) => {
		console.log('[CHAT] Register');

		// We should decrypt the player data
		const decryptedData = decryptJson(data.data);
		if (decryptedData) {
			// We subscribe the player to the channels
			socket.join(`faction_${data.faction}`);
			socket.join(`guild_${data.guild}`);
			socket.join(`battle_${data.battle}`);
			socket.join('world_0');
			socket.join('system_0');

			decryptedData.last_activity							= new Date();
			players[decryptedData.uid]							= decryptedData;
			playersByName[decryptedData.name.toLowerCase()]		= players[decryptedData.uid];
			lastMessages[decryptedData.user_id]					= null;

			_uid												= decryptedData.uid;
			_player												= players[decryptedData.uid];

			counters[decryptedData.uid]							= true;

			// For every new connection we should broadcast the message history for every channel(now it's aync bitch)
			['faction', 'guild', 'battle', 'world', 'system'].forEach((channel) => {
				let channelId;

				if (['world', 'system'].indexOf(channel) !== -1) {
					channelId = 0;
				} else {
					channelId = decryptedData[channel];
				}

				if (channels[channel] && channels[channel][channelId]) {
					const {
						messages
					} = channels[channel][channelId];

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
		const pvtIndex = Object.keys(privates[_uid])[data.index];
		if (privates[_uid] && privates[_uid][pvtIndex]) {
			delete privates[_uid][pvtIndex];
		}
	});

	// Player want to send a message
	socket.on('message', async (data) => {
		if (!_player) {
			console.log('invalid player trying to send a message');
			return;
		}

		// Check for blacklisted words in the message
		if (!_player.gm) {
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

			if (hasBlacklistedWord) {
				return;
			}
		}

		if (data.channel == 'system' && !_player.gm) {
			return
		}

		if (!_player.gm) {
			data.message = phpjs.htmlspecialchars(data.message);
		}

		// Send private message
		if (data.channel === 'private') {
			if (isNaN(data.dest)) {
				data.dest = data.dest.replace(/\s/, '');

				if (!playersByName[data.dest.toLowerCase()]) {
					socket.emit('broadcast', {
						from: 'Sistema',
						message: `Usuário "${data.dest}" indisponível para receber mensagens`,
						channel: 'warn',
					});

					return;
				}

				data.dest = playersByName[data.dest.toLowerCase()].uid;
			}

			const playerDest	= players[data.dest]
			// Check if the destination user has blocked the one that's sending the message
			if (playerDest) {
				if (playerDest.user_id == _player.user_id) {
					socket.emit('broadcast', {
						from: 'Sistema',
						message: 'Você não pode enviar uma mensagem privada para você mesmo!',
						channel: 'warn'
					});

					return;
				}

				db.query(`SELECT id FROM chat_blocked WHERE user_id = ${playerDest.user_id} AND user_blocked = ${_player.user_id}`, (error, results, fields) => {
					if (results.length) {
						socket.emit('broadcast', {
							from: 'Sistema',
							message: 'Você não pode enviar mensagens para esse usuário!',
							channel: 'warn'
						});

						return;
					} else {
						db.query(`INSERT INTO chats (from_id, to_id, from_user_id, to_user_id, channel, message) VALUES (${_player.uid}, ${playerDest.uid}, ${_player.user_id}, ${playerDest.user_id}, 'pvt', '${data.message}')`);

						if (!privates[data.dest]) {
							privates[data.dest] = {};
						}
						privates[data.dest][Math.random() * 512384] = {
							name:		_player.name,
							message:	data.message,
							id:			_player.uid,
						};

						return;
					}
				});
			}
		}

		// Block another player
		if (data.channel === 'block') {
			const playerToBlock = playersByName[data.message.toLowerCase()];

			// Não encontrei ninguém paraa bloquear
			if (!playerToBlock) {
				socket.emit('broadcast', {
					from: 'Sistema',
					message: `Usuário "${data.message}" não encontrado`,
					channel: 'warn',
				});

				return;
			}

			// Não pode bloquear um staff
			if (playerToBlock.gm) {
				socket.emit('broadcast', {
					from: 'Sistema',
					message: 'Você não pode bloquear esse jogador pois ele pertence a STAFF',
					channel: 'warn',
				});

				return;
			}

			// Não pode bloquear você mesmo
			if (playerToBlock.user_id == _player.user_id) {
				socket.emit('broadcast', {
					from: 'Sistema',
					message: 'Você não pode bloquear a sua propria conta!',
					channel: 'warn'
				});

				return;
			}

			db.query(`INSERT INTO chat_blocked (user_id, user_blocked) VALUES (${_player.user_id}, ${playerToBlock.user_id})`);

			socket.emit('broadcast', {
				from: 'Sistema',
				message: 'Usuário "' + data.message + '" bloqueado com sucesso!',
				channel: 'success'
			});
			return;
		}

		// Set Channel Id
		let channelId;
		switch (data.channel) {
			case 'faction':	channelId = _player.faction;	break;
			case 'guild':	channelId = _player.guild;	break;
			case 'battle':	channelId = _player.battle;	break;
		}

		// Invalid Channel Id
		if (!channelId && !['world', 'system'].indexOf(data.channel) === -1) {
			console.log('channel error');

			return;
		}

		// Check user global ban
		if (_player.user_id && banned[_player.user_id]) {
			socket.emit('broadcast', {
				from: 'Sistema',
				message: 'Você foi banido do chat!',
				channel: 'warn'
			});

			return;
		}

		if (!_player.gm) {
			const now = new Date();
			const sendingTooOften = lastMessages[_player.user_id] && diffInSeconds(lastMessages[_player.user_id], now).seconds < 5;
			if (sendingTooOften) {
				socket.emit('broadcast', {
					from: 'Sistema',
					message: 'Você deve aguardar 10 segundos antes de enviar uma nova mensagem',
					channel: 'warn'
				});

				return;
			}

			lastMessages[_player.user_id]	= now;

			// Character limtit for non-gm users
			data.message	= data.message.substr(0, userMessageSize);
		}

		data.message	= haveUrl(data.message);
		data.message	= bbcodes.parse(data.message, _player.gm)
		data.message	= emoticons.parse(data.message, _player.gm);

		// If don't have Channel Id, set to default 0
		if (!channelId) {
			channelId = 0;
		}

		const broadcast = {
			from:		_player.name,
			avatar:		_player.avatar,
			faction:	_player.faction,
			message:	data.message,
			channel:	data.channel,
			channel_id:	channelId,
			id:			_player.uid,
			user_id:	_player.user_id,
			gm:			_player.gm,
			when:		new Date(),
		};

		if (data.channel != 'faction') {
			broadcast.color	= _player.color;
			broadcast.icon	= _player.icon;
		}

		if (data.channel == 'guild' && _player.guild_owner) {
			broadcast.color	= '#BF2121';
		}

		// Channel allocation
		if (!channels[data.channel]) {
			channels[data.channel] = {};
		}
		if (!channels[data.channel][channelId]) {
			channels[data.channel][channelId] = {
				last: new Date(),
				messages: [],
			};
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
			}).apply(this).forEach(() => channels[data.channel][channelId].messages.shift());
		}

		// Broadcast the message
		io.sockets.in(`${data.channel}_${channelId}`).emit('broadcast', broadcast);
	});

	// Get player block list
	socket.on('blocked-query', () => {
		if (!players[_uid]) {
			return;
		}

		db.query(`SELECT user_blocked FROM chat_blocked WHERE user_id = ${players[_uid].user_id}`, (error, results, fields) => {
			socket.emit('blocked-broadcast', results.map((row) => row.user_blocked));
		});
	});

	// Player queries for private messages
	socket.on('pvt-query', (data) => {
		if (!players[_uid]) {
			return;
		}

		console.log(`[CHAT] Get private messages - ${_uid}`);

		const privateMessages	= Object.values(privates[_uid] || {});
		const broadcast			= privateMessages.map((message, i) => ({
			from:		message.name,
			message:	message.message,
			id:			message.id,
			index:		i,
		}));

		if (broadcast.length) {
			socket.emit('pvt-broadcast', broadcast);
		}

	});

	// Player disconnect
	socket.on('disconnect', () => {
		console.log(`[SIO] Disconnect - ${_uid}`);
		if (!players[_uid]) {
			return;
		}

		counters[_uid]	= false;

		socket.leave(`faction_${players[_uid].faction}`);
		socket.leave(`guild_${players[_uid].guild}`);
		socket.leave(`battle_${players[_uid].battle}`);
		socket.leave('world_0');
		socket.leave('system_0');
	});
});

// Blacklist timer
setInterval(() => {
	console.log('[INTERVAL] Update word blacklist');

	blacklist	= []
	db.query('SELECT * FROM chat_word_blacklist', (error, words, fields) => {
		for (row in words) {
			blacklist.push(row.expr);
		}
	});
}, 2000);

// Ban Hammer Timer =D
setInterval(() => {
	console.log('[INTERVAL] Updating banned user list');

	banned	= []
	db.query('SELECT * FROM chat_banned', (error, users, fields) => {
		for (row in users) {
			banned[row.user_id]	= true;
		}
	});
}, 5000);

// Channel GC timer
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

// Played time
setInterval(() => {
	console.log('[INTERVAL] Updating time played');

	Object.entries(counters).forEach(([user, counter]) => {
		if (!counter) {
			return;
		}

		db.query(`UPDATE played_time SET minutes = minutes+1 WHERE player_id = ${user}`);
	});
}, 60000);

// Startup =)
bootstrap();
