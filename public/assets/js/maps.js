(function () {
	// Treasures
	$('.store_change').on('click', function () {
		
			lock_screen(true);
			var	_	= $(this);

			$.ajax({
				url:		make_url('maps#store_change'),
				data:		{ mode: _.data('mode')},
				dataType:	'json',
				type:		'post',
				success:	function (result) {
					if(result.success) {
						location.href	= make_url('maps#preview');
					} else {
						lock_screen(false);
						format_error(result);
					}
				}
			});
		});
	$('.ability-speciality-box').on('click', '.unlock', function () {
		lock_screen(true);

		var	_	= $(this);

		$.ajax({
			url:		make_url('maps#unlock'),
			data:		{ id: _.data('id'), mode: _.data('mode') },
			dataType:	'json',
			type:		'post',
			success:	function (result) {
				lock_screen(false);

				if (!result.success) {
					format_error(result);
				} else {
					location.href	= make_url('maps#preview');
				}
			}
		});
	});
	$('#navegation').on('click', '.direction', function () {
		lock_screen(true);
		var	_	= $(this);

		$.ajax({
			url:		make_url('maps#navegation'),
			data:		{ map: _.data('map'), direction: _.data('direction')},
			dataType:	'json',
			type:		'post',
			success:	function (result) {
				lock_screen(false);

				if (!result.success) {
					format_error(result);
				} else {
					location.href	= make_url('maps#preview');
				}
			}
		});
	});
	$('#map-leave').on('click', '.leave', function () {
		bootbox.confirm($(this).data('message'), function (result) {
			if(result) {
				lock_screen(true);
				var	_	= $(this);

				$.ajax({
					url:		make_url('maps#leave'),
					data:		{id: _.data('id')},
					type:		'post',
					dataType:	'json',
					success:	function (result) {
						if(result.success) {
							location.href	= make_url('characters#status');
						} else {
							lock_screen(false);
							format_error(result);
						}
					}
				});
			}
		});
	});
})();