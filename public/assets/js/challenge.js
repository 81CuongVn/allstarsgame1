(function () {
	$('#challenge-list').on('click', '.unlock', function () {
		lock_screen(true);

		var	_	= $(this);

		$.ajax({
			url:		make_url('challenges#unlock'),
			data:		{ challenge: _.data('challenge'), mode: _.data('mode') },
			dataType:	'json',
			type:		'post',
			success:	function (result) {
				lock_screen(false);

				if (!result.success) {
					format_error(result);
				} else {
					bootbox.alert(I18n.t('challenges.success'), function () {
						location.href	= make_url('challenges#show/'+_.data('challenge'));
					});
				}
			}
		});
	});

	$('.challenge-list').on('click', '.battle', function () {
		lock_screen(true);

		$.ajax({
			url:		make_url('history_mode#accept'),
			data:		{ npc: $(this).data('npc') },
			dataType:	'json',
			type:		'post',
			success:	function (result) {
				if (!result.success) {
					lock_screen(false);
					format_error(result);
				} else {
					location.href	= make_url('battle_npcs#fight');
				}
			}
		});
	});

	$('.challenge-list').on('click', '.show-battles', function () {
		var _		= $(this);
		var target	= $(_.data('target'));

		if (_.data('shown')) {
			_.html(_.data('show-text'));
			_.data('shown', false);
			target.hide();
		} else {
			_.html(_.data('hide-text'));
			_.data('shown', true);
			target.show();
		}
	});
})();