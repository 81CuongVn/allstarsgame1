(function () {
	var	distribute_container	= $('#training-distribute-container');

	function _distribute_points(data) {
		$.ajax({
			url:		make_url('trainings#distribute_attribute'),
			type:		data ? 'post' : 'get',
			dataType:	'json',
			data:		data,
			success:	function (result) {
				character_stats({
					mana:			result.mana,
					max_mana:		result.max_mana,
					stamina:		result.stamina,
					max_stamina:	result.max_stamina
				});

				distribute_container.html(result.view);

				$('.requirement-popover', distribute_container).each(function () {
                    $(this).popover({
						content:	function () {
							return $($(this).data('source')).html();
						},
						html:		true
					});
				});
			}
		})
	}

	if(distribute_container.length) {
		distribute_container.on('click', '.distribute', function () {
			var	_	= $(this);
			var	qty	= parseInt($('[name=' + _.data('attribute') + '_val]', distribute_container).val());

			if(qty) {
				_distribute_points({
					attribute:	_.data('attribute'),
					quantity:	qty
				});				
			}
		});

		distribute_container.on('change', 'select', function () {
			var	_		= $(this);
			var	max		= $('.distribute-general', distribute_container).data('max');
			var	used	= 0;
			var	html	= '<option value="0">' + _.data('default') + '</option>';
			var	size	= 1;
			var	others	= $('select', distribute_container);
			var	reset	= false;

			others.each(function (select) {
				used	+= parseInt(this.value);
			});

			if(used <= max) {
				for(var i = 1; i <= (max - used); i++) {
					html	+= '<option value="' + i + '">' + i + '</option>';
					size++;
				}
			} else {
				reset	= true;
			}

			others.each(function (select) {
				var	equals	= $(this).attr('name') == _.attr('name');

				if(reset && !equals) {
					$(this).html(html);
				} else {
					if(equals || parseInt(this.value)) {
						var	current_value	= this.value;

						if(this.options.length < size) {
							$(this).html(html);
							this.options[current_value].selected	= true;
						}

						return;
					}

					$(this).html(html);
				}
			});
		});

		distribute_container.on('click', '.distribute-general', function () {
			var	post	= {general: 1, data: []};
			var	selects	= $('select', distribute_container);

			selects.each(function () {
				var	_	= $(this);

				post.data.push({attribute: _.attr('name').replace('_val', ''), quantity: this.value});
			});

			_distribute_points(post);
		});

		_distribute_points();
	}

	var	training_attribute	= $('#training-attribute-basic');
	if(training_attribute.length) {
		training_attribute.on('change', 'select', function () {
			var	_ = $(this);

			$('.mana', training_attribute).html(_.val() * _.data('consume-mana'));
			$('.stamina', training_attribute).html(_.val() * _.data('consume-stamina'));
		}).trigger('change');

		training_attribute.on('click', '.train', function () {
			lock_screen(true);

			$.ajax({
				url:		make_url('trainings#train_attribute'),
				data:		$('#training-attribute-basic').serialize(),
				type:		'post',
				dataType:	'json',
				success:	function (result) {
					lock_screen(false);

					if(result.level_redirect) {
						location.href	= make_url('characters#next_level');
						return;
					}

					if(result.success) {
						$('#traning-limit-container').html(result.view);
						_distribute_points();

						character_exp(result.exp_player, result.level_exp, result.level);
						character_stats({
							mana:			result.mana,
							max_mana:		result.max_mana,
							stamina:		result.stamina,
							max_stamina:	result.max_stamina
						});
					} else {
						format_error(result);
					}
				}
			});
		});
	}

	$('#learned-technique-list').on('click', '.train', function () {
		var	_	= $(this);
		lock_screen(true);

		$.ajax({
			url:		make_url('trainings#techniques'),
			data:		{item: _.data('item'), duration: $('#technique-training-duration-' + _.data('item')).val()},
			type:		'post',
			dataType:	'json',
			success:	function (result) {
				if(result.success) {
					location.href	= make_url('trainings#technique_wait')
				} else {
					lock_screen(false);
					format_error(result);
				}
			}
		});
	});
})();