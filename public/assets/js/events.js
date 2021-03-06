(function () {
	var	container	= $('#wanteds-filter-form');
	var	wanteds	= $('#wanteds-filter-form');

	if(wanteds.length) {
		wanteds.on('click', '.pagination a', function () {
			lock_screen(true);
			$('[name=page]', wanteds).val($(this).data('page') - 1);

			wanteds[0].submit();
		});

		wanteds.on('click', '.filter', function () {
			$('[name=page]', wanteds).val(0);
			wanteds[0].submit();
		});
	}
	container.on('click', '.bg-wanteds', function (e) {
		if(e.shiftKey && typeof ChatService !== 'undefined') {
			ChatService.embed($(this).data('embed'), '[wanted:' + $(this).data('id') + ']');
			return;
		}
	});

	if (typeof ChatService !== 'undefined') {
		var	expression	= /\[wanted\:(\d*)\]/i;
		ChatService.register_embed_cb(expression, function (element, data) {
			if(data) {
				element.html(element.html().replace(expression, '<span class="vermelho embed embed-procurado" data-id="$1" data-name="'+ data.name +'" data-type="'+ data.type +'" data-won="'+ data.won +'" data-character="'+ data.character +'">[Procurado: '+ data.name +']</span>'));

				$('.embed-procurado').each(function () {
					if(this.with_callback) {
						return;
					}

					attach_wanted_popver($(this), null, true);
				});
			}
		});
	}

	window.attach_wanted_popver	= function (source, comparison, is_chat) {
		source.popover({
			html:		true,
			trigger:	is_chat ? 'manual' : 'hover',
			title:		'',
			placement:	is_chat ? 'right' : 'bottom',
			content:	'<div class="bg-wanteds" style="margin: 5px 10px;">' +
						'<div class="wanteds-foto">' +
							'<img src="' + image_url('wanted/' + source.data('character') + '.jpg') + '" width="139" height="107" />' +
						'</div>' +
						'<div class="wanteds-info">' +
							'<b style="font-size:16px">' + source.data('name') +'</b><br />' +
							'<span>' + source.data('type')+' </span><br />' +
							'<span style="font-size:14px">' + source.data('won') +'</span>' +
						'</div>' +
					'</div><div style="clear:both"></div>'
		}).on("mouseenter", function () {
			var _this = this;

			if (is_chat) {
				$(this).popover("show");
				$(this).siblings(".popover").css({
					position:	'fixed',
					left:		'270px',
					top:		$(window).height() - 350
				});

				$(document.body).append($(this).siblings(".popover"));
			}
		}).on("mouseleave", function () {
			if (is_chat) {
				$(this).popover("hide");
			}
		});
	}
	// Fidelity
	$('.reward_fidelity').on('click', function () {
			lock_screen(true);
			var	_	= $(this);

			$.ajax({
				url:		make_url('events#reward_fidelity'),
				data:		{ day: _.data('day')},
				dataType:	'json',
				type:		'post',
				success:	function (result) {
					if(result.success) {
						location.href	= make_url('events#fidelity');
					} else {
						lock_screen(false);
						format_error(result);
					}
				}
			});
		});
	// Troca de Prêmios por Round
	$('.objective_change').on('click', function () {
		
			lock_screen(true);
			var	_	= $(this);

			$.ajax({
				url:		make_url('events#objective_reward'),
				data:		{ id: _.data('id')},
				dataType:	'json',
				type:		'post',
				success:	function (result) {
					if(result.success) {
						location.href	= make_url('events#objectives');
					} else {
						lock_screen(false);
						format_error(result);
					}
				}
			});
		});
			
})();