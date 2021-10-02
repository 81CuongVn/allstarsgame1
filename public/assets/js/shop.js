(function () {
	$('#shop-items-container').on('click', '.buy', function () {
		var	_		= $(this);
		var	item	= _.data('item');

		lock_screen(true);

		$.ajax({
			url:		make_url('shop#buy'),
			data:		{
				item: 		item,
				quantity: 	$('#shop-item-quantity-select-' + item).val(),
				method:		$('#shop-items-container input[name=method_' + item + ']:checked').val()
			},
			type:		'post',
			dataType:	'json',
			success:	function (result) {
				lock_screen(false);

				if(result.success) {
					jalert(result.message);
					$('#shop-item-quantity-' + _.data('item')).html('x' + result.quantity);

					character_stats({
						currency:	result.currency
					});
				} else {
					format_error(result);
				}
			}
		})
	});
	$('#shop-map-container').on('click', '.buy', function () {
		var	_		= $(this);
		var	item	= _.data('item');

		lock_screen(true);

		$.ajax({
			url:		make_url('shop#map_buy'),
			data:		{item: item},
			type:		'post',
			dataType:	'json',
			success:	function (result) {
				lock_screen(false);

				if(result.success) {
					location.href	= make_url('shop/map');
				} else {
					format_error(result);
				}
			}
		})
	});

	$('#shop-items-container').on('change', '.quantity', function () {
		var	_			= $(this);
		var	currency	= _.data('price-currency');
		var	credits		= _.data('price-credits');
		var	item		= _.data('item');

		if(currency) {
			var	container	= $('#shop-item-currency-value-' + item);
			container.html(container.data('currency') + ' ' + (currency * _.val()));
		}

		if(credits) {
			var	container	= $('#shop-item-credits-value-' + item);
			container.html(container.data('currency') + ' ' + (credits * _.val()));
		}
	});

	$('#shop-items-container td input[type=radio]:first-child').attr('checked', 'checked');
})();
