(function() {
	var treino_stamina = $('#treino-stamina-filter-form');
	var is_learning = false;
	var source_types = null;
	var source_drop = null;

	// Remove um pet de uma missão com o botão direito
	$('.remove-gem').bind('click', function() {

		var item_id = $(this).data('item');
		var counter = $(this).data('counter');

		bootbox.confirm($(this).data('message'), function(result) {
			if (result) {
				lock_screen(true);

				$.ajax({
					url: make_url('techniques#remove_gem'),
					data: {
						item_id: item_id,
						counter: counter
					},
					type: 'post',
					dataType: 'json',
					success: function(result) {
						if (result.success) {
							location.href = make_url('techniques#enchant');
						} else {
							lock_screen(false);
							format_error(result);
						}
					}
				});
			}
		});
	});
	//Adiciona o Golpe para ser Encantado na página
	$('.change_golpe_enchant').on('click', function() {
		var _ = $(this);
		var win = bootbox.dialog({
			message: '...',
			buttons: [{
				label: 'Fechar',
				class: 'btn btn-sm btn-default'
			}]
		});

		$('.modal-dialog', win).addClass('pattern-container');
		$('.modal-content', win).addClass('with-pattern');

		$.ajax({
			url: _.data('url'),
			type: 'get',

			success: function(result) {
				$('.bootbox-body', win).html(result);

				// This one is for the images
				$('.modal-content', win).on('click', '.enchant-box', function() {
					win.modal('hide');
					lock_screen(true);

					$.ajax({
						url: make_url('techniques#list_golpes'),
						type: 'post',
						data: {
							item_id: $(this).data('item')
						},
						dataType: 'json',
						success: function(result) {
							if (result.success) {
								location.href = make_url('techniques#enchant');
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

	// Cria uma joia
	$('.msg-container .create_gem').on('click', function() {
		lock_screen(true);

		$.ajax({
			url: make_url('techniques#create_gem'),
			type: 'post',
			data: {
				create: 1
			},
			dataType: 'json',
			success: function(result) {
				if (result.success) {
					location.href = make_url('techniques#enchant?joia=' + result.premio);
				} else {
					lock_screen(false);
					format_error(result);
				}
			}
		});
	});

	// Encanta finalmente o golpe
	$('.enchant .enchant_item_gem').on('click', function() {
		lock_screen(true);
		var _ = $(this);

		$.ajax({
			url: make_url('techniques#enchant_golpe'),
			type: 'post',
			data: {
				item_id: _.data('item'),
				enchanted_id: _.data('enchanted'),
				combination: _.data('counter')
			},
			dataType: 'json',
			success: function(result) {
				if (result.success) {
					location.href = make_url('techniques#enchant');
				} else {
					lock_screen(false);
					format_error(result);
				}
			}
		});
	});

	// Faz o treino diario dos encantamentos	
	treino_stamina.on('click', '.filter', function() {
		lock_screen(true);
		var _ = $(this);

		$.ajax({
			url: make_url('techniques#enchant_trainner'),
			data: {
				stamina: $("#sltTreinoStamina").val()
			},
			dataType: 'json',
			type: 'post',
			success: function(result) {
				if (result.success) {
					location.href = make_url('techniques#enchant');
				} else {
					lock_screen(false);
					format_error(result);
				}
			}
		});
	});

	$('#eqquiped-technique-list .item').droppable({
		drop: function(e, ui) {
			// alert('Dropo merda');
			// return ;
			var old_item = $('.item-content', this);
			var _ = $(this);
			$(this).append(ui.draggable[0].outerHTML);

			if (old_item) {
				$('#technique-dropsource-' + old_item.data('item'))
					.append(old_item).
				attr('class', _.attr('class').replace('dropzone', ''));

				apply_drag_cb();
			}

			$('.item-content', this).attr('style', '');
			ui.draggable.remove();

			_.attr('class', source_types + ' dropzone');

			$.ajax({
				url: make_url('techniques/learn'),
				data: {
					slot: $(this).data('slot'),
					item: ui.draggable.data('item')
				},
				type: 'post',
				dataType: 'json',
				success: function(result) {
					if (!result.success) {
						format_error(result);
					} else
						location.reload();
				}
			});
		}
	})

	$('#technique-list .item-content').on('click', function() {
		// alert($(this).data('item'));
	});

	function apply_drag_cb() {
		$('#technique-list .item-content').each(function() {
			var _ = $(this);

			if (this._with_drag || _.hasClass('locked')) {
				return
			}

			this._with_drag = false;

			$(this).draggable({
				accept: '.dropzone',
				revert: true,
				start: function() {
					var parent = $(this).parent();
					source_drop = parent;
					source_types = parent.attr('class');

					parent.addClass('dropping');
				},
				stop: function() {
					$(this).parent().removeClass('dropping');
				}
			});
		});
	}

	apply_drag_cb();
	// Adiciona elas realmente no slot
	$('.enchant .enchant-item').droppable({
		drop: function(e, ui) {
			var old_item = $('.item-content-gem', this);
			var _ = $(this);
			$(this).append(ui.draggable[0].outerHTML);

			if (old_item) {
				$('#technique-dropsource-' + old_item.data('item'))
					.append(old_item).
				attr('class', _.attr('class').replace('dropzone', ''));

				apply_drag_cb_gem();
			}

			$('.item-content-gem', this).attr('style', '');
			ui.draggable.remove();

			_.attr('class', source_types + ' dropzone');

			$.ajax({
				url: make_url('techniques/equip_gem'),
				data: {
					slot: $(this).data('slot'),
					item: ui.draggable.data('item')
				},
				type: 'post',
				dataType: 'json',
				success: function(result) {
					if (!result.success) {
						format_error(result);
					} else {
						location.href = make_url('techniques#enchant');
					}
				}
			});
		}
	})

	// Aplica as joias nos slots
	function apply_drag_cb_gem() {
		$('#technique-list .item-content-gem').each(function() {
			var _ = $(this);

			if (this._with_drag || _.hasClass('locked')) {
				return
			}

			this._with_drag = false;

			$(this).draggable({
				accept: '.dropzone',
				revert: true,
				start: function() {
					var parent = $(this).parent();
					source_drop = parent;
					source_types = parent.attr('class');

					parent.addClass('dropping');
				},
				stop: function() {
					$(this).parent().removeClass('dropping');
				}
			});
		});
	}

	apply_drag_cb_gem();

	var technique_wait_timer = $('#technique-wait-timer');

	if (technique_wait_timer.length) {
		create_timer(
			technique_wait_timer.data('hours'),
			technique_wait_timer.data('minutes'),
			technique_wait_timer.data('seconds'),
			'technique-wait-timer',
			function() {
				location.reload()
			},
			null,
			true
		);
	}

	$('#technique-training-status-container .cancel').on('click', function() {
		jconfirm($(this).data('confirmation'), function() {
			lock_screen(true);

			$.ajax({
				url: make_url('trainings#technique_wait'),
				type: 'post',
				data: {
					cancel: 1
				},
				dataType: 'json',
				success: function(result) {
					if (result.success) {
						location.href = make_url('trainings#techniques');
					} else {
						lock_screen(false);
						format_error(result);
					}
				}
			});
		});
	});

	$('#technique-training-status-container .finish').on('click', function() {
		lock_screen(true);

		$.ajax({
			url: make_url('trainings#technique_wait'),
			type: 'post',
			data: {
				finish: 1
			},
			dataType: 'json',
			success: function(result) {
				if (result.success) {
					location.href = make_url('trainings#techniques');
				} else {
					lock_screen(false);
					format_error(result);
				}
			}
		});
	});
	// Desbloqueia um golpe do grimorio
	$('.player-item-finish').on('click', function() {
		var id = $(this).data('id');
		lock_screen(true);

		$.ajax({
			url: make_url('techniques#learn_grimoire'),
			type: 'post',
			data: {
				id: id
			},
			success: function(result) {
				if (result.success) {
					location.href = make_url('techniques#grimoire');
				} else {
					lock_screen(false);
					format_error(result);
				}
			}
		});
	});
	// Fazendo um evento com o botão direito do mouse

	var equip_ability_speciality = function (element) {
		if (element.hasClass('ability'))
			var url = make_url('techniques#learn_ability');
		else
			var url = make_url('techniques#learn_speciality');

		lock_screen(true);

		$.ajax({
			url: url,
			dataType: 'json',
			type: 'post',
			data: {
				id: element.data('id')
			},
			success: function(response) {
				if (response.success)
					location.href = make_url('techniques#abilities_and_specialities');
				else {
					lock_screen(false);
					format_error(response);
				}
			}
		})
	};
	var modify_ability_speciality = function (element) {
		var win = bootbox.dialog({
			message: '...',
			buttons: [{
				label: 'Fechar',
				class: 'btn btn-sm btn-default'
			}]
		});

		$('.modal-dialog', win).addClass('pattern-container');
		$('.modal-content', win).addClass('with-pattern');

		$.ajax({
			url: element.data('url'),
			type: 'get',
			data: {
				id: element.data('id')
			},

			success: function(result) {
				$('.bootbox-body', win).html(result);

				// This one is for the images
				$('.modal-content', win).on('click', '.upgrade', function() {
					win.modal('hide');
					lock_screen(true);

					$.ajax({
						url: make_url(element.data('url2')),
						type: 'post',
						data: {
							id: $(this).data('id'),
							id2: $(this).data('id2')
						},
						dataType: 'json',
						success: function(result) {
							if (result.success)
								location.href = make_url('techniques/abilities_and_specialities');
							else {
								lock_screen(false);
								format_error(result);
							}
						}
					});

				});
			}
		});
	};

	$('#ability-speciality-list').on('click', '.ability-speciality-box', function() {
		var element	= $(this),
			buttons	= [];

		if (!element.hasClass('disabled') && !element.hasClass('active'))
			buttons.push({
				label: I18n.t('abilities.show.learn'),
				className: ' btn-sm btn-primary',
				callback: function() {
					equip_ability_speciality(element);
					return false;
				}
			});

		buttons.push({
			label: I18n.t('abilities.show.modify'),
			className: 'btn btn-sm btn-danger',
			callback: function() {
				modify_ability_speciality(element);
				return false;
			}
		}, {
			label: I18n.t('global.close'),
			className: 'btn btn-sm'
		});
		
		bootbox.dialog({
			message: I18n.t('abilities.show.click_text_' + (element.hasClass('ability') ? 'ability' : 'speciality')),
			buttons: buttons
		});
	});
})();