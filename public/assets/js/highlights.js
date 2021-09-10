var inviteModal			= null;
var listModal			= null;
var ignoreInviteCalls	= false;
var currentQueue		= null;
var inviteModalTemplate	= $("<div class='modal fade'>\
	<div class='modal-dialog'>\
		<div class='modal-content'>\
			<div class='modal-header'>Sua organização precisa de você!</div>\
			<div class='modal-body'>\
				<h4>Você está sendo convidado para participar da masmorra <span class='dungeon-name'></span></h4>\
			</div>\
			<div class='modal-footer'>\
				<a class='btn btn-primary'>Aceitar desafio</a>\
				<a class='btn btn-danger'>Recusar</a>\
			</div>\
		</div>\
	</div>\
</div>");

function createInviteModal(eventId) {
	var invitesSent = false;

	listModal = inviteModalTemplate.clone();

	listModal
		.modal({
			backdrop: 'static'
		})
		.on('bs.modal.hidden', function() {
			listModal.remove();
		})
		.find('.modal-header')
		.html('Convidar integrantes para o evento')
		.end()
		.find('.modal-body')
		.each(function() {
			var self = $(this);

			self.html('Aguarde...');

			$.ajax({
				url: make_url('guilds/dungeon_invite'),
				type: 'post',
				data: {
					list: 1,
					dungeon_id: eventId
				},
				success: function(result) {
					if (result.success) {
						if (result.started) {
							invitesSent = true;
							listModal.find('.btn-primary').addClass('disabled');
						}

						var html = ['<form>', '<input type="hidden" name="dungeon_id" value="' + eventId + '" />'];

						result.players.forEach(function(p) {
							var id = $id();
							var statusText = '';

							if (p.accepted) {
								statusText = '<span class="badge alert-success" data-player="' + p.id + '">Aceito</span>';
							} else if (p.refused) {
								statusText = '<span class="badge alert-danger" data-player="' + p.id + '">Recusado</span>';
							} else if (result.started) {
								statusText = '<span class="badge loading" data-player="' + p.id + '">Aguardando</span>';
							}

							html.push("<div class='checkbox' data-player='" + p.id + "'>\
								<input type='checkbox' " + (p.invited ? "checked='checked'" : '') + " name='players[]' value='" + p.id + "' id='" + id + "' />\
								<label for='" + id + "'>" + p.name + "</label>" + statusText + "\
							</div>")
						});

						html.push('</form>');
						self.html(html.join(''));
					} else {
						listModal.modal('hide');
						format_error(result);
					}
				}
			})
		})
		.end()
		.find('.btn-primary')
		.html('Convidar jogadores')
		.addClass('disabled')
		.on('click', function() {
			var self = $(this);
			invitesSent = true;

			listModal.find('.alert').remove();

			listModal.find('.checkbox input:checked').each(function() {
				var element = $(this);

				$('<span class="badge loading" data-player="' + element.val() + '">Aguardando</span>').insertAfter(element.next());
			});

			self
				.html('Aguardando jogadores')
				.addClass('loading disabled');

			$.ajax({
				url: make_url('guilds/dungeon_invite'),
				data: listModal.find('form').serialize(),
				type: 'post',
				dataType: 'json',
				success: function(result) {
					if (!result.success) {
						self
							.html('Convidar jogadores')
							.removeClass('loading')

						listModal
							.find('.modal-body')
							.append('<div class="alert alert-danger"><ul>' + result.messages.map(function(e) {
								return '<li>' + e + '</li>'
							}).join('') + '</ul></div>')
							.end()
							.find('.badge')
							.remove()
							.end()
					}
				}
			})
		})
		.end()
		.find('.btn-danger')
		.html('Cancelar')
		.on('click', function() {
			$.ajax({
				url: make_url('guilds/dungeon_cancel'),
				type: 'post',
				data: {
					dungeon_id: eventId
				}
			});

			listModal.modal('hide');
			socket.emit('cancel-dungeon', {
				guild: _current_guild
			});
		})
		.end()
		.on('click', 'input[type=checkbox]', function(e) {
			if (invitesSent) {
				e.preventDefault();
				return;
			}

			var checked = listModal.find('input[type=checkbox]:checked');

			if (checked.length) {
				listModal.find('.btn-primary').removeClass('disabled');
			} else {
				listModal.find('.btn-primary').addClass('disabled');
			}
		});
}

var socket = io.connect(_highlights_server);
socket.on('connect', function () {
	console.log('Highlights service connected');

	socket.emit('set-language', {
		lang: _language
	});

	if (_current_guild) {
		socket.emit('enter-orgnaization', {
			guild: _current_guild
		})
	};
});

socket.on('error', function () {
	console.log('Highlights service error');
	console.log(arguments);
});

socket.on('message', function (data) {
	console.log('Got message from Highlights service');

	var d = $(document.createElement('DIV')).addClass('highlight-window');
	var m = $(document.createElement('DIV')).addClass('highlight-text');
	var len = $('.highlight-window').length;

	m.html(data.message);
	d.append(m);

	$(document.body).append(d);

	if(len) {
		d.css({marginTop: len * (d.height() + 10)});
	}

	var __iv  = setInterval(function () {
		d.animate({opacity: 0}, function () {
			d.remove();
		});

		clearInterval(__iv);
	}, 7000);
});

socket.on('dungeon-invite', function(dungeon) {
	if (listModal) {
		dungeon.accepts.forEach(function(accepted) {
			listModal
				.find('.badge[data-player=' + accepted + ']')
				.addClass('alert-success')
				.html('Aceito');
		});

		dungeon.refuses.forEach(function(refused) {
			listModal
				.find('.badge[data-player=' + refused + ']')
				.addClass('alert-danger')
				.html('Recusado');
		});
	} else {
		if (inviteModal || ignoreInviteCalls) {
			return;
		}
		console.log('aqui');

		if (dungeon.targets.indexOf(_current_player.toString()) == -1) {
			return;
		};

		inviteModal = inviteModalTemplate.clone();
		currentQueue = dungeon.queue;

		$(document.body).append(inviteModal);

		inviteModal
			.modal({
				backdrop: 'static'
			})
			.on('hidden.bs.modal', function() {
				inviteModal.remove();
			}).find('.dungeon-name')
			.html(dungeon.name)
			.end()
			.on('click', '.btn-primary', function() {
				var self = $(this);

				ignoreInviteCalls = true;
				inviteModal.modal('hide');

				$.ajax({
					url: make_url('guilds#dungeon_accept'),
					type: 'post',
					dataType: 'json',
					data: {
						queue_id: dungeon.queue
					}
				});
			}).on('click', '.btn-danger', function() {
				ignoreInviteCalls = true;

				inviteModal.modal('hide');

				$.ajax({
					url: make_url('guilds#dungeon_refuse'),
					type: 'post',
					data: {
						queue_id: dungeon.queue
					}
				});
			});
	}
});

socket.on('dungeon-cancelled', function() {
	if (!inviteModal) {
		return;
	}

	inviteModal
		.on('hidden.bs.modal', function() {
			inviteModal = null;
			ignoreInviteCalls = false;
		})
		.modal('hide');
});

socket.on('dungeon-redirect', function(players) {
	if (players.indexOf(_current_player.toString()) == -1) {
		return;
	}

	location.href = make_url('guilds/dungeon');
});

$(document).on('ready', function() {
	$('#guild-event-list').on('click', '.invite', function() {
		var trigger = $(this);

		createInviteModal(trigger.data('event'));
	});
});
