(function () {
		var	quest_list	= $('#quest-list-content');

		$('#quest-list-tabs a').click(function (e) {
			e.preventDefault()
			$(this).tab('show');
		});
	// Troca a missão semanal da Organização do Jogador
		$('.organization_daily_quests_change').on('click', function () {
			var	id			= $(this).data('id');
			var	quest		= $(this).data('quest');
			lock_screen(true);

			$.ajax({
				url:		make_url('quests#organization_daily_change'),
				type:		'post',
				data:		{id: id, quest: quest},
				success:	function (result) {
					if(result.success) {
						location.href	= make_url('quests#organization_daily');
					} else {
						lock_screen(false);
						format_error(result);
					}
				}
			});
		});
	// Troca a missão de Diária do Jogador
		$('.daily_quests_change').on('click', function () {
			var	id			= $(this).data('id');
			var	quest		= $(this).data('quest');
			lock_screen(true);

			$.ajax({
				url:		make_url('quests#daily_change'),
				type:		'post',
				data:		{id: id, quest: quest},
				success:	function (result) {
					if(result.success) {
						location.href	= make_url('quests#daily');
					} else {
						lock_screen(false);
						format_error(result);
					}
				}
			});
		});
	// Troca a missão de Conta
		$('.account_quests_change').on('click', function () {
			var	id			= $(this).data('id');
			var	quest		= $(this).data('quest');
			lock_screen(true);

			$.ajax({
				url:		make_url('quests#account_change'),
				type:		'post',
				data:		{id: id, quest: quest},
				success:	function (result) {
					if(result.success) {
						location.href	= make_url('quests#account');
					} else {
						lock_screen(false);
						format_error(result);
					}
				}
			});
		});	
	// Finaliza a missão de pet
		$('.pet-quest-finish').on('click', function () {
			var	id			= $(this).data('id');
			lock_screen(true);

			$.ajax({
				url:		make_url('quests#pet_finish'),
				type:		'post',
				data:		{id: id},
				success:	function (result) {
					if(result.success) {
						location.href	= make_url('quests#pet');
					} else {
						lock_screen(false);
						format_error(result);
					}
				}
			});
		});

	//Lista os Pets das missões
	$('.current-quest-change-pet.add-pet').on('click', function () {
		var	_	= $(this);
		var	win	= bootbox.dialog({
			message: 'Que ação você gostaria de fazer?',
			buttons: [{
				label: 'Fechar',
				className:	'btn btn-sm btn-danger'
			}]
		});

		$('.modal-dialog', win).addClass('pattern-container');
		$('.modal-content', win).addClass('with-pattern');

		$.ajax({
			url:		_.data('url'),
			type:		'get',
			data:		{quest_id: _.data('quest_id'), counter: _.data('counter')},
			
			success:	function (result) {
				$('.bootbox-body', win).html(result);

				// This one is for the images
				$('.modal-content', win).on('click', '.pet-box', function () {
					win.modal('hide');
					lock_screen(true);

					$.ajax({
						url:		make_url('quests#list_pets'),
						type:		'post',
						data:		{id: $(this).data('id'), quest_id: $(this).data('quest_id'), counter: $(this).data('counter')},
						dataType:	'json',
						success:	function (result) {
							if(result.success) {
								location.href	= make_url('quests#pet');
							} else {
								lock_screen(false);
								format_error(result);
							}
						}
					});

				});

			}
		});
	});
	// Remove um pet de uma missão com o botão direito
	$('.current-quest-change-pet.remove-pet').on('click', function () {
		
		var	quest_id		= $(this).data('quest_id');
		var	counter			= $(this).data('counter'); 
		
		bootbox.confirm($(this).data('message'), function (result) {
			if(result) {
				lock_screen(true);

				$.ajax({
					url:		make_url('quests#pet_remove'),
					data:		{quest_id: quest_id, counter: counter},
					type:		'post',
					dataType:	'json',
					success:	function (result) {
						if(result.success) {
							location.href	= make_url('quests#pet');
						} else {
							lock_screen(false);
							format_error(result);
						}
					}
				});
			}
		});
	});	
	// Aceita a missão de pet
		var	pet_quest_list	= $('#pet-quests-list-content');
		pet_quest_list.on('click', '.accept', function () {
			var	id			= $(this).data('id');
			lock_screen(true);

			$.ajax({
				url:		make_url('quests#pet_accept'),
				type:		'post',
				data:		{ quest: id},
				success:	function (result) {
					if(result.success) {
						location.href	= make_url('quests#pet');
					} else {
						lock_screen(false);
						format_error(result);
					}
				}
			});
		});
		
		// Time quests -->
		var	time_quest_list	= $('#time-quests-list-content');
		$('#pvp-quests-list-tabs a').click(function (e) {
			e.preventDefault()
			$(this).tab('show');
		});

		time_quest_list.on('click', '.accept', function () {
			var	id		= $(this).data('id'),
				select	= $('#quest-time-duration-selector-' + id);

			lock_screen(true);

			$.ajax({
				url:		make_url('quests#time_accept'),
				type:		'post',
				data:		{
					quest: id,
					duration: select.val()
				},
				success:	function (result) {
					if (result.success)
						location.href	= make_url('quests#time_wait');
					else {
						lock_screen(false);
						format_error(result);
					}
				}
			});
		});

		time_quest_list.on('change', '.duration-selector', function () {
			var	target	= $('#time-quest-reward-' + $(this).data('id')),
				option	= $(this.options[this.selectedIndex]);

			$('.exp',		target).html(highamount(option.data('exp')));
			$('.currency',	target).html(highamount(option.data('currency')));
		});

		$('#timer-quest-cancel').on('click', function () {
			bootbox.confirm(I18n.t('quests.time.wait.warn'), function (result) {
				if (result) {
					lock_screen(true);

					$.ajax({
						url:		make_url('quests#time_cancel'),
						success:	function (result) {
							location.href	= make_url('characters#status');
						}
					});
				}
			});
		});

		$('#timer-quest-finish').on('click', function () {
			lock_screen(true);

			$.ajax({
				url:		make_url('quests#time_finish'),
				success:	function (result) {
					if(result.success) {
						location.href	= make_url('characters#status');
					} else {
						lock_screen(false);
						format_error(result);
					}
				}
			});
		});
	// <--
		$('#daily_quests_finish').on('click', function () {
			lock_screen(true);

			$.ajax({
				url:		make_url('quests#daily_finish'),
				success:	function (result) {
					if(result.success) {
						location.href	= make_url('quests#daily');
					} else {
						lock_screen(false);
						format_error(result);
					}
				}
			});
		});
	// <--
	// <--
		$('#account_quests_finish').on('click', function () {
			lock_screen(true);

			$.ajax({
				url:		make_url('quests#account_finish'),
				success:	function (result) {
					if(result.success) {
						location.href	= make_url('quests#account');
					} else {
						lock_screen(false);
						format_error(result);
					}
				}
			});
		});
	// <--
	// <--
		$('#organization_daily_quests_finish').on('click', function () {
			lock_screen(true);

			$.ajax({
				url:		make_url('quests#organization_daily_finish'),
				success:	function (result) {
					if(result.success) {
						location.href	= make_url('quests#organization_daily');
					} else {
						lock_screen(false);
						format_error(result);
					}
				}
			});
		});
	// <--
		var	pvp_quest_list	= $('#pvp-quests-list-content');

		$('#time-quests-list-tabs a').click(function (e) {
			e.preventDefault()
			$(this).tab('show');
		});
		$('#guide-category-list-tabs a').click(function (e) {
			e.preventDefault()
			$(this).tab('show');
		});
		$('#guide-subcategory-list-tabs a').click(function (e) {
			e.preventDefault()
			$(this).tab('show');
		});

		pvp_quest_list.on('click', '.accept', function () {
			var	id		= $(this).data('id');

			lock_screen(true);

			$.ajax({
				url:		make_url('quests#pvp_accept'),
				type:		'post',
				data:		{ quest: id },
				success:	function (result) {
					if(result.success) {
						location.href	= make_url('quests#pvp_status');
					} else {
						lock_screen(false);
						format_error(result);
					}
				}
			});
		});

		$('#pvp-quest-cancel').on('click', function () {
			bootbox.confirm(I18n.t('quests.time.wait.warn'), function (result) {
				if (result) {
					lock_screen(true);

					$.ajax({
						url:		make_url('quests#pvp_cancel'),
						success:	function (result) {
							location.href	= make_url('characters#status');
						}
					});
				}
			});
		});

		$('#pvp-quest-finish').on('click', function () {
			lock_screen(true);

			$.ajax({
				url:		make_url('quests#pvp_finish'),
				success:	function (result) {
					if(result.success) {
						location.href	= make_url('characters#status');
					} else {
						lock_screen(false);
						format_error(result);
					}
				}
			});
		});
		
})();