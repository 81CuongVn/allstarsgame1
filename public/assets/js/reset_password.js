(function () {
	$('#reset-password-form').on('submit', function (e) {
		e.preventDefault();

		lock_screen(true);

		$.ajax({
			url:		make_url('users#reset_password'),
			data:		$(this).serialize(),
			type:		'post',
			dataType:	'json',
			success:	function (result) {
				lock_screen(false);

				if(!result.success) {
					format_error(result);
				} else {
					$('#reset-password-form').html(result.view);
				}
			}
		});
	});

	$('#reset-password-finish-form').on('submit', function (e) {
		e.preventDefault();

		lock_screen(true);

		$.ajax({
			url:		$(this).attr('action'),
			data:		$(this).serialize(),
			type:		'post',
			dataType:	'json',
			success:	function (result) {
				lock_screen(false);

				if(!result.success) {
					format_error(result);
				} else {
					location.href	= make_url();
				}
			}
		});
	});
})();