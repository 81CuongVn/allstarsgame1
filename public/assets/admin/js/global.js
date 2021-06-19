(() => {
	window.makeUrl		= (to) => {
        if (!to)
            return _site_url;

        return _site_url + (_rewrite_enabled ? '' : '/index.php') + '/' + to.split('#').join('/');
    };

    window.imageUrl		= (to) => {
        return _site_url + '/assets/images/' + to;
    };

    window.resourceUrl	= (to) => {
        return _site_url + '/assets/' + to;
    };

    window.absoluteUrl	= (to) => {
        return _site_url + '/' + to;
    };

	window.formatError	= (errors) => {
		const messages	= [];
		errors.forEach((message) => {
			messages.push(message + '<br />');
		});

		return messages.join('');
	};

	window.jAlert		= (message, success, callback) => {
		Swal.fire({
			title:	(success ? 'Parabéns' : 'Que Pena!'),
			html:	message,
			type:	(success ? 'success' : 'error'),
			confirmButtonClass: 'btn btn-confirm mt-2'
		}).then(() => {
			if (callback) {
				callback.apply(null, []);
			}
		});
    };

	window.jConfirm		= (ok_callback, cancel_callback) => {
		Swal.fire({
			title: 'Tem certeza?',
			html: "Você não poderá reverter isso!",
			type: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Sim, continuar!',
			cancelButtonText: 'Não, cancelar!',
			confirmButtonClass: 'btn btn-success mt-2',
			cancelButtonClass: 'btn btn-danger ml-2 mt-2',
			buttonsStyling: false
		}).then((result) => {
			if (result.value) {
				if (ok_callback) {
					ok_callback.apply(null, []);
				}
			} else if (result.dismiss === Swal.DismissReason.cancel) {
				if (cancel_callback) {
					cancel_callback.apply(null, []);
				}
			}
		});
    };

	window.blockForm	= (form, status) => {
		if (status) {
			$('input, textarea, button, select', form).attr('disabled', true);
		} else {
			$('input, textarea, button, select', form).removeAttr('disabled');
		}
	};
    window.lockScreen	= (show) => {
        if (show) {
            var d	= $(document.createElement('DIV')).addClass('screen-lock');
            var dd	= $(document.createElement('DIV')).addClass('screen-lock-text');

            dd.html('<i class="fa fa-spinner fa-pulse fa-fw"></i> Aguarde...');

            $(document.body).append(d, dd).css('overflow', 'hidden');

            if (!window.has_screen_lock_callback) {
                window.has_screen_lock_callback	= true;

                $(window).on('resize', () => {
                    $('.screen-lock')
                        .css('width', $(window).width())
                        .css('height', $(window).height());
                });
            }
        } else {
            $(document.body).css('overflow', 'auto');
            $('.screen-lock, .screen-lock-text').remove();
        }
    };

	window.$id = () => {
        return 'id-' + parseInt(Math.random() * 512384).toString(16) + '-' + parseInt(Math.random() * 512384).toString(16) + '-' + parseInt(Math.random() * 512384).toString(16) + '-' + parseInt(Math.random() * 512384).toString(16);
    }

	window.highamount	= (number, decimals, dec_point, thousands_sep) => {
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = (typeof thousands_sep === 'undefined') ? '.' : thousands_sep,
			dec = (typeof dec_point === 'undefined') ? ',' : dec_point,
			s = '',
			toFixedFix = (n, prec) => {
				var k = Math.pow(10, prec);
				return '' + Math.round(n * k) / k;
			};
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || '').length < prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1).join('0');
		}
		return s.join(dec);
	}
})()
