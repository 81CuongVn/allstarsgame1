(function () {
	$('#hospital-heal-button').on('click', function () {
		bootbox.confirm(I18n.t('hospital.confirm_payment'), function (result) {
			if(result) {
				lock_screen(true);

				$.ajax({
					url:		make_url('hospital#heal'),
					type:		'post',
					dataType:	'json',
					success:	function (result) {
						if(result.success) {
							location.href	= make_url('characters#status');
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