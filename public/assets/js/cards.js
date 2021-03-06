function cardize(container) {
	container			= $(container);
	var cards			= $('.card', container);
	var	last_card		= $(cards[cards.length - 1]);
	var	first_card		= $(cards[0]);
	var	width			= first_card.width();
	var	spacer			= (container.width() - width) / (cards.length - 1);
	var	have_i_clicked	= false;

	for(var i = 0; i < cards.length; i++) {
		var	card	= $(cards[i]);

		if(i == cards.length - 1) {
			card.data('last', true);
		} else if(i == 0) {
			card.data('first', true);
		}

		card.css({
			marginLeft:	spacer * i
		}).data('index', i).data('margin', spacer * i);
	}

	cards.on('click', function () {
		have_i_clicked	= !have_i_clicked;
	});

	cards.on('mouseover', function () {
		var	_		= $(this);
		var	counter	= 0;

		if(_.data('last') || have_i_clicked) {
			return;
		}

		if(_.data('index') > cards.length / 2) {
			var	dest_margin		= _.data('margin') - _.width() + spacer;
			var start_pixel		= 0;
			var	current_spacer	= dest_margin / _.data('index');
			var	start_index		= 1;
			var	last_index		= _.data('index');

			_.stop().animate({marginLeft: dest_margin});
		} else {
			var start_pixel		= _.data('margin') + _.width();
			var	current_spacer	= (last_card.data('margin') - start_pixel) / (cards.length - _.data('index'));
			var	start_index		= _.data('index') + 1;
			var last_index		= cards.length - 1;
		}

		for(var i = start_index; i < last_index; i++) {
			var	_this	= $(this);

			cards.each(function () {
				var	card	= $(this);

				if(card.data('index') == i) {
					counter++;
					card.stop().animate({marginLeft: start_pixel + (current_spacer * counter)});
				}
			});
		}
	}).on('mouseout', function () {
		if(have_i_clicked) {
			return;
		}

		cards.each(function () {
			var	card	= $(this);

			card.stop().animate({marginLeft: card.data('margin')});
		});
	});
}