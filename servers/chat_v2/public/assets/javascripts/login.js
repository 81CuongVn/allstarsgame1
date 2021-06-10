(function () {
	$('#form-login').on('submit', function (e) {
		e.preventDefault();

		lock_screen(true);

		$.ajax({
			url:		'/login',
			dataType:	'json',
			type:		'post',
			data:		$(this).serialize(),
			success:	function (result) {
				lock_screen(false);

				if (!result.success) {
					alert('Invalid login data!');
				} else {
					location.reload();
				}
			}
		});
	})
})();