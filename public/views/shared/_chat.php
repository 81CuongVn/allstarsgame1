<div id="chat-container">
	<div class="title">
		Chat All-Stars
	</div>
	<div class="messages"></div>
	<div class="composer">
		<div class="channel-selector">
			<div class="channel-current"></div>
			<ul class="channel-list"></ul>
		</div>
		<input type="text" name="message" maxlength="100" value="" />
		<div class="timer"></div>
		<input type="checkbox" name="autoscroll" checked="checked" />
	</div>
</div>
<?php if (!$_SESSION['universal']  ): ?>
<input type="hidden" name="gm" checked="checked" />
<?php endif ?>
<?php
$chat_enc_key		= CHAT_SECRET;
$chat_registration	= [
	'key'		=> CHAT_KEY,
	'name'		=> $player->name,
	'player'	=> $player->id,
	'world_id'	=> $player->character()->anime_id,
	'channels'	=> [],
	'color'		=> $_SESSION['universal'] ? '#EDD309' : '',
	'is_master'	=> $_SESSION['universal']
];

$iv                 = substr($chat_enc_key, 0, 16);
$registration       = openssl_encrypt(json_encode($chat_registration), 'AES-256-CBC', $chat_enc_key,0, $iv);
?>
<script type="text/javascript">
	(function () {
		var socket				= null;
		var	connected			= false;
		var	container			= $('#chat-container');
		var	last_message		= null;
		var	wait_seconds		= 0;
		var	key					= null;
		var	chat_size_mini		= '35px';
		var	chat_size_normal	= '350px';
		var	current_channel		= '';
		var current_filter		= '';
		var	have_channels		= false;
		var	colors				= {};
		var	owner				= <?=$player->id;?>;
		var worker_port			= 0;

		$.ajax({
			url:		_node_server + ':2999/register/',
			dataType:	'json',
			type:		'post',
			data:		{
				registration: '<?=$registration;?>',
				game_id: _chat_server_id
			},
			cors:		true,
			success:	function (result) {
				if(!connected) {
					if (result.status == 200) {
						worker_port = result.server;
						connected	= true;
						key			= result.key;

						result.channels.forEach(function (channel) {
							$('.channel-list', container).append('<li data-key="' + channel.key + '" data-filter="' + channel.filter + '">' + channel.name + '</li>');

							if (!channel.subchannel && !current_channel) {
								$('.channel-list li:last', container).trigger('click');
							}

							colors[channel.filter]	= channel.color;
						});

						have_channels	= true;
						init_socket(function () {
							socket.emit('join', result.key);
						});
					} else {
						$('.messages', container).append('<div align="center">Erro de autenticação no chat</div>');
					}
				}
			}, error: function () {
				$('.messages', container).append('<div align="center">Falha ao efetuar autenticação com o chat</div>');
			}
		});

		window.ChatService	= {
			embeds:		[],
			decoded:	[],
			callbacks:	[],
			embed:	function (signed_string, match_text) {
				$('.composer [name=message]', container)[0].value += match_text;
				ChatService.embeds.push([match_text, signed_string]);
			},

			register_embed_cb:	function (matcher_expression, cb) {
				ChatService.callbacks.push([matcher_expression, cb]);
			},

			apply_embed_cb:	function (element) {
				ChatService.callbacks.forEach(function (callback) {
					var	count	= 0;

					while(true) {
						if(count++ >= 1000) {
							break;
						}

						var match	= element.html().match(callback[0]);
						var decoded	= null;

						if (match) {
							for (var i in ChatService.decoded) {
								if(i == match[0]) {
									decoded	= ChatService.decoded[i];
								}
							}

							callback[1].apply(null, [element, decoded]);
						} else {
							break;
						}
					}
				});
			}
		};

		var	ChatPMManager	= {
			_initialised:		false,
			store:				[],
			container:			null,
			minify_container:	null,
			window_count:		0,
			minified_count:		0,
			init:				function () {
				var	_this				= this;
				var	pm_container		= $(document.createElement('DIV')).addClass('pm-window-container');
				var	pm_minified_list	= $(document.createElement('DIV')).addClass('pm-minified-list');

				pm_container.append(pm_minified_list);

				$(document.body).append(pm_container);

				this.container			= pm_container;
				this.minify_container	= pm_minified_list;
				this._initialised		= true;

				pm_minified_list.on('click', function () {
					$('.list', this).toggleClass('shown');
				});

				pm_minified_list.on('click', '.list li', function () {
					var	id	= $(this).data('id');

					_this.store.forEach(function (item) {
						if (item.id == id) {
							item.show();
						}
					});
				});
			}, allocate:		function (id, name, auto_open) {
				if (!this._initialised) {
					this.init();
				}

				var	new_chat		= true;
				var	current_chat	= null;

				this.store.forEach(function (chat) {
					if (chat.id == id) {
						if (auto_open) {
							chat.show();
						}

						new_chat		= false;
						current_chat	= chat;
					}
				});

				if (new_chat) {
					current_chat	= new ChatPM(id, name, this, auto_open);
					this.store.push(current_chat);
				}

				return current_chat;
			}, update_minified:	function () {
				if (this.minified_count) {
					var	html	= '<div class="count"></div><ul class="list"></ul>';

					this.minify_container.show().html(html);
					this.store.forEach(function (item) {
						if (item.minified) {
							$('.list', this.minify_container).append('<li data-id="' + item.id + '">' + item.name + '</li>');
						}
					});

					$('.count', this.minify_container).html(this.minified_count);
				} else {
					this.minify_container.hide().html('');
				}
			}, close:		function (id) {
				var	new_store	= [];

				this.store.forEach(function (chat) {
					if (chat.id != id) {
						new_store.push(chat);
					} else {
						chat.destroy();
					}
				});

				this.store	= new_store;
				this.update_minified();
			}
		};

		var ChatPM	= function (id, name, manager, auto_open) {
			this.opened		= true;
			this.minified	= false;
			this.id			= id;
			this.name		= name;
			this.is_new		= true;

			var	_this		= this;
			var	chat_window	= $('<div class="pm-window"><div class="title">' + name + '<div class="close">&times;</div></div><div class="messages"></div>' +
				'<div class="composer"><input type="text" /></div></div>');

			chat_window.on('click', '.title', function () {
				var	_			= $(this);
				_this.opened	= !_this.opened;

				if (_this.opened) {
					_this.show();
				} else {
					_this.hide();
				}
			});

			chat_window.on('keyup', 'input[type=text]', function (e) {
				if (e.which == 13 && !e.shiftKey) {
					socket.emit('pmsend', {
						key:		key,
						content:	this.value,
						target:		_this.id,
						who:		_this.name
					});

					_this.receive(owner, this.value);
					this.value	= '';
				} else if (e.which == 13 && e.shiftKey) {
					this.value	+= "\r\n";
				}
			});

			chat_window.on('minify', function () {
				_this.minify();
			});

			chat_window.on('click', '.close', function () {
				manager.close(_this.id);
			});

			manager.window_count++;
			manager.container.append(chat_window);

			this.show	= function () {
				if (manager.window_count > 2) {
					$('.pm-window.normal:last', manager.container).trigger('minify');
				}

				chat_window.removeClass('closed');
				chat_window.removeClass('minified');
				chat_window.addClass('opened');
				chat_window.addClass('normal');

				if (_this.minified) {
					manager.minified_count--;
				}

				_this.opened	= true;
				_this.minified	= false;
				_this.is_new	= false;

				manager.update_minified();
			};

			this.hide	= function () {
				chat_window.addClass('closed');
				chat_window.removeClass('opened');
				_this.opened	= false;
			};

			this.minify	= function () {
				chat_window.addClass('minified');
				chat_window.removeClass('normal');
				_this.minified	= true;
				_this.opened	= false;

				manager.minified_count++;
				manager.update_minified();
			};

			this.receive	= function (who, message) {
				var	style	= 'self';

				if (parseInt(who) != parseInt(owner)) {
					style	= 'guest';
				}

				$('.messages', chat_window).append('<div class="' + style + '">' + message + '</div><div class="divider"></div>');
			};

			this.destroy	= function () {
				socket.emit('pmclose', {
					key:	key,
					target: this.id
				});

				chat_window.remove();
			};

			if (auto_open) {
				this.show();
			}
		};

		function init_socket(connect_cb) {
			socket = io.connect(base_server + ':' + worker_port);

			socket.on('connect', function () {
				if (connect_cb) {
					connect_cb.apply(null, []);
				}

				socket.on('broadcast', function (broadcast) {
					var	staff	= broadcast.staff ? '<span class="glyphicon glyphicon-star"></span>&nbsp;' : '';
					var	style	= 'color: #' + colors[broadcast.filter];

					var	html	= '<div style="' + style + '" class="message message-' + broadcast.filter + '">' + staff +
						'<span class="who" data-chat-id="' + broadcast.id + '" data-who="' + broadcast.who + '">' + broadcast.who + ':</span><span>' +
						broadcast.message + '</span></div>';

					$('.messages', container).append(html);

					if (broadcast.decodes) {
						broadcast.decodes.forEach(function (decoded) {
							ChatService.decoded[decoded[0]]	= decoded[1];
						});
					}

					list_last_message	= $('.messages .message:last', container);

					if (broadcast.filter != current_filter) {
						list_last_message.hide();
					}

					ChatService.apply_embed_cb(list_last_message);

					if ($('[name=autoscroll]:checked', container).length) {
						$('.messages', container).scrollTop(10000000);
					}
				});

				socket.on('pmreceived', function (pm_data) {
					var	chat	= ChatPMManager.allocate(pm_data.from, pm_data.who, false);
					chat.receive(pm_data.from, pm_data.message.content);
				});

				socket.on('pminitial', function (pm_data) {
					var	chat	= ChatPMManager.allocate(pm_data.from, pm_data.who, true);

					pm_data.messages.forEach(function (message) {
						chat.receive(message.from, message.content);
					});
				});
			});

			socket.on('error', function () {
				$('.messages', container).append('Falha ao conectar ao chat');
			});
		}

		$('.channel-current', container).on('click', function (e) {
			if (!have_channels) {
				return;
			}

			if (!this.shown) {
				$('.channel-list', container).show().css('opacity', 0).animate({opacity: 1});

				this.shown	= true;
			} else {
				$('.channel-list', container).animate({opacity: 0}, function () { $('.channel-list', container).hide() });

				this.shown	= false;
			}
		});

		container.on('click', '.channel-list li', function (e) {
			var	_	= $(this);

			current_channel	= _.data('key');
			current_filter	= _.data('filter');

			$('.channel-current', container).html(_.html()).css('color', '#' + colors[current_filter]);
			$('input[type=text]', container).css('padding-left', ($('.channel-current', container).width() + 20) + 'px');
			$('.channel-current', container).trigger('click');

			$('.messages .message', container).hide();
			$('.messages .message-' + current_filter, container).show();
		});

		$(container).on('click', '.messages .who', function () {
			var	id	= parseInt($(this).data('chat-id'));

			if (id == owner) {
				return;
			}

			ChatPMManager.allocate(id, $(this).data('who'), true);
		});

		$(container).on('keyup', '.composer [name=message]', function (e) {
			if (!connected) {
				console.log('not connected');

				return;
			}

			if (!this.value.replace(/[\s]*/, '').length) {
				console.log('all blank');

				return;
			}

			if(e.which == 13) {
				if(last_message && wait_seconds <= 10) {
					return;
				}

				socket.emit('message', {
					key:		key,
					message:	this.value,
					staff:		$('[name=gm]:checked', container).length,
					embeds: 	ChatService.embeds,
					channel:	current_channel
				});

				this.value			= '';
				ChatService.embeds	= [];

				<?php if(!$_SESSION['universal']): ?>
				last_message	= new Date();
				<?php endif; ?>
			}
		});

		<?php if(!$_SESSION['universal']): ?>
		setInterval(function () {
			if (last_message) {
				var	now			= new Date();
				var	diff		= last_message.getTime() - now.getTime();
				wait_seconds	= Math.round(Math.abs(diff / 1000));

				if (wait_seconds <= 10) {
					$('.timer', container).show().html('Aguarde ' + (10 - wait_seconds) + ' segundo(s)');
				} else {
					$('.timer', container).hide();
				}
			};
		}, 1000);
		<?php endif; ?>

		$('[name=gm]:checked', container).on('click', function (e) {
			e.stopPropagation();
		});

		if (!$.cookie('chat_expanded')) {
			$.cookie('chat_expanded', 0);
		}

		if ($.cookie('chat_expanded') == 1) {
			container.css('height', chat_size_normal);
		}

		$('.title', container).on('click', function () {
			if ($.cookie('chat_expanded') == 0) {
				container.css('height', chat_size_normal);
				$.cookie('chat_expanded', 1);
			} else {
				container.css('height', chat_size_mini);
				$.cookie('chat_expanded', 0);
			}
		});
	})();
</script>
