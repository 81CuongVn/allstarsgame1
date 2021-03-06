(function () {
	$('#talents-container').on('click', '.item', function () {
		var	_	= $(this);

		if(_.hasClass('on')) {
			return;
		}

		lock_screen(true);

		$.ajax({
			url:		make_url('characters#talents'),
			data:		{item_id: _.data('item')},
			type:		'post',
			dataType:	'json',
			success:	function (result) {
				lock_screen(false);

				if(result.success) {
					_.addClass('on');
				} else {
					format_error(result);
				}
			}
		});
	});
})();