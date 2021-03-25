(function () {
    $('#tournament').on('click', '.subscribe', function () {
		lock_screen(true);

		var	_	= $(this);

		$.ajax({
			url:		make_url('tournaments#subscribe'),
			data:		{
                id:     _.data('tournament'),
                method: _.data('method')
            },
			dataType:	'json',
			type:		'post',
			success:	function (result) {
				lock_screen(false);

				if (!result.success) {
					format_error(result);
				} else {
					bootbox.alert(I18n.t('tournaments.success.subscribe'), function () {
						location.href	= make_url('tournaments#show/' + _.data('tournament'));
					});
				}
			}
		});
	});
})();