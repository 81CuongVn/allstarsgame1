(function () {
	var	form	= $('#game-form');

	form.on('submit', function (e) {
		e.preventDefault();

		lock_screen(true);

		$.ajax({
			url:		'/games/update/' + ($(this).data('id') || ''),
			data:		$(this).serialize(),
			dataType:	'json',
			type:		'post',
			success:	function (result) {
				if (result.success) {
					location.href	= '/games';
				} else {
					lock_screen(false);
					format_error(result);
				}
			}
		});
	});

	form.on('click', '.remove-channel', function () {
		var	tr		= $(this).parentsUntil('tbody').last();
		var	hidden	= $('input[type=hidden]');

		if (hidden.val()) {
			form.append('<input type="hidden" name="destroy_channel[]" value="' + hidden.val() + '" />')
		}

		tr.remove();
	});

	form.on('click', '.add-channel', function () {
		$('#channel-list').append(
			'<tr><td>&nbsp;<input type="hidden" name="channel_id[]" /><input type="hidden" name="channel_allow_subchannel[]" value="0" /></td>' +
			'<td><input type="text" class="form-control" name="channel[]" /></td>' + 
			'<td><input type="text" class="form-control" name="channel_key[]" /></td>' + 
			'<td><input type="text" class="form-control" name="channel_color[]" maxlength="6" /></td>' + 
			'<td><input type="checkbox" value="1" class="subchannel" /> Allow sub-channel</td>' +
			'<td><a class="btn-danger btn remove-channel">Remove</a></td></tr>'
		);
	});

	form.on('click', '.save-game', function () {
		form.trigger('submit');
	});

	form.on('click', '.subchannel', function () {
		var	parent	= $(this).parentsUntil('tr').parent();

		$('[name*=channel_allow_subchannel]', parent).val(this.checked ? 1 : 0);
	});
})();