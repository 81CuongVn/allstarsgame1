(function () {
	window.lock_screen	= function (show) {
		if(show) {
			var d	= $(document.createElement('DIV')).addClass('screen-lock');
			var dd	= $(document.createElement('DIV')).addClass('screen-lock-text');
			
			dd.html('<span class="glyphicon glyphicon-refresh"></span>&nbsp;Aguarde...');
			
			$(document.body).append(d, dd).css('overflow', 'hidden');
			
			if(!window.has_screen_lock_callback) {
				window.has_screen_lock_callback	= true;
				
				$(window).on('resize', function () {
					$('.screen-lock')
						.css('width', $(window).width())
						.css('height', $(window).height());
				});
			}
		} else {
			$(document.body).css('overflow', 'auto');
			$('.screen-lock, .screen-lock-text').remove();
		}
	}

	window.format_error	=	function (result) {
		var errors	= [];
		var	win		= bootbox.dialog({message: '...', buttons: [
			{
				'label': 'Fechar'
			}
		]});

		(result.errors || result.messages).forEach(function (error) {
			errors.push('<li>' + error + '</li>');
		});

		$('.bootbox-body', win).html('<h3>Os seguintes erros impediram de salvar os dados atuais:</h3><ul>' + errors.join('') + '</ul>')
		$('.modal-body', win).css('border-top', 'solid 6px #F00');
	}
})();