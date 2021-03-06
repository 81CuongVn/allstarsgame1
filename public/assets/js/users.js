(function () {
	var	join_form		= $('#f-user-join');
	var	account_form	= $('#f-account-join');
	
	$("#zip").keypress(function (e) {
	 //if the letter is not digit then display error and don't type anything
	 if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
		//display error message
		$("#errmsg").html("Somente Números no Cep").show().fadeOut("slow");
			   return false;
	}
	});
	
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
						location.href	= make_url('users#activation/' + result.key);
					} else {
						lock_screen(false);
						format_error(result);
					}
				}
			});
		});

		$('#join-captcha-image-refresh').on('click', function () {
			var	img	= $('#join-captcha-image');

			img.attr('src', img.data('image') + '?_cache=' + (Math.random() * 512384));
		});
	}
	if(account_form.length) {
		account_form.on('submit', function (e) {
			lock_screen(true);

			e.preventDefault();

			$.ajax({
				url:		make_url('users#account_complete'),
				data:		account_form.serialize(),
				type:		'post',
				dataType:	'json',
				success:	function (result) {
					if(result.success) {
						location.href	= make_url('users#account');
					} else {
						lock_screen(false);
						format_error(result);
					}
				}
			});
		});
	}
})();