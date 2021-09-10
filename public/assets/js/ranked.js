(function () {
	// Filtro da página de ligas
	$('#leagues').on('change', function () {
		$('#league-filter-form').trigger('submit');
	});

	// Recebe a recompensa da Season
	$('#reward-league').on('click', '.reward', function () {
		lock_screen(true);
		var _ = $(this);
		$.ajax({
			url:		make_url('ranked#reward'),
			data:		{
				id: _.data('league')
			},
			dataType:	'json',
			type:		'post',
			success:	function (result) {
				if (result.success) {
					location.href = make_url('ranked');
				} else {
					lock_screen(false);
					format_error(result);
				}
			}
		});
	});
})();
