(function () {
	var search_friend_form	= $('#f-search-friend');
	var results				= $('#friend-search-results');

	// Presenteando
	$('#friend-list-player').on('click','.gift', function () {
		lock_screen(true);
		var	_	= $(this);

		$.ajax({
			url:		make_url('friend_lists#gift'),
			data:		{ player: _.data('player'), gift: _.data('gift')},
			dataType:	'json',
			type:		'post',
			success:	function (result) {
				if(result.success) {
					location.href	= make_url('friend_lists');
				} else {
					lock_screen(false);
					format_error(result);
				}
			}
		});
	});

	$('#friend-list-player').on('click', '.kick', function () {
		var _	= $(this);

		var	win	= bootbox.dialog({message: '<h3>' + I18n.t('friends.kick_reason') + '</h3>' + '<textarea style="width: 400px" rows="5"></textarea>', buttons: [
			{
				label:		I18n.t('friends.kick'),
				className:	'btn btn-sm btn-primary',
				callback:	function () {
					lock_screen(true);

					$.ajax({
						url:		make_url('friend_lists#kick'),
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

	// Status do jogador
	$('.current-player').on('click', function () {
		var	_	= $(this);
		var	win	= bootbox.dialog({message: '...', buttons: [
			{
				label: 'Fechar',
				class:	'btn btn-sm btn-default'
			}
		]});

		$('.modal-dialog', win).addClass('pattern-container');
		$('.modal-content', win).addClass('with-pattern');

		$.ajax({
			url:		_.data('url'),
			type:		'get',
			data:		{player_id: _.data('player_id')},

			success:	function (result) {
				$('.bootbox-body', win).html(result);

				// This one is for the images
			}
		});
	});

	// Procura amigos
	if (search_friend_form.length) {
		search_friend_form.on('submit', function (e) {
			e.preventDefault();
			lock_screen(true);

			$.ajax({
				url:		make_url('friend_lists#make_list'),
				data:		$(this).serialize(),
				type:		'post',
				success:	function (result) {
					lock_screen(false);
					results.html(result);
				}
			});
		});

		search_friend_form.trigger('submit');
	}

	if (results.length) {
		var join_cb	= function (id) {
			lock_screen(true);

			$.ajax({
				url:		make_url('friend_lists#send/' + id),
				type:		'post',
				dataType:	'json',
				success:	function (result) {
					lock_screen(false);

					if (result.success) {
						$('#player-search-item-' + id + ' .send').addClass('disabled');
					} else {
						format_error(result);
					}
				}
			});
		}

		results.on('click', '.send', function () {
			join_cb($(this).data('id'));
		});

		var accept_cb	= function (id) {
			lock_screen(true);

			$.ajax({
				url:		make_url('friend_lists#enter_accept/' + id),
				type:		'post',
				dataType:	'json',
				success:	function (result) {
					lock_screen(false);

					if (result.success) {
						location.href	= make_url('friend_lists#search');
					} else {
						format_error(result);
					}
				}
			});
		}

		results.on('click', '.accept', function () {
			accept_cb($(this).data('id'));
		});

		var refuse_cb	= function (id) {
			lock_screen(true);

				$.ajax({
					url:		make_url('friend_lists#enter_refuse/' + id),
					type:		'post',
					dataType:	'json',
					success:	function (result) {
						lock_screen(false);

						if (result.success) {
							location.href	= make_url('friend_lists#search');
						} else {
							format_error(result);
						}
					}
				});
		}

		results.on('click', '.refuse', function () {
			refuse_cb($(this).data('id'));
		});

		// Remove todos os pedidos de amizade
		var remove_all_cb	= function (id) {
			bootbox.confirm(id, function (result) {
				if(result) {
					lock_screen(true);

				$.ajax({
					url:		make_url('friend_lists#remove_all'),
					type:		'post',
					dataType:	'json',
					success:	function (result) {
						lock_screen(false);

						if (result.success) {
							location.href	= make_url('friend_lists#search');
						} else {
							format_error(result);
						}
					}
				});
				}
			});
		}
		results.on('click', '.remove_all', function () {
			remove_all_cb($(this).data('message'));
		});
	}

	$('#friend-details-tabs a').click(function (e) {
		e.preventDefault()
		$(this).tab('show')
	});

	$('#friend-accept-list').on('click', '.refuse', function () {
		var _	= $(this);

		var	win	= bootbox.dialog({message: '<h3>' + I18n.t('guilds.show.reason_title') + '</h3>' + '<textarea style="width: 400px" rows="5"></textarea>', buttons: [
			{
				label:		I18n.t('guilds.show.refuse'),
				className:	'btn btn-sm btn-primary',
				callback:	function () {
					$.ajax({
						url:		make_url('friend_lists#enter_refuse'),
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
})();
