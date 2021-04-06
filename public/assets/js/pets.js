(function () {
	var learn_pet = function(element) {
		lock_screen(true);

		$.ajax({
			url:		make_url('characters/learn_pet'),
			dataType:	'json',
			type:		'post',
			data:		{ id: element.data('item') },
			success:	function (response) {
				if (response.success) {
					location.reload();
				} else {
					lock_screen(false);
					format_error(response);					
				}
			}
		});
	};
	var remove_pet = function(element) {
		lock_screen(true);

		$.ajax({
			url:		make_url('characters#remove_pet'),
			type:		'post',
			dataType:	'json',
			data:		{id: element.data('item')},
			success:	function (result) {
				if(result.success) {
					location.reload();
				} else {
					lock_screen(false);
					format_error(result);
				}
			}
		});
	};
	$('#pet-list').on('click', '.pet-box', function () {
		var element	= $(this),
			buttons	= [];

		if (!element.hasClass('disabled') && !element.hasClass('active')) {
			buttons.push({
				label: I18n.t('pets.show.equip'),
				className: 'btn btn-sm btn-primary',
				callback: function() {
					learn_pet(element);

					win.modal('hide');
					return;
				}
			})
		} else {
			buttons.push({
				label: I18n.t('pets.show.unequip'),
				className: 'btn btn-sm btn-danger',
				callback: function() {
					remove_pet(element);

					win.modal('hide');
					return;
				}
			})
		}
		buttons.push({
			label: I18n.t('global.close'),
			className: 'btn btn-sm'
		});

		var win = bootbox.dialog({
			message: I18n.t('pets.show.click_text'),
			buttons: buttons
		});
	});
})();