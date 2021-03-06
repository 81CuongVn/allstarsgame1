(function () {
	var	running			= false;
	var	currency		= null;
	var	type_reward		= null;
	var	type			= null;
	var expect			= [];
	var	cur_day			= 0;
	var result_message	= '';
	
	
	$('#type_reward').on('change', function() {
		if(this.value=='character_theme'){
			$('#luck-mask2').css("background-image", "url(" + image_url('summon/bg3.png') + ")");
			type_reward = $("#type_reward").val();  
		}else{
			$('#luck-mask2').css("background-image", "url(" + image_url('summon/bg.png') + ")");
			type_reward = $("#type_reward").val();
		}
	 	 //alert( this.value ); // or $(this).val()
	});
	
	var	luck_list	= $('#luck-list-content');

		$('#luck-list-tabs a').click(function (e) {
			e.preventDefault()
			$(this).tab('show');
		});

	function _roll() {
		var	counter	= 1;
		var	stopped	= 0;

		//$('#luck-container #result').html(result_message).animate({top: '424px'});

		for(var i = 1; i <= 4; i++) {
			setTimeout(function () {
				var	stop	= false;
				var	current	= counter.toString();
				var	strip	= $('#luck-stripe-' + counter).css({backgroundImage: 'url(' + _site_url + '/assets/images/luck/slot_blured.jpg' + ')'});
				var	pos		= -(Math.random() * 3000);
				var	speed	= 70;
				var	iv		= setInterval(function () {
					pos	-= speed;

					var	top1	= (expect[current - 1] - 1) * 141;
					var	top2	= (expect[current - 1] - 1) * 141 + 20;

					strip.css({
						backgroundPosition: '0px ' + pos + 'px'
					});

					if(stop && Math.abs(pos) >= top1 && Math.abs(pos) <= top2) {
						clearInterval(iv);
						stopped++;

						if(stopped >= 4) {
							running = false;

							$('#luck-container .day-' + cur_day).addClass('green');
							$('#luck-container #result').html(result_message);//.animate({top: '477px'});
						}

						strip.css({backgroundPosition: '0px -' + (top1) + 'px'});
					}

					if(Math.abs(pos) >= 4512) {
						if(speed > 30) {
							pos	= -(Math.random() * 3000);
						} else {
							pos	= 0;
						}
					}
				}, 50);

				setTimeout(function () {
					speed	= 50;
				}, 1000);

				setTimeout(function () {
					speed	= 40;
				}, 2000);

				setTimeout(function () {
					speed	= 30;
					strip.css({backgroundImage: 'url(' + _site_url + '/assets/images/luck/slot.jpg' + ')'});
				}, 2500);

				setTimeout(function () {
					speed	= 20;
					stop	= true;
				}, 3000);

				setTimeout(function () {
					speed	= 15;
				}, 4000);

				counter++;
			}, (500 + (i * (Math.random() * 1000)) / 2));
		}
	}
	function _roll2() {
		var	counter	= 1;
		var	stopped	= 0;

		//$('#luck-container #result').html(result_message).animate({top: '424px'});

		for(var i = 1; i <= 4; i++) {
			setTimeout(function () {
				var	stop	= false;
				var	current	= counter.toString();
				var	strip	= $('#luck-stripe-' + counter).css({backgroundImage: 'url(' + _site_url + '/assets/images/summon/slot_blured.png' + ')'});
				var	pos		= -(Math.random() * 3000);
				var	speed	= 70;
				var	iv		= setInterval(function () {
					pos	-= speed;

					var	top1	= (expect[current - 1] - 1) * 65;
					var	top2	= (expect[current - 1] - 1) * 65 + 20;

					strip.css({
						backgroundPosition: '0px ' + pos + 'px'
					});

					if(stop && Math.abs(pos) >= top1 && Math.abs(pos) <= top2) {
						clearInterval(iv);
						stopped++;

						if(stopped >= 4) {
							running = false;

							$('#luck-container .day-' + cur_day).addClass('green');
							$('#luck-container .luck-result').html(result_message);//.animate({top: '477px'});
						}

						strip.css({backgroundPosition: '0px -' + (top1) + 'px'});
					}

					if(Math.abs(pos) >= 508) {
						if(speed > 30) {
							pos	= -(Math.random() * 3000);
						} else {
							pos	= 0;
						}
					}
				}, 50);

				setTimeout(function () {
					speed	= 50;
				}, 1000);

				setTimeout(function () {
					speed	= 40;
				}, 2000);

				setTimeout(function () {
					speed	= 30;
					strip.css({backgroundImage: 'url(' + _site_url + '/assets/images/summon/slot.png' + ')'});
				}, 2500);

				setTimeout(function () {
					speed	= 20;
					stop	= true;
				}, 3000);

				setTimeout(function () {
					speed	= 15;
				}, 4000);

				counter++;
			}, (500 + (i * (Math.random() * 1000)) / 2));
		}
	}
	$('#summon-button').on('click', function () {
		if (running) {
			return;
		}

		running		= true;

		$.ajax({
			url:		make_url('luck#summoning'),
			type:		'post',
			dataType:	'json',
			data:		{currency: currency, type_reward: type_reward},
			success:	function (result) {
				if(result.success) {
					expect			= result.slot;
					cur_day			= result.today;
					result_message	= result.message;

					$('.top-expbar-container .currency').html(result.currency);
					$('#top-container .credits').html(result.credits);

					_roll2();
				} else {
					format_error(result);

					running	= false;
				}
			}
		});
	});
	
	$('#luck-button').on('click', function () {
		if (running) {
			return;
		}

		running		= true;

		$.ajax({
			url:		make_url('luck#roll'),
			type:		'post',
			dataType:	'json',
			data:		{currency: currency, type: type},
			success:	function (result) {
				if(result.success) {
					expect			= result.slot;
					cur_day			= result.today;
					result_message	= result.message;

					$('.top-expbar-container .currency').html(result.currency);
					$('#top-container .credits').html(result.credits);

					_roll();
				} else {
					format_error(result);

					running	= false;
				}
			}
		});
	});

	$("#luck-container #buttons").on('click', '.button', function () {
		var	_	= $(this);

		currency	= _.data('currency');
		type		= _.data('type');

		$("#luck-container #buttons .button").removeClass('selected');
		_.addClass('selected');
	});

	$("#luck-container .button:first").trigger('click');
	
	$("#luck-container #luck-buy").on('click', '.summon-button', function () {
		var	_	= $(this);

		currency	= _.data('currency');
		type_reward = $("#type_reward").val();
		
		$("#luck-container #luck-buy .summon-button").removeClass('selected');
		_.addClass('selected');

	});
	$("#luck-container .summon-button:first").trigger('click');
})();