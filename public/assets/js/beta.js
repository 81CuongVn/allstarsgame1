(function () {
	var	join_form	= $('#f-user-join');

	if(join_form.length) {
		join_form.on('submit', function (e) {
			lock_screen(true);

			e.preventDefault();

			$.ajax({
				url:		make_url('users#join_complete'),
				data:		join_form.serialize(),
				type:		'post',
				dataType:	'json',
				success:	function (result) {
					if(result.success) {
						location.href	= make_url('users#beta_activation/' + result.key);
					} else {
						lock_screen(false);
						format_error(result);
					}
				}
			});
		});
	}

	$('#join-captcha-image-refresh').on('click', function () {
		var	img	= $('#join-captcha-image');

		img.attr('src', img.data('image') + '?_cache=' + (Math.random() * 512384));
	});

	$('#beta-login-button').on('click', function (e) {
		lock_screen(true);

		$.ajax({
			url:		make_url('users#beta_login'),
			data:		$('#beta-login-form').serialize(),
			dataType:	'json',
			type:		'post',
			success:	function (result) {
				if(!result.success) {
					lock_screen(false);

					format_error(result);
				} else {
					location.href	= result.redirect;
				}
			}
		});

		e.preventDefault();
	});
})();