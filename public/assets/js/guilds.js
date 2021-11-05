(function () {
	var create_form	= $('#f-create-guild');
	var search_form	= $('#f-search-guild');
	var results		= $('#guild-search-results');

	if (create_form.length) {
		create_form.on('submit', function (e) {
			e.preventDefault();
			lock_screen(true);

			$.ajax({
				url:		make_url('guilds#create'),
				data:		$(this).serialize(),
				type:		'post',
				dataType:	'json',
				success:	function (result) {
					if (result.success) {
						location.href	= make_url('guilds#show');
					} else {
						lock_screen(false);
						format_error(result);
					}
				}
			});
		});
	}

	// Treasures
	$('.treasures_change').on('click', function () {

		lock_screen(true);
		var	_	= $(this);

		$.ajax({
			url:		make_url('guilds#treasures_change'),
			data:		{ mode: _.data('mode')},
			dataType:	'json',
			type:		'post',
			success:	function (result) {
				if(result.success) {
					location.href	= make_url('guilds#treasure');
				} else {
					lock_screen(false);
					format_error(result);
				}
			}
		});
	});

	if (search_form.length) {
		search_form.on('submit', function (e) {
			e.preventDefault();
			lock_screen(true);

			$.ajax({
				url:		make_url('guilds#make_list'),
				data:		$(this).serialize(),
				type:		'post',
				success:	function (result) {
					lock_screen(false);
					results.html(result);
				}
			});
		});

		search_form.trigger('submit');
	}

	if (results.length) {
		var join_cb	= function (id) {
			lock_screen(true);

			$.ajax({
				url:		make_url('guilds#enter/' + id),
				type:		'post',
				dataType:	'json',
				success:	function (result) {
					lock_screen(false);

					if (result.success) {
						$('#guild-search-item-' + id + ' .join').addClass('disabled');
					} else {
						format_error(result);
					}
				}
			});
		}

		results.on('click', '.join', function () {
			join_cb($(this).data('id'));
		});

		results.on('click', '.details', function () {
			var _	= $(this);

			lock_screen(true);

			var	win	= bootbox.dialog({message: '...', buttons: [
				{
					label:		I18n.t('guilds.search.join'),
					className:	'btn btn-sm btn-primary',
					callback:	function () {
						join_cb(_.data('id'));
						return false;
					}
				}, {
					label:		'Fechar',
					className:	'btn btn-sm btn-default'
				}
			]});

			$('.modal-dialog', win).addClass('pattern-container');
			$('.modal-content', win).addClass('with-pattern');

			$.ajax({
				url:		make_url('guilds#show/' + _.data('id')),
				data:		{popup: 1},
				type:		'post',
				success:	function (result) {
					$('.bootbox-body', win).html(result);
					lock_screen(false);
				}
			});
		});
	}

	$('#guild-details-tabs a').click(function (e) {
		e.preventDefault()
		$(this).tab('show')
	});

	$('#guild-accept-list').on('click', '.accept', function () {
		var _	= $(this);
		lock_screen(true);

		$.ajax({
			url:		make_url('guilds#enter_accept'),
			data:		{ id: _.data('id') },
			type:		'post',
			dataType:	'json',
			success:	function (result) {
				lock_screen(false);

				if (result.success) {
					_.parent().parent().remove();
				} else {
					format_error(result);
				}
			}
		});
	});

	// Remove todos os pedidos
	$('#guild-accept-list').on('click', '.remove_all', function () {
		var _	= $(this);

		bootbox.confirm(_.data('message'), function (result) {
				if(result) {
					lock_screen(true);

				$.ajax({
					url:		make_url('guilds#remove_all'),
					type:		'post',
					dataType:	'json',
					success:	function (result) {
						lock_screen(false);

						if (result.success) {
							location.href	= make_url('guilds#show');
						} else {
							format_error(result);
						}
					}
				});
				}
			});
	});

	$('#guild-accept-list').on('click', '.refuse', function () {
		var _	= $(this);

		var	win	= bootbox.dialog({message: '<h3>' + I18n.t('guilds.show.reason_title') + '</h3>' + '<textarea style="width: 400px" rows="5"></textarea>', buttons: [
			{
				label:		I18n.t('guilds.show.refuse'),
				className:	'btn btn-sm btn-primary',
				callback:	function () {
					$.ajax({
						url:		make_url('guilds#enter_refuse'),
						data:		{ id: _.data('id'), reason: $('textarea', win).val() },
						type:		'post',
						dataType:	'json',
						success:	function (result) {
							lock_screen(false);

							if (result.success) {
								_.parent().parent().remove();
								win.modal('hide');
							} else {
								format_error(result);
							}
						}
					});
				}
			}, {
				label:		'Fechar',
				className:	'btn btn-sm btn-default'
			}
		]});

		$('.modal-dialog', win).addClass('pattern-container mini');
		$('.modal-content', win).addClass('with-pattern');
	});

	$('#guild-player-list').on('click', '.destroy', function () {
		bootbox.dialog({message: '<h3>' + I18n.t('guilds.destroy.title') + '</h3>' + I18n.t('guilds.destroy.text'), buttons: [
			{
				label:		I18n.t('guilds.destroy.confirm'),
				className:	'btn btn-sm btn-danger',
				callback:	function () {
					$.ajax({
						url:		make_url('guilds#destroy'),
						type:		'post',
						dataType:	'json',
						success:	function (result) {
							if (result.success) {
								location.href	= make_url('characters#status');
							} else {
								lock_screen(false);
								format_error(result);
							}
						}
					});
				}
			}, {
				label:		'Fechar',
				className:	'btn btn-sm btn-default'
			}
		]});
	});

	$('#guild-player-list').on('click', '.can-approve, .can-kick', function () {
		lock_screen(true);
		var _			= $(this);
		var	action		= _.hasClass('can-approve') ? 'accept' : 'kick';
		var	data		= { id: _.data('id') };

		data[action]	= this.checked ? 1 : 0;

		$.ajax({
			url:		make_url('guilds#update_acl'),
			type:		'post',
			dataType:	'json',
			data:		data,
			success:	function (result) {
				lock_screen(false);

				if (!result.success) {
					format_error(result);
				}
			}
		});
	});

	$('#guild-player-list').on('click', '.leave', function () {
		lock_screen(true);

		$.ajax({
			url:		make_url('guilds#leave'),
			type:		'post',
			dataType:	'json',
			success:	function (result) {
				lock_screen(false);

				if (!result.success) {
					format_error(result);
				} else {
					location.href	= make_url('characters#status');
				}
			}
		});
	});

	$('#guild-player-list').on('click', '.kick', function () {
		var _	= $(this);

		var	win	= bootbox.dialog({message: '<h3>' + I18n.t('guilds.show.kick_reason') + '</h3>' + '<textarea style="width: 400px" rows="5"></textarea>', buttons: [
			{
				label:		I18n.t('guilds.show.kick'),
				className:	'btn btn-sm btn-primary',
				callback:	function () {
					lock_screen(true);

					$.ajax({
						url:		make_url('guilds#kick'),
						type:		'post',
						dataType:	'json',
						data:		{ id: _.data('id'), reason: $('textarea', win).val() },
						success:	function (result) {
							lock_screen(false);

							if (!result.success) {
								format_error(result);
							} else {
								_.parent().parent().remove();
							}
						}
					});
				}
			}, {
				label:		'Fechar',
				className:	'btn btn-sm btn-default'
			}
		]});

		$('.modal-dialog', win).addClass('pattern-container mini');
		$('.modal-content', win).addClass('with-pattern');
	});

	$('#guild-event-list').on('click', '.unlock', function () {
		lock_screen(true);

		var	_	= $(this);

		$.ajax({
			url:		make_url('guilds#unlock'),
			data:		{ event_id: _.data('event'), mode: _.data('mode') },
			dataType:	'json',
			type:		'post',
			success:	function (result) {
				lock_screen(false);

				if (!result.success) {
					format_error(result);
				} else {
					bootbox.alert(I18n.t('challenges.success'), function () {
						location.href	= make_url('guilds#events');
					});
				}
			}
		});
	});
})();
