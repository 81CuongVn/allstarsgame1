(function () {
	$('#graduation-list .graduate').on('click', function () {
		lock_screen(true);

		$.ajax({
			url:		make_url('graduations#graduate/' + $(this).data('id')),
			type:		'post',
			dataType:	'json',
			success:	function (result) {
				if(result.success) {
					location.reload();
				} else {
					lock_screen(false);
					format_error(result);
				}
			}
		});
	});
})();