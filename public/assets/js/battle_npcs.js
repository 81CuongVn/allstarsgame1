(function () {
	_locked	= [];

	$('#btn-enter-npc-battle').on('click', function () {
		lock_screen(true);
		var	_	= $(this);
		$.ajax({
			url:		make_url('battle_npcs#accept'),
			data:		{ type: _.data('type')},
			dataType:	'json',
			type:		'post',
			success:	function (result) {
				if(result.success) {
					location.href	= make_url('battle_npcs#fight');
				} else {
					lock_screen(false);
					format_error(result);
				}
			}
		});
	});
	$('#btn-enter-npc-battle-challenge').on('click', function () {
		lock_screen(true);
		var	_	= $(this);
		$.ajax({
			url:		make_url('battle_npcs#accept_challenge'),
			data:		{ type: _.data('type')},
			dataType:	'json',
			type:		'post',
			success:	function (result) {
				if(result.success) {
					location.href	= make_url('battle_npcs#fight');
				} else {
					lock_screen(false);
					format_error(result);
				}
			}
		});
	});
	$('.change-oponent').on('click', function () {
		bootbox.confirm($(this).data('message'), function (result) {
			if(result) {
				lock_screen(true);
				$.ajax({
					url:		make_url('battle_npcs#change_oponent'),
					data:		{ character_id: $("#character_id").val()},
					dataType:	'json',
					type:		'post',
					success:	function (result) {
						if(result.success) {
							location.href	= make_url('battle_npcs');
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