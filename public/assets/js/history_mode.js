(function () {
	$('#history-mode-group-list').on('click', '.unlock', function () {
		lock_screen(true);

		var	_	= $(this);

		$.ajax({
			url:		make_url('history_mode#unlock'),
			data:		{ group: _.data('group'), mode: _.data('mode') },
			dataType:	'json',
			type:		'post',
			success:	function (result) {
				lock_screen(false);

				if (!result.success) {
					format_error(result);
				} else {
					bootbox.alert(I18n.t('history_mode.unlock.success'), function () {
						location.reload();
					});
				}
			}
		});
	});

	$('.history-mode-subgroup-list').on('click', '.battle', function () {
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

	$('.history-mode-subgroup-list').on('click', '.show-battles', function () {
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