(function () {
	var create_form	= $('#f-create-organization');
	var search_form	= $('#f-search-organization');
	var results		= $('#organization-search-results');

	if (create_form.length) {
		create_form.on('submit', function (e) {
			e.preventDefault();
			lock_screen(true);

			$.ajax({
				url:		make_url('organizations#create'),
				data:		$(this).serialize(),
				type:		'post',
				dataType:	'json',
				success:	function (result) {
					if (result.success) {
						location.href	= make_url('organizations#show');
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
				url:		make_url('organizations#treasures_change'),
				data:		{ mode: _.data('mode')},
				dataType:	'json',
				type:		'post',
				success:	function (result) {
					if(result.success) {
						location.href	= make_url('organizations#treasure');
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
				url:		make_url('organizations#make_list'),
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
				url:		make_url('organizations#enter/' + id),
				type:		'post',
				dataType:	'json',
				success:	function (result) {
					lock_screen(false);

					if (result.success) {
						$('#organization-search-item-' + id + ' .join').addClass('disabled');
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
					label:		I18n.t('organizations.search.join'),
					className:	'btn btn-primary',
					callback:	function () {
						join_cb(_.data('id'));
						return false;
					}
				}, {
					label:		'Fechar',
					className:	'btn btn-default'
				}
			]});

			$('.modal-dialog', win).addClass('pattern-container');
			$('.modal-content', win).addClass('with-pattern');

			$.ajax({
				url:		make_url('organizations#show/' + _.data('id')),
				data:		{popup: 1},
				type:		'post',
				success:	function (result) {
					$('.bootbox-body', win).html(result);
					lock_screen(false);
				}
			});
		});
	}

	$('#organization-details-tabs a').click(function (e) {
		e.preventDefault()
		$(this).tab('show')
	});

	$('#organization-accept-list').on('click', '.accept', function () {
		var _	= $(this);
		lock_screen(true);

		$.ajax({
			url:		make_url('organizations#enter_accept'),
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
	// Remove todos os pedidos de amizade
	$('#organization-accept-list').on('click', '.remove_all', function () {
		var _	= $(this);

		bootbox.confirm(_.data('message'), function (result) {
				if(result) {
					lock_screen(true);
			
				$.ajax({
					url:		make_url('organizations#remove_all'),
					type:		'post',
					dataType:	'json',
					success:	function (result) {
						lock_screen(false);
	
						if (result.success) {
							location.href	= make_url('organizations#show');
						} else {
							format_error(result);
						}
					}
				});
				}
			});
	});
	
	$('#organization-accept-list').on('click', '.refuse', function () {
		var _	= $(this);

		var	win	= bootbox.dialog({message: '<h3>' + I18n.t('organizations.show.reason_title') + '</h3>' + '<textarea style="width: 400px" rows="5"></textarea>', buttons: [
			{
				label:		I18n.t('organizations.show.refuse'),
				className:	'btn btn-primary',
				callback:	function () {
					$.ajax({
						url:		make_url('organizations#enter_refuse'),
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
				className:	'btn btn-default'
			}
		]});

		$('.modal-dialog', win).addClass('pattern-container mini');
		$('.modal-content', win).addClass('with-pattern');
	});

	$('#organization-player-list').on('click', '.destroy', function () {
		bootbox.dialog({message: '<h3>' + I18n.t('organizations.destroy.title') + '</h3>' + I18n.t('organizations.destroy.text'), buttons: [
			{
				label:		I18n.t('organizations.destroy.confirm'),
				className:	'btn btn-danger',
				callback:	function () {
					$.ajax({
						url:		make_url('organizations#destroy'),
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
				className:	'btn btn-default'
			}
		]});
	});

	$('#organization-player-list').on('click', '.can-approve, .can-kick', function () {
		lock_screen(true);
		var _			= $(this);
		var	action		= _.hasClass('can-approve') ? 'accept' : 'kick';
		var	data		= { id: _.data('id') };

		data[action]	= this.checked ? 1 : 0;

		$.ajax({
			url:		make_url('organizations#update_acl'),
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

	$('#organization-player-list').on('click', '.leave', function () {
		lock_screen(true);

		$.ajax({
			url:		make_url('organizations#leave'),
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

	$('#organization-player-list').on('click', '.kick', function () {
		var _	= $(this);

		var	win	= bootbox.dialog({message: '<h3>' + I18n.t('organizations.show.kick_reason') + '</h3>' + '<textarea style="width: 400px" rows="5"></textarea>', buttons: [
			{
				label:		I18n.t('organizations.show.kick'),
				className:	'btn btn-primary',
				callback:	function () {
					lock_screen(true);

					$.ajax({
						url:		make_url('organizations#kick'),
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
				className:	'btn btn-default'
			}
		]});

		$('.modal-dialog', win).addClass('pattern-container mini');
		$('.modal-content', win).addClass('with-pattern');
	});
	$('#organization-event-list').on('click', '.unlock', function () {
		lock_screen(true);

		var	_	= $(this);

		$.ajax({
			url:		make_url('organizations#unlock'),
			data:		{ event_id: _.data('event'), mode: _.data('mode') },
			dataType:	'json',
			type:		'post',
			success:	function (result) {
				lock_screen(false);

				if (!result.success) {
					format_error(result);
				} else {
					bootbox.alert(I18n.t('challenges.success'), function () {
						location.href	= make_url('organizations#events');
					});
				}
			}
		});
	});
})();