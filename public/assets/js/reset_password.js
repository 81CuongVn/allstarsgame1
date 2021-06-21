(function () {
	var resetPassword	= $('#reset-password-form');
	if (resetPassword.length) {
		resetPassword.on('submit', function (e) {
			e.preventDefault();

			doResetPassword();
		})

		window.doResetPassword	= function() {
			lock_screen(true);

			$.ajax({
				url:		make_url('users#reset_password'),
				data:		resetPassword.serialize(),
				type:		'post',
				dataType:	'json',
				success:	function (result) {
					lock_screen(false);

					if(!result.success) {
						format_error(result);
					} else {
						$('#reset-password-box').html(result.view);
					}
				}
			});
		}
	}

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
