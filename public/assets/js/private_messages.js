(function () {
	var private_messages_list	= $('#private-messages-list');

	function paginate(page) {
		$.ajax({
			url:		make_url('private_messages#messages/' + page),
			type:		'post',
			success:	function (result) {
				private_messages_list.html(result);
			}
		});
	}

	function read(id, sender) {
		var buttons	= [{
			label:		'Fechar',
			className:	'btn btn-sm btn-default'
		}];

		if (sender) {
			buttons.unshift({
				label:		I18n.t('private_messages.reply_current'),
				className:	'btn btn-sm btn-primary',
				callback:	function () {
					win.modal('hide');

					reply(id);
				}
			});
		}

		var	win		= bootbox.dialog({message: '...', buttons: buttons});

		$.ajax({
			url:		make_url('private_messages#read/' + id),
			success:	function (result) {
				$('.bootbox-body', win).html(result);
			}
		});
	}

	function reply(id) {
		var	loaded	= false;
		var	name	= '';
		var	win		= bootbox.dialog({message: '...', buttons: [
			{
				label:		I18n.t('private_messages.send_now'),
				className:	'btn btn-sm btn-primary',
				callback:	function () {
					if (!loaded) {
						return false;
					}

					lock_screen(true);

					$.ajax({
						url:		make_url('private_messages#send'),
						data:		$('form', win).serialize(),
						dataType:	'json',
						type:		'post',
						success:	function (result) {
							lock_screen(false);

							if (result.success) {
								win.modal('hide');
							} else {
								format_error(result);
							}
						}
					});

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
			url:		make_url('private_messages#reply/' + (id || '')),
			success:	function (result) {
				$('.bootbox-body', win).html(result);

				$('[name=to]', win).typeahead({
					minLength:	4,
					highlight:	true
				}, {
					name:		'default',
					source:		function (query, proc) {
						$.ajax({
							url:		make_url('private_messages#find_player'),
							dataType:	'json',
							data:		{keyword: query},
							success:	function (result) {
								var	matches	= [];
								
								result.forEach(function (item) {
									matches.push({value: item.image + item.name + ' - Lvl. ' + item.level, item: item});
								});

								proc(matches);
							}
						});
					}
				}).on('typeahead:selected', function (e, suggestion, ds) {
					e.stopPropagation();
					$('[name=to_id]', win).val(suggestion.item.id);

					name	= suggestion.item.name;

					//setTimeout(function () {
						$('[name=to]', win).val(suggestion.item.name);
					//}, 100);
				}).on('blur', function (e) {
					e.stopPropagation();

					//setTimeout(function () {
						$('[name=to]', win).val(name);
					//}, 100);					
				});

				loaded	= true;
			}
		});
	}

	if (private_messages_list.length) {
		private_messages_list.on('click', '.message', function () {
			var _	= $(this);

			read(_.data('id'), _.data('sender'));
		});

		$('#private-messages-list').on('click', '.pagination a', function () {
			paginate($(this).data('page')-1);
		});

		$('#private-message-compose').on('click', function () {
			reply();
		});

		$('#private-message-delete-selected').on('click', function () {
			var	options	= $('input[type=checkbox]:checked', private_messages_list);
			var	post	= {'ids[]': []};

			if (options.length) {
				bootbox.dialog({message: I18n.t('private_messages.confirm_delete'), buttons: {
					close:	{
						label:	I18n.t('global.cancel')
					}, confirm: {
						label:		I18n.t('global.continue'),
						className:	'btn btn-sm btn-danger',
						callback:	function () {
							options.each(function () {
								post['ids[]'].push($(this).data('id'));
							});

							$.ajax({
								url:		make_url('private_messages#delete'),
								type:		'post',
								data:		post,
								success:	function () {
									location.reload();
								}
							});
						}
					}
				}});
			}
		});

		$('#private-message-delete-all').on('click', function () {
			bootbox.dialog({message: I18n.t('private_messages.confirm_delete_all'), buttons: {
				close:	{
					label:	I18n.t('global.cancel')
				}, confirm: {
					label:		I18n.t('global.continue'),
					className:	'btn btn-sm btn-danger',
					callback:	function () {
						$.ajax({
							url:		make_url('private_messages#delete'),
							type:		'post',
							data:		{all: '1'},
							success:	function () {
								location.reload();
							}
						});
					}
				}
			}});
		});

		paginate(0);
	}
})();