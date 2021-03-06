(function () {
	var	ranking_players	= $('#ranking-players-filter-form');
	var results_character	= $('#characters');


	if(ranking_players.length) {
		ranking_players.on('click', '.pagination a', function () {
			lock_screen(true);
			$('[name=page]', ranking_players).val($(this).data('page') - 1);

			ranking_players[0].submit();
		});

		ranking_players.on('click', '.filter', function () {
			$('[name=page]', ranking_players).val(0);
			ranking_players[0].submit();
		});
	}	
	$('#anime_id').on('change', function () {
		var	_	= $(this);
		$.ajax({
			url:		make_url('rankings#list_characters'),
			data:		$(this).serialize(),
			type:		'post',
			data:		{ anime_id: $(this).val()},
			success:	function (result) {
				lock_screen(false);
				results_character.html(result);
				$("input[id=h_character_id2]").val($('#h_character_id').val());
				$("#character_id option[value="+ $('#h_character_id').val() +"]").prop("selected", "selected");
			}
			
		});		
	});	
	$('#anime_id:first').trigger('change');
	

})();