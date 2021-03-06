(function () {
	var support_form	= $('#f-support-search');
	var reply_form		= $('#support-ticket-reply-form');
	var	ticket_list		= $('#support-ticket-list');

	var	_refresh		= function () {
		lock_screen(true);

		$.ajax({
			url:		make_url('support#search'),
			type:		'post',
			data:		support_form.serialize(),
			success:	function (result) {
				lock_screen(false);
				ticket_list.html(result);
			}
		});
	}

	if(support_form.length) {
		support_form.on('submit', function (e) {
			e.preventDefault();

			$('[name=page]', support_form).val(0);
			_refresh()
		});

		ticket_list.on('click', '.pagination a', function () {
			$('[name=page]', support_form).val($(this).data('page') - 1);
			_refresh();
		});

		ticket_list.on('click', '.filter', function () {
			$('[name=page]', support_form).val(0);
			_refresh();
		});

		support_form.trigger('submit');
	}

	if(reply_form.length) {
		$('.reply', reply_form).on('click', function () {
			if(!$('[name=content]', reply_form).val().length) {
				bootbox.alert(I18n.t('support.ticket.no_description'))
				return;
			}

			reply_form[0].submit();
		});

		$('.reopen', reply_form).on('click', function () {
			lock_screen(true);

			$.ajax({
				url:		reply_form.attr('action'),
				success:	function () {
					location.reload();
				}
			});
		});

		$('.reply-close', reply_form).on('click', function () {
			if(!$('[name=content]', reply_form).val().length) {
				bootbox.alert(I18n.t('support.ticket.no_description'));
				return;
			}

			$('[name=close]', reply_form).val(1);

			reply_form[0].submit();
		});
	}

	$('#support-command-extras').on('click', '.alternate', function () {
		lock_screen(true);

		$.ajax({
			url:		make_url('support#alternate'),
			data:		{ticket: $(this).data('id')},
			type:		'post',
			success:	function () {
				location.reload()
			}
		});
	});

	$('#support-command-extras').on('click', '.fullalternate', function () {
		lock_screen(true);

		$.ajax({
			url:		make_url('support#alternate/1'),
			data:		{ticket: $(this).data('id')},
			type:		'post',
			success:	function () {
				location.reload()
			}
		});
	});
})();