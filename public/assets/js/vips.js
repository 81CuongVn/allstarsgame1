(function () {
	// Ativa ou Desativa a vantagem de remover os talentos dos caras em batalha.
	$('.item-vip-list').on('click','.no-talent', function () {
		lock_screen(true);
		var	_	= $(this);

		$.ajax({
			url:		make_url('vips#no_talent'),
			data:		{ id: _.data('id')},
			dataType:	'json',
			type:		'post',
			success:	function (result) {
				if(result.success) {
					location.href	= make_url('vips');
				} else {
					lock_screen(false);
					format_error(result);
				}
			}
		});
	});
	$(".item-vip-list, .menu").on("click", ".buy", function () {
		lock_screen(true);

		$.ajax({
			url:		make_url("vips#buy"),
			data:		$("#vip-form-" + $(this).data("id")).serialize(),
			type:		"post",
			dataType:	"json",
			success:	function (result) {
				lock_screen(false);

				if (result.success) {
					location.reload();
				} else {
					format_error(result);
				}
			}
		});
	});
	$('.tab-content').on('click','.vip_purchase', function () {
		var _ = $(this);
		bootbox.confirm($(this).data('message'), function (result) {
			if(result) {
				lock_screen(true);

				$.ajax({
					url:		make_url('vips#pay_donation'),
					data:		{ mode: _.data('mode'), valor: _.data('valor')},
					type:		'post',
					dataType:	'json',
					success:	function (result) {
						if(result.success) {
							location.href	= make_url('vips#done_donation');
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