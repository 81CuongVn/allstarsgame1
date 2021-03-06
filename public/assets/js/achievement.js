(function () {
	var results = $('#make-list-achievement');
	
	$('#time-quests-list-tabs').on('click','a', function () {
		lock_screen(true);
		var	_	= $(this);
	
		$.ajax({
			url:		make_url('achievements#make_list'),
			data:		{ achievement_id: _.data('id')},
			type:		'post',
			success:	function (result) {
				lock_screen(false);
				results.html(result);
			}
		});
	});
	$('#time-quests-list-tabs').find('a:first').trigger('click');
})();