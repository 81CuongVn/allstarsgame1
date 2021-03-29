(function () {
	var	locked				= [];
	var	battle_container	= $('#battle-container');
	var	log_container		= $('.log', battle_container);
	var	all_loaded			= false;
	var	images_to_load		= 0;
	var	load_count			= 0;
	var	lcanvas				= null;
	var	rcanvas				= null;
	var max_log_scroll		= 0;
	var	current_log_scroll	= 0;
	var	ping_iv				= null;
	var	can_ping			= true;
	var	timer_mins			= 1;
	var	timer_secs			= 30;
	var	battle_timer_iv		= 0;
	var	sound_was_played	= false;
	var	audio				= $(document.createElement('AUDIO')).attr('src', resource_url('media/battle.mp3')).attr('type', 'audio/mpeg');
	var negatives			= ['attack_speed', 'attack_speed_percent', 'slowness', 'slowness_percent', 'bleeding', 'bleeding_percent', 'next_mana_cost', 'stun','reduce_critical_damage','reduce_critical_damage_percent'];
	var hidden				= ['bonus_exp_mission','bonus_exp_mission_percent','bonus_gold_mission','bonus_gold_mission_percent','bonus_stamina_max','currency_reward_extra','exp_reward_extra','currency_reward_extra_percent','exp_reward_extra_percent','bonus_stamina_heal','no_consume_stamina','fragment_find','item_find','pets_find'];
	var	images				= {
		rn:	{element: null, url: 'battle/bars/battle_lb_right.png', loaded: false},
		rf:	{element: null, url: 'battle/bars/battle_lb_right_fill.png', loaded: false}
	};

	function update_log_tooltip() {
		$('.log .i', battle_container).each(function () {
			var	_	= $(this);

            _.popover({
				content:	$(document.getElementById(_.data('tooltip'))).html(),
				html:		true,
				placement:	'bottom',
				trigger:	'hover'
			});
		});
	}

	function draw_modifiers(objekt, status, container) {
		$('.item', container).remove();

		var	item	= $(document.createElement('DIV')).addClass('item status');
		var	item_id	= 'i-' + (Math.random() * 65535) + '.' + (Math.random() * 65535);
		var	popover	= $(document.createElement('DIV')).attr('id', item_id).css({display: 'none'});
		var	html	= '<div class="modifier-tooltip">' + I18n.t('battles.status_tooltip.atk', {image: image_url('icons/for_atk.png'), value: status.atk}) + "<br />" +
					  I18n.t('battles.status_tooltip.def', {image: image_url('icons/for_def.png'), value: status.def}) + "<br />" +
					  I18n.t('battles.status_tooltip.crit', {image: image_url('icons/for_crit.png'), value: status.crit, inc: status.crit_inc}) + "<br />" +
					  I18n.t('battles.status_tooltip.abs', {image: image_url('icons/for_abs.png'), value: status.abs, inc: status.abs_inc}) + "<br />" +
					  I18n.t('battles.status_tooltip.prec', {image: image_url('icons/for_prec.png'), value: status.prec}) + "<br />" +
					  I18n.t('battles.status_tooltip.init', {image: image_url('icons/for_inti.png'), value: (status.init).toFixed(2), init: status.init.toFixed(2)}) + "<br />";

		item.append('<img src="' + image_url('battle/details.png') + '" class="technique-popover" data-placement="' + container.data('placement') + '" data-source="' + item_id + '" data-trigger="hover" data-placement="bottom"  />')
		item.append(popover);
		popover.append(html);
		container.append(item);

		if(objekt.mods && objekt.mods.length) {
			objekt.mods.forEach(function (mod) {
				if(mod.infinity) {
					$('#activatables #infinity-container-' + mod.id + ' .technique-popover img', container.parent()).css('opacity', 1);
				} else {
					var	item	= $(document.createElement('DIV')).addClass('item');
					var	item_id	= 'i-' + (Math.random() * 65535) + '.' + (Math.random() * 65535);
					var	popover	= $(document.createElement('DIV')).attr('id', item_id).css({display: 'none'});
					var	html	= '<div class="modifier-tooltip">' + mod.tooltip + '</div>';

					item.append('<img src="' + image_url(mod.image) + '" width="24" height="24" class="technique-popover" data-placement="' + container.data('placement') + '" data-source="' + item_id + '" data-title="' + mod.name + '" data-trigger="click" data-placement="bottom"  />')
					item.append(popover);
					popover.append(html);
					container.append(item);
				}
			});
		}

		$('.item img', container).each(function () {
			var	_	= $(this);

            _.popover({
				content:	function () {
					return $(document.getElementById($(this).data('source'))).html();
				},
				html:		true,
				placement:	_.data('placement'),
				trigger:	'hover'
			});
		});
	}

	function draw_faction(target) {
		var container	= $('#battle-container #' + target + ' .modifiers');

		$('.faction', container).remove();

		var	item	= $(document.createElement('DIV')).addClass('faction status').css('margin-bottom', '5px');
		var	item_id	= 'i-' + (Math.random() * 65535) + '.' + (Math.random() * 65535);
		var	popover	= $(document.createElement('DIV')).attr('id', item_id).css({display: 'none'});
		var faction			= parseInt($('#battle-container #' + target).data('faction'));
		var organization	= $('#battle-container #' + target).data('organization');
		var	html	= I18n.t('characters.select.labels.faction') + ': ' + I18n.t('factions.' + faction);
		html = html +' <br /> ' + I18n.t('global.guild') + ': ' + organization ;
	
		item.append('<img src="' + image_url('factions/icons/small/' + faction + '.png') + '" class="technique-popover" data-placement="' + container.data('placement') + '" data-source="' + item_id + '" data-trigger="hover" data-placement="bottom"  />')
		item.append(popover);
		popover.append('<div class="modifier-tooltip">' + html + '</div>');
		container.append(item);

		$('img', item).each(function () {
			var	_	= $(this);

            _.popover({
				content:	function () {
					return $(document.getElementById($(this).data('source'))).html();
				},
				html:		true,
				placement:	_.data('placement'),
				trigger:	'hover'
			});
		});
		
	}
	function draw_wanted(target) {
		var container	= $('#battle-container #' + target + ' .modifiers');

		$('.wanted', container).remove();

		var	item	= $(document.createElement('DIV')).addClass('wanted status').css('margin-bottom', '5px');
		var	item_id	= 'i-' + (Math.random() * 65535) + '.' + (Math.random() * 65535);
		var	popover	= $(document.createElement('DIV')).attr('id', item_id).css({display: 'none'});
		var wanted			= parseInt($('#battle-container #' + target).data('wanted'));
		var wanted_reward	= $('#battle-container #' + target).data('wanted-reward');
		var wanted_type		= $('#battle-container #' + target).data('wanted-type');
		var	html	= '<b style="font-size:14px; color:#f53b3b">' + I18n.t('global.wanted') + '</b><br />';
		html = html + I18n.t('global.wanted_reward') + ': ' + wanted_reward + '<br />' ;
		html = html + wanted_type ;
		
		if(wanted){
			item.append('<img src="' + image_url('icons/wanted.png') + '"  class="technique-popover" data-placement="' + container.data('placement') + '" data-source="' + item_id + '" data-trigger="hover" data-placement="bottom"  />')
			item.append(popover);
			popover.append('<div class="modifier-tooltip">' + html + '</div>');
			container.append(item);
		}
		
		$('img', item).each(function () {
			var	_	= $(this);

            _.popover({
				content:	function () {
					return $(document.getElementById($(this).data('source'))).html();
				},
				html:		true,
				placement:	_.data('placement'),
				trigger:	'hover'
			});
		});
		
	}

	window.draw_battle_hb	= function (value, max, pos) {
		var	w	= (value * 100 / max);
		pos		= pos || 'r';

		if(pos == 'r') {
			$('#battle-container #enemy .life-fill').animate({width: w + '%'});
			$('#battle-container #enemy .life .text').html(value + ' / ' + max + ' ' + I18n.t('formula.for_life'));
		} else {
			$('#battle-container #player .life-fill').animate({width: w + '%'});
			$('#battle-container #player .life .text').html(I18n.t('formula.for_life') + ' ' +value + ' / ' + max);
		}
	};

	window.draw_battle_mb	= function (value, max, pos) {
		var	w	= (value * 100 / max);
		pos		= pos || 'r';

		if(pos == 'r') {
			$('#battle-container #enemy .mana-fill').animate({width: w + '%'});
			$('#battle-container #enemy .mana .text').html(value + ' / ' + max + ' ' + battle_container.data('mana-enemy'));
		} else {
			$('#battle-container #player .mana-fill').animate({width: w + '%'});
			$('#battle-container #player .mana .text').html(battle_container.data('mana-player') + ' ' +value + ' / ' + max);
		}
	}

	for(var i in images) {
		var	el				= document.createElement('img');
		el.src				= image_url(images[i].url);
		el.style.display	= 'none';
		el.onload			= function () {
			load_count++;

			if(load_count >= images_to_load) {
				all_loaded	= true;
			}

			images[this.getAttribute('data-key')].loaded	= true;
		}

		el.setAttribute('data-key', i);

		images[i].element	= el;
		images_to_load++;
	}

	for(var i in images) {
		document.body.appendChild(images[i].element);
	}

	if(battle_container.length) {
		$('.log-scroller .up', battle_container).on('click', function () {
			current_log_scroll	-= 10;

			if(current_log_scroll < 0) {
				current_log_scroll	= 0;
			}

			log_container.scrollTop(current_log_scroll);
		});

		$('.log-scroller .down', battle_container).on('click', function () {
			current_log_scroll	+= 10;
			log_container.scrollTop(current_log_scroll);

			if(current_log_scroll > log_container.scrollTop()) {
				current_log_scroll	= log_container.scrollTop();
			}
		});

		function parse_technique() {
			var	_	= $(this);

			if(locked[_.data('id')]) {
				jalert(I18n.t('battles.errors.technique_locked'));
				return;
			}

			var	variant	= 'attack';

			if(_.hasClass('ability') || _.hasClass('speciality')) {
				variant	= 'ability_speciality';
			}

			$.ajax({
				url:		battle_container.data('target') + '/' + variant,
				data:		{item: _.data('item')},
				type:		'post',
				dataType:	'json',
				success:	function (result) {
					parse(result);
				}
			})			
		}

		function parse(result) {
			draw_faction('player');
			draw_faction('enemy');
			draw_wanted('player');
			draw_wanted('enemy');

			if(result.log && result.log.length) {
				html	= '';

				result.log.forEach(function (entry) {
					html	+= '<div>' + entry + '</div><hr />';
				});

				current_log_scroll	= log_container.scrollTop();
				old_max_scroll		= max_log_scroll;

				log_container.html(html).scrollTop(1000000);
				max_log_scroll	= log_container.scrollTop();

				if(current_log_scroll != old_max_scroll) {
					log_container.scrollTop(current_log_scroll);
				}

				update_log_tooltip();
			}

			if(!result.flight) {
				if (result.tooltips && result.tooltips.length) {
					result.tooltips.forEach(function (tooltip) {
						if (tooltip.item == 'ability' || tooltip.item == 'speciality') {
							$('.activatables-player .' + tooltip.item + ' .technique-container').html(tooltip.tooltip);
						} else {
							$('#technique-content-' + tooltip.item, battle_container).html(tooltip.tooltip)
						}
					});
				}

				if (result.remove && result.remove.length) {
					result.remove.forEach(function (item) {
						$('#item-container-' + item, battle_container).remove();
					});
				}

				if (result.player) {
					draw_battle_hb(result.player.life, result.player.life_max, 'l');					
					draw_battle_mb(result.player.mana, result.player.mana_max, 'l');

					$('.activatables-player .modifier-turn-data').remove();
					$('.activatables-player .technique-popover').css({opacity: 1});

					//if ($('.activatables-enemy .modifier-turn-data').length && result.enemy.update_existent_locks) {
						$('.activatables-enemy .modifier-turn-data').remove();
						$('.activatables-enemy .technique-popover').css({opacity: 1});
					//}

					result.enemy.locks.forEach(function (item) {
						if (item.type == 'speciality' || item.type == 'ability') {
							if (item.type == 'speciality') {
								var target_container	= $('.activatables-enemy .speciality');
							} else {
								var target_container	= $('.activatables-enemy .ability');
							}

							target_container.append('<div class="modifier-turn-data">' + item.remaining + '</div>');
							$('.technique-popover', target_container).css({opacity: 0.2});
						}
					});

					result.player.locks.forEach(function (item) {
						if (item.type == 'speciality' || item.type == 'ability') {
							if (item.type == 'speciality') {
								var target_container	= $('.activatables-player .speciality');
							} else {
								var target_container	= $('.activatables-player .ability');
							}

							target_container.append('<div class="modifier-turn-data">' + item.remaining + '</div>');
							$('.technique-popover', target_container).css({opacity: 0.2});
						} else {
							$('#item-container-' + item.id + ' .modifier-turn-data').html(item.remaining);

							var	container	= $('#item-container-' + item.id, battle_container);

							if(!container.hasClass('locked')) {
								container.addClass('locked');

								$('.modifier-turn-data', container).show();
								$('.technique-popover', container).stop().animate({opacity: .2});
							}
						}
					});

					$('#technique-container .locked', battle_container).each(function () {
						var	_				= $(this);
						var	should_unlock	= true;
						var	id				= _.data('item');

						result.player.locks.forEach(function (item) {
							if(parseInt(item.id) == parseInt(id)) {
								should_unlock	= false;
							}
						});

						if(should_unlock) {
							_.removeClass('locked');
							$('.modifier-turn-data', _).hide();
							$('.technique-popover', _).stop().animate({opacity: 1});
						}
					});

					draw_modifiers(result.player, result.player.status, $('#player .modifiers', battle_container));
				}

				if (result.enemy) {
					draw_battle_hb(result.enemy.life, result.enemy.life_max, 'r');
					draw_battle_mb(result.enemy.mana, result.enemy.mana_max, 'r');

					draw_modifiers(result.enemy, result.enemy.status, $('#enemy .modifiers', battle_container));
				}

				if(result.timer) {
					timer_secs	= result.timer.seconds;
					timer_mins	= result.timer.minutes;

					if(!battle_timer_iv) {
						battle_timer_iv	= setInterval(function () {
							if(timer_secs == 0) {
								timer_mins--;
								timer_secs	= 59;
							}

							timer_secs--;

							if(timer_mins < 0) {
								ping();
							}

							mins	= timer_mins < 10 ? '0' + timer_mins : timer_mins;
							mins	= timer_mins < 0 ? '--' : mins;

							secs	= timer_secs < 10 ? '0' + timer_secs : timer_secs;
							secs	= timer_mins < 0 ? '--' : secs;

							$('.log-timer', battle_container).html(mins + ':' + secs);
						}, 1000);
					}
				}

				if (result.effects_roundup) {
					['p', 'e'].forEach(function (word) {
						/*if (word == 'e' && !result.enemy.update_existent_locks) {
							return;
						}*/

						var fixed_values_positive	= '';
						var fixed_values_negative	= '';
						var infinity_values	= '';
						var	roundup			= result.effects_roundup[word];
						var	normal_html		= '';
						var	container		= $('#' + (word == 'p' ? 'player' : 'enemy') + ' .modifiers', battle_container);
						var	got_effect		= false;
						var	special_ic		= [
							{icon: image_url('icons/stun.png'),  values: []},
							{icon: image_url('icons/bleed.png'),  values: []},
							{icon: image_url('icons/slow.png'),  values: []},
							{icon: image_url('icons/conf.png'), values: []}
						];

						var	specials	= [['stun'], ['bleeding', 'bleeding_percent'], ['slowness', 'slowness_percent'], ['confusion', 'confusion_percent']]

						for (var attribute in roundup) {
							if (hidden.indexOf(attribute) != -1) {
								continue;
							}

							var	values	= roundup[attribute];

							if (values) {
								var got_special	= false;

								for(var special in specials) {
									for(var prop in specials[special]) {
										if (specials[special][prop] == attribute) {
											special_ic[special].values.push({prop: attribute, values: values});
											got_special	= true;
										}
									}
								}

								if (!got_special) {
									got_effect	= true;

									for (var turn in values) {
										var	turns		= parseInt(turn);
										var total		= parseInt(values[turn]);
										var raw_total	= parseInt(values[turn]);

										if (negatives.indexOf(attribute) != -1) {
											total	= -total;
										}

										if (!total) {
											continue;
										}

										var current_html	= I18n.t('effects_roundup.' + attribute, {turns: turns, value: total}) + '<br />';

										if (turn == 'infinity') {
											infinity_values	+= current_html;
										} else if(turn == 'fixed') {
											if (raw_total > 0) {
												fixed_values_positive += current_html;
											} else {
												fixed_values_negative += current_html;
											}
										} else {
											normal_html	= current_html
										}
									}
								}
							}
						}

						if (got_effect && normal_html) {
							var	item	= $(document.createElement('DIV')).addClass('item status');
							var	item_id	= 'i-' + (Math.random() * 65535) + '.' + (Math.random() * 65535);
							var	popover	= $(document.createElement('DIV')).attr('id', item_id).css({display: 'none'});

							item.append('<img src="' + image_url('battle/arrows.png') + '"  class="technique-popover" data-placement="' + container.data('placement') + '" data-source="' + item_id + '" data-trigger="hover" data-placement="bottom"  />')
							item.append(popover);
							popover.append('<div class="modifier-tooltip">' + normal_html + '</div>');
							container.append(item);

							$('img', item).each(function () {
								var	_	= $(this);

                                _.popover({
									content:	function () {
										return $(document.getElementById($(this).data('source'))).html();
									},
									html:		true,
									placement:	_.data('placement'),
									trigger:	'hover'
								});
							});
						}

						if (fixed_values_positive) {
							var	item	= $(document.createElement('DIV')).addClass('item status');
							var	item_id	= 'i-' + (Math.random() * 65535) + '.' + (Math.random() * 65535);
							var	popover	= $(document.createElement('DIV')).attr('id', item_id).css({display: 'none'});

							fixed_values_positive = fixed_values_positive.replace(/por NaN turno\(s\)/img, '');

							item.append('<img src="' + image_url('battle/talents_p.png') + '"  class="technique-popover" data-placement="' + container.data('placement') + '" data-source="' + item_id + '" data-trigger="hover" data-placement="bottom"  />')
							item.append(popover);
							popover.append('<div class="modifier-tooltip"><b style="font-size:14px; color:#ff871c; padding-bottom:5px">' + I18n.t('item_types.6') + '</b><br />' + fixed_values_positive + '</div>');
							container.append(item);

							$('img', item).each(function () {
								var	_	= $(this);

                                _.popover({
									content:	function () {
										return $(document.getElementById($(this).data('source'))).html();
									},
									html:		true,
									placement:	_.data('placement'),
									trigger:	'hover'
								});
							});
						}

						if (fixed_values_negative) {
							var	item	= $(document.createElement('DIV')).addClass('item status');
							var	item_id	= 'i-' + (Math.random() * 65535) + '.' + (Math.random() * 65535);
							var	popover	= $(document.createElement('DIV')).attr('id', item_id).css({display: 'none'});

							fixed_values_negative = fixed_values_negative.replace(/por NaN turno\(s\)/img, '');

							item.append('<img src="' + image_url('battle/talents_n.png') + '"  class="technique-popover" data-placement="' + container.data('placement') + '" data-source="' + item_id + '" data-trigger="hover" data-placement="bottom"  />')
							item.append(popover);
							popover.append('<div class="modifier-tooltip"><b style="font-size:14px; color:#ff871c; padding-bottom:5px">' + I18n.t('item_types.6') + '</b><br />' + fixed_values_negative + '</div>');
							container.append(item);

							$('img', item).each(function () {
								var	_	= $(this);

                                _.popover({
									content:	function () {
										return $(document.getElementById($(this).data('source'))).html();
									},
									html:		true,
									placement:	_.data('placement'),
									trigger:	'hover'
								});
							});
						}


						if (infinity_values) {
							var	item	= $(document.createElement('DIV')).addClass('item status');
							var	item_id	= 'i-' + (Math.random() * 65535) + '.' + (Math.random() * 65535);
							var	popover	= $(document.createElement('DIV')).attr('id', item_id).css({display: 'none'});

							infinity_values = infinity_values.replace(/por NaN turno\(s\)/img, '');

							item.append('<img src="' + image_url('battle/pet.png') + '"  class="technique-popover" data-placement="' + container.data('placement') + '" data-source="' + item_id + '" data-trigger="hover" data-placement="bottom"  />')
							item.append(popover);
							popover.append('<div class="modifier-tooltip"><b style="font-size:14px; color:#ff871c; padding-bottom:5px">' + I18n.t('item_types.3') + '</b><br />' + infinity_values + '</div>');
							container.append(item);

							$('img', item).each(function () {
								var	_	= $(this);

                                _.popover({
									content:	function () {
										return $(document.getElementById($(this).data('source'))).html();
									},
									html:		true,
									placement:	_.data('placement'),
									trigger:	'hover'
								});
							});
						}

						special_ic.forEach(function (icon) {
							if (!icon.values.length) {
								return;
							}

							var	item	= $(document.createElement('DIV')).addClass('item status');
							var	item_id	= 'i-' + (Math.random() * 65535) + '.' + (Math.random() * 65535);
							var	popover	= $(document.createElement('DIV')).attr('id', item_id).css({display: 'none'});
							var	html	= '<div class="modifier-tooltip">';

							icon.values.forEach(function (effects, index) {
								for (var turn in effects.values) {
									var	turns	= parseInt(turn);
									var value	= parseInt(effects.values[turn]);

									if (negatives.indexOf(effects.prop) != -1) {
										value	= -value;
									}

									html		+= I18n.t('effects_roundup.' + effects.prop, {value: value, turns: turns}) + '<br />';
								}
							});

							item.append('<img src="' + icon.icon + '"  class="technique-popover" data-placement="' + container.data('placement') + '" data-source="' + item_id + '" data-trigger="hover" data-placement="bottom"  />')
							item.append(popover);
							popover.append(html + '</div></div>');
							container.append(item);

							$('img', item).each(function () {
								var	_	= $(this);

                                _.popover({
									content:	function () {
										return $(document.getElementById($(this).data('source'))).html();
									},
									html:		true,
									placement:	_.data('placement'),
									trigger:	'hover'
								});
							});
						});
					});
				}
			}

			if(result.finished) {
				// $('#finished-message').html(result.finished);
				$('#battle-container #technique-container').html('').hide();
				$('#battle-container .player-container #players').css({ height: '430px' });

				var	win	= bootbox.dialog({message: result.finished, buttons: [
					{
						label:		'Fechar',
						class:		'btn btn-sm btn-default',
						callback:	function () {
							lock_screen(true);
							location.href	= result.redirect;
							// location.href	= parseInt(result.end_type) ? result.redirect : make_url('hospital') ;
						}
					}
				]});

				$('.modal-dialog', win).addClass('pattern-container');
				$('.modal-content', win).addClass('with-pattern');
			}

			if(result.messages && result.messages.length) {
				format_error(result);
			}

			if(result.attack_text) {
				$('#attack-text', battle_container).html(result.attack_text);
				$('.log-timer', battle_container).css({color: result.my_turn ? '#BA1C1C' : '#1CBA26'});

				if (result.my_turn) {
					if (!sound_was_played) {
						sound_was_played	= true;
						audio[0].play();
					}
				} else {
					sound_was_played	= false;
				}
			}
		}

		function ping(initial) {
			$.ajax({
				url:		battle_container.data('target') + '/ping' + (initial ? '?initial' : ''),
				type:		'post',
				dataType:	'json',
				success:	function (result) {
					parse(result);
				}
			});
		}

		$('#technique-container, .activatables-player', battle_container).on('click', '.item', function () {
			parse_technique.apply(this, []);
		});

		$('#technique-container #skip-turn').on('click', function () {
			parse_technique.apply(this, []);
		})

		if(parseInt(battle_container.data('ping'))) {
			ping_iv	= setInterval(function () {
				if(!can_ping) {
					return;
				}

				can_ping	= false;

				$.ajax({
					url:	absolute_url('pvp_ping.php?uuid=' + battle_container.data('ping')),
					dataType:	'json',
					success:	function (result) {
						can_ping	= true;

						if(result.ping) {
							ping();
						}
					}, error:	function (result) {
						can_ping	= true;
					}
				});
			}, 5000);
		}

		update_log_tooltip();
		log_container.scrollTop(1000000);

		current_log_scroll	= log_container.scrollTop();
		max_log_scroll		= current_log_scroll;

		// Initial ping to draw status
		ping(true);
	}

	$(document).ready(function () {
		$(document.body).append(audio);
	})
})();