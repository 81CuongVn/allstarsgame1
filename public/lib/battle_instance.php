<?php
	class BattleInstance {
		public	$log				= [];
		private	$player				= null;
		private	$player_item		= null;
		private	$enemy				= null;
		private	$enemy_item			= null;
		public	$first				= 'player';
		public	$player_was_error 	= false;
		public	$enemy_was_error 	= false;

		function set_player(&$player) {
			$this->player	=& $player;
		}

		function set_player_item(&$item) {
			$this->player_item	=& $item;

			if (is_a($this->player, 'Player')) {
				SharedStore::S('last_battle_item_of_' . $this->player->id, $item->id);
			} else {
				SharedStore::S('last_battle_npc_item_of_' . $this->enemy->id, $item->id);
			}
		}

		function set_enemy($enemy) {
			$this->enemy	=& $enemy;
		}

		function set_enemy_item(&$item) {
			$this->enemy_item	=& $item;

			if (is_a($this->enemy, 'Player')) {
				SharedStore::S('last_battle_item_of_' . $this->enemy->id, $item->id);
			} else {
				SharedStore::S('last_battle_npc_item_of_' . $this->player->id, $item->id);
			}
		}

		function add_effect($item, $source, $target) {
			if (!$item->item_effect_ids) {
				return;
			}

			$attack		= $source == $this->player ? $this->player_item : $this->enemy_item;
			$is_error	= $attack->formula(true)->hit_chance < 100 ? rand(1, 100) > $attack->formula(true)->hit_chance : false;

			if ($is_error || $target->get_parsed_effects()['null_next_attack'] || $target->get_parsed_effects()['dodge_technique']) {
				return;
			}

			$chances	= explode(',', $item->effect_chances);

			foreach ($item->effects() as $key => $effect) {
				$source->add_effect($item, $effect, $effect->chance, $item->duration, 'player');
				$target->add_effect($item, $effect, $effect->chance, $item->duration, 'enemy');
			}
		}

		function run() {
			$this->log			= [];
			$log				= [];

			$critical_image		= '<img src="' . image_url('icons/for_crit.png') . '" align="absmiddle" />&nbsp;';
			$absorb_image		= '<img src="' . image_url('icons/for_abs.png') . '" align="absmiddle" />&nbsp;';
			$precision_image	= '<img src="' . image_url('icons/for_prec.png') . '" align="absmiddle" />&nbsp;';
			$bleeding_image		= '<img src="' . image_url('icons/bleed.png') . '" align="absmiddle" />&nbsp;';
			$strong_image		= '<span class="glyphicon glyphicon-chevron-up" style="color: #00b008"></span>&nbsp;';

			// Player Effects
			$player_effects		= $this->player->get_parsed_effects();
			if ($player_effects['turns_attack_to_neutral']) {
				$this->player_item->force_attack_type(0);
			}

			if ($player_effects['turns_attack_to_elemental']) {
				$this->player_item->force_attack_type(1);
			}

			if ($player_effects['turns_attack_to_fighter']) {
				$this->player_item->force_attack_type(2);
			}

			if ($player_effects['turns_attack_to_magic']) {
				$this->player_item->force_attack_type(3);
			}

			if ($player_effects['turns_attack_to_warrior']) {
				$this->player_item->force_attack_type(4);
			}

			if ($player_effects['turns_attack_to_ranger']) {
				$this->player_item->force_attack_type(5);
			}

			// Enemy Effects
			$enemy_effects		= $this->enemy->get_parsed_effects();
			if ($enemy_effects['turns_attack_to_neutral']) {
				$this->enemy_item->force_attack_type(0);
			}

			if ($enemy_effects['turns_attack_to_elemental']) {
				$this->enemy_item->force_attack_type(1);
			}

			if ($enemy_effects['turns_attack_to_fighter']) {
				$this->enemy_item->force_attack_type(2);
			}

			if ($enemy_effects['turns_attack_to_magic']) {
				$this->enemy_item->force_attack_type(3);
			}

			if ($enemy_effects['turns_attack_to_warrior']) {
				$this->enemy_item->force_attack_type(4);
			}

			if ($enemy_effects['turns_attack_to_ranger']) {
				$this->enemy_item->force_attack_type(5);
			}

			$player_stronger	= $this->enemy_item->attack_type() ? $this->player_item->is_strong_to($this->enemy_item->attack_type()->id) : false;
			$enemy_stronger		= $this->player_item->attack_type() ? $this->enemy_item->is_strong_to($this->player_item->attack_type()->id) : false;

			$player_is_skip		= is_a($this->player_item, 'SkipTurnItem');
			$enemy_is_skip		= is_a($this->enemy_item, 'SkipTurnItem');

			$this->player_item->formula(true);
			$this->enemy_item->formula(true);

			if (!$this->player_item->is_buff && !$player_is_skip) {
				$player_is_critical		= rand(1, 100) <= $this->player->for_crit();
				$player_is_absorb		= rand(1, 100) <= $this->player->for_abs();
			} else {
				$player_is_critical		= false;
				$player_is_absorb		= false;
			}

			$player_is_precision	= rand(1, 100) <= $this->player->for_prec() && !$this->player_item->is_defensive;
			$player_is_error		= $this->player_item->formula()->hit_chance < 100 ? rand(1, 100) > $this->player_item->formula()->hit_chance : false;

			if ($player_effects['next_is_critical']) {
				$player_is_critical	= true;
			}

			if ($player_effects['next_is_absorb']) {
				$player_is_absorb	= true;
			}

			if ($player_effects['next_is_precise']) {
				$player_is_precision	= true;
			}

			if ($player_effects['next_will_hit']) {
				$player_is_error	= false;
			}

			if (!$player_is_error) {
				$player_attack	= $this->player->for_atk() + $this->player_item->formula()->damage;
				$player_defense	= $this->player->for_def() + $this->player_item->formula()->defense;

				if ($player_is_critical) {
					if ($this->player_item->is_defensive) {
						$player_defense	+= percent($this->player->for_crit_inc(), $player_defense);
					} else {
						$player_attack	+= percent($this->player->for_crit_inc(), $player_attack);
					}
				}

				if ($enemy_effects['remove_attack_weakness']) {
					$player_stronger = false;
				}

				if ($player_stronger) {
					$incr	= ($player_effects['double_strong_effect'] ? 100 : 50) - (has_chance($enemy_effects['half_weak_damage_chance']) ? 25 : 0);

					if ($this->player_item->is_defensive) {
						if ($player_stronger) {
							$player_defense	+= percent($incr, $player_defense);
						}
					} else {
						if ($player_stronger) {
							$player_attack	+= percent($incr, $player_attack) +
											   percent($player_effects['strong_effect'] - $enemy_effects['weak_effect'], $player_attack);
						}
					}
				}
			} else {
				$player_attack		= $this->player->for_atk();
				$player_defense		= $this->player->for_def();
				$player_stronger	= false;
			}

			if (!$this->enemy_item->is_buff && !$enemy_is_skip) {
				$enemy_is_critical		= rand(1, 100) <= $this->enemy->for_crit();
				$enemy_is_absorb		= rand(1, 100) <= $this->enemy->for_abs();
			} else {
				$enemy_is_critical		= false;
				$enemy_is_absorb		= false;
			}

			$enemy_is_precision		= rand(1, 100) <= $this->enemy->for_prec() && !$this->enemy_item->is_defensive;
			$enemy_is_error			= $this->enemy_item->formula()->hit_chance < 100 ? rand(1, 100) > $this->enemy_item->formula()->hit_chance : false;

			if ($enemy_effects['next_is_critical']) {
				$enemy_is_critical	= true;
			}

			if ($enemy_effects['next_is_absorb']) {
				$enemy_is_absorb	= true;
			}

			if ($enemy_effects['next_is_precise']) {
				$enemy_is_precision	= true;
			}

			if ($enemy_effects['next_will_hit']) {
				$enemy_is_error	= false;
			}

			if(!$enemy_is_error) {
				$enemy_attack	= $this->enemy->for_atk() + $this->enemy_item->formula()->damage;
				$enemy_defense	= $this->enemy->for_def() + $this->enemy_item->formula()->defense;

				if($enemy_is_critical) {
					if($this->enemy_item->is_defensive) {
						$enemy_defense	+= percent($this->enemy->for_crit_inc(), $enemy_defense);
					} else {
						$enemy_attack	+= percent($this->enemy->for_crit_inc(), $enemy_attack);
					}
				}

				if ($player_effects['remove_attack_weakness']) {
					$enemy_stronger	= false;
				}

				if ($enemy_stronger) {
					$incr	= ($enemy_effects['double_strong_effect'] ? 100 : 50) - (has_chance($player_effects['half_weak_damage_chance']) ? 25 : 0);

					if ($this->enemy_item->is_defensive) {
						if ($enemy_stronger) {
							$enemy_defense	+= percent($incr, $enemy_defense);
						}
					} else {
						if ($enemy_stronger) {
							$enemy_attack	+= percent($incr, $enemy_attack) +
											   percent($enemy_effects['strong_effect'] - $player_effects['weak_effect'], $player_attack);
						}
					}
				}
			} else {
				$enemy_attack		= $this->enemy->for_atk();
				$enemy_defense		= $this->enemy->for_def();
				$enemy_stronger		= false;
			}

			// Precision should be processed here since we'll null a defensive value -->
				if ($player_is_precision && !($player_is_error || $player_is_skip)) {
					$enemy_defense	= 0;
				}

				if ($enemy_is_precision && !($enemy_is_error || $enemy_is_skip)) {
					$player_defense	= 0;
				}
			// <--

			// Absorb values -->
				if($player_is_absorb && !($player_is_error || $player_is_skip)) {
					$enemy_attack	-= percent($this->player->for_abs_inc(), $enemy_attack);
				}

				if($enemy_is_absorb && !($enemy_is_error || $enemy_is_skip)) {
					$player_attack	-= percent($this->enemy->for_abs_inc(), $player_attack);
				}
			// <--

			if ($this->player_item->is_buff) {
				$player_attack	= 0;
			} elseif ($this->enemy_item->is_buff) {
				$enemy_attack	= 0;
			}

			$raw_player_attack	= $player_attack;
			$raw_enemy_attack	= $enemy_attack;


			foreach (['bleeding', 'stun', 'slowness', 'confusion'] as $effect) {
				foreach (['player_attack', 'enemy_attack'] as $target) {
					$attack		=& $$target;
					$raw_attack	= $target == 'player_attack' ? $raw_player_attack : $raw_enemy_attack;
					$item		=& $target == 'player_attack' ? $this->player_item : $this->enemy_item;
					$e_effects	= $target == 'player_attack' ? $enemy_effects : $player_effects;
					$p_effects	= $target == 'player_attack' ? $player_effects : $enemy_effects;

					if ($e_effects[$effect] || (isset($e_effects[$effect . '_percent']) && $e_effects[$effect . '_percent'])) {
						$attack	+= $p_effects['damage_in_' . $effect] + percent($p_effects['damage_in_' . $effect . '_percent'], $raw_attack);
					}

					// if ($p_effects['generic_attack_damage'] && $item->is_generic) {
					// 	$attack	+= $p_effects['generic_attack_damage'] + percent($p_effects['generic_attack_damage'], $raw_attack);
					// }

					// if ($p_effects['unique_attack_damage'] && !$item->is_generic) {
					// 	$attack	+= $p_effects['unique_attack_damage'] + percent($p_effects['unique_attack_damage'], $raw_attack);
					// }
				}
			}

			if ($enemy_effects['confusion'] || $enemy_effects['confusion_percent'] && has_chance($player_effects['damage_increase_in_confusion'])) {
				$player_attack	+= percent(25, $player_attack);
			}

			if ($player_effects['confusion'] || $player_effects['confusion_percent'] && has_chance($enemy_effects['damage_increase_in_confusion'])) {
				$enemy_attack	+= percent(25, $enemy_attack);
			}

			if ($enemy_effects['slowness'] || $enemy_effects['slowness_percent'] && has_chance($player_effects['damage_increase_in_slowness'])) {
				$player_attack	+= percent(25, $player_attack);
			}

			if ($player_effects['slowness'] || $player_effects['slowness_percent'] && has_chance($enemy_effects['damage_increase_in_slowness'])) {
				$enemy_attack	+= percent(25, $enemy_attack);
			}

			$player_damage		= floor($player_attack - $enemy_defense);
			$enemy_damage		= floor($enemy_attack - $player_defense);

			$player_init		= 100 - $this->player_item->formula()->attack_speed;
			$enemy_init			= 100 - $this->enemy_item->formula()->attack_speed;

			$can_draw			= $player_init == $enemy_init;

			if ($can_draw) {
				$can_draw	= $this->player->for_init() == $this->enemy->for_init();

				if (!$can_draw) {
					$player_init	= $this->player->for_init();
					$enemy_init		= $this->enemy->for_init();
				}
			}

			if($player_init >= $enemy_init) {
				$condition		= 0;
				$this->first	= 'player';
			} elseif($player_init < $enemy_init) {
				$condition		= 1;
				$this->first	= 'enemy';
			}

			$this->player_was_error = $player_is_error;
			$this->enemy_was_error = $enemy_is_error;

			for ($i=0; $i <= 1; $i++) {
				$item				= $i == $condition ? $this->player_item : $this->enemy_item;
				$player				= $i == $condition ? $this->player : $this->enemy;
				$enemy				= $i == $condition ? $this->enemy : $this->player;
				$tooltip_id			= 'bi-' . uniqid(uniqid(), true);
				$is_critical		= $i == $condition ? $player_is_critical : $enemy_is_critical;
				$is_absorb			= $i == $condition ? $player_is_absorb : $enemy_is_absorb;
				$is_precision		= $i == $condition ? $player_is_precision : $enemy_is_precision;
				$is_error			= $i == $condition ? $player_is_error : $enemy_is_error;
				$is_stronger		= $i == $condition ? $player_stronger : $enemy_stronger;
				$effects			= $i == $condition ? $player_effects : $enemy_effects;
				$effects_enemy		= $i == $condition ? $enemy_effects : $player_effects;
				$is_null			= $i == $condition ? $enemy_effects['null_next_attack'] : $player_effects['null_next_attack'];
				$is_dodge			= $i == $condition ? $enemy_effects['dodge_technique'] : $player_effects['dodge_technique'];
				$effect				= '';
				$bleeding_text		= '';
				$bleeding_damage	= 0;
				$counter_damage		= 0;
				$entry				= '';

				// Current player damage
				$damage	= $i == $condition ? $player_damage : $enemy_damage;

				if(!$is_error) {
					if($is_absorb) {
						$effect	.= $absorb_image;
					}

					if (!($item->is_buff || $item->is_turn_skip)) {
						if($is_critical) {
							$effect	.= $critical_image;
						}

						if($is_precision) {
							$effect	.= $precision_image;
						}

						if ($is_stronger) {
							$effect	.= $strong_image;
						}
					}

					if ($effects_enemy['bleeding']) {
						$bleeding_damage	+= $effects_enemy['bleeding'] + -$effects['increase_bleeding_damage'];
					}

					if ($effects_enemy['bleeding_percent']) {
						$bleeding_damage	+= percent($effects_enemy['bleeding_percent'] + -$effects['increase_bleeding_damage_percent'], $damage);
					}

					$bleeding_damage	= abs($bleeding_damage);

					if ($bleeding_damage) {
						$bleeding_text	= $bleeding_image . t('battles.bleeding_damage', ['damage' => $bleeding_damage]);
					}
				}

				$entry	.= t('battles.attack_text', [
					'player'	=> $player->name,
					'enemy'		=> $enemy->name,
					'item'		=> $effect . $item->description()->name,
					'tooltip'	=> $tooltip_id
				]) . '&nbsp;';

				if ($item->is_buff || $item->is_turn_skip) {
					if ($bleeding_damage) {
						$entry				.= $bleeding_image . t('battles.bleeding_text_only', ['damage' => $bleeding_damage]) . '&nbsp;';
						$enemy->less_life	+= $bleeding_damage;
					}

					$is_kill	= false;
				} else {
					if($is_error) {
						$entry	.= t('battles.error_text') . '&nbsp;';
					} elseif($is_null) {
						$entry	.= t('battles.null_text') . '&nbsp;';
					} elseif($is_dodge) {
						$entry	.= t('battles.dodge_text') . '&nbsp;';
					} else {
						if(!$item->is_defensive) {
							if($damage == 0) {
								$entry	.= t('battles.defense_text') . '&nbsp;';
							} elseif($damage > 0) {
								$entry				.= t('battles.damage_text', ['damage' => $damage, 'bleeding_damage' => $bleeding_text]) . '&nbsp;';
								$enemy->less_life	+= $damage + $bleeding_damage;

								// Recupera vida batendo
								if($player_effects['steal_health']){
									$player->less_life -= percent($player_effects['steal_health'],$damage);
								}
								// Remove 2 de mana do jogador e adiciona ao npc.
								if($player_effects['steal_mana']){
									$player->less_mana		+= $player_effects['steal_mana'];
									$enemy->less_mana		-= $player_effects['steal_mana'];
								}

							} elseif($damage < 0) {
								$entry				.= t('battles.counter_text', ['damage' => abs($damage)]) . '&nbsp;';
								$player->less_life	+= -$damage;
								$counter_damage		= $damage;
							}
						} else {
							if ($bleeding_damage) {
								$entry				.= $bleeding_image . t('battles.bleeding_text_only', ['damage' => $bleeding_damage]) . '&nbsp;';
								$enemy->less_life	+= $bleeding_damage;
							}
						}
					}

					if(!$is_error && !$is_null) {
						$is_kill	= $enemy->for_life() <= 0;

						if (is_a($player, 'Player')) {
							$player_item	= $item->player_item();

							if (is_a($player_item, 'PlayerItem')) {
								$stats	= $player_item->stats();
								$stats->uses++;

								if ($player->for_mana() <= $player->for_mana(true) / 2) {
									$stats->use_low_stat++;
								}

								if ($item->is_defensive) {
									if ($damage < 0 && !$counter_damage) {
										$stats->full_defenses++;
									}

									if ($counter_damage) {
										$stats->def_counter++;
									}

									if ($is_critical) {
										$stats->def_crit++;
									}
								} else {
									if ($is_precision) {
										$stats->use_with_precision++;
									}

									if ($is_kill) {
										$stats->kills++;

										if (is_a($enemy, 'Player')) {
											if ($player_effects['slowness'] || $player_effects['slowness_percent']){
												$player_kills = new PlayerKill();
												$player_kills->player_id = $player->id;
												$player_kills->enemy_id  = $enemy->id;
												$player_kills->kills_with_slowness++;
												$player_kills->save();
											}

											if ($player_effects['confusion'] || $player_effects['confusion_percent']){
												$player_kills = new PlayerKill();
												$player_kills->player_id = $player->id;
												$player_kills->enemy_id  = $enemy->id;
												$player_kills->kills_with_confusion++;
												$player_kills->save();
											}

											if ($player_effects['bleeding'] || $player_effects['bleeding']){
												$player_kills = new PlayerKill();
												$player_kills->player_id = $player->id;
												$player_kills->enemy_id  = $enemy->id;
												$player_kills->kills_with_bleeding++;
												$player_kills->save();
											}

											if ($player_effects['stun'] || $player_effects['stun']){
												$player_kills = new PlayerKill();
												$player_kills->player_id = $player->id;
												$player_kills->enemy_id  = $enemy->id;
												$player_kills->kills_with_stun++;
												$player_kills->save();
											}

											if ($is_stronger) {
												$player_kills = new PlayerKill();
												$player_kills->player_id = $player->id;
												$player_kills->enemy_id  = $enemy->id;
												$player_kills->kills_with_stronger++;
												$player_kills->save();
											}
											if ($is_critical) {
												$stats->kills_with_crit++;
												$player_kills = new PlayerKill();
												$player_kills->player_id = $player->id;
												$player_kills->enemy_id  = $enemy->id;
												$player_kills->kills_with_crit++;
												$player_kills->save();
											}
											if ($is_precision) {
												$stats->kills_with_precision++;
												$player_kills = new PlayerKill();
												$player_kills->player_id = $player->id;
												$player_kills->enemy_id  = $enemy->id;
												$player_kills->kills_with_precision++;
												$player_kills->save();
											}
										}
									}
								}

								$stats->save();
							}

							/*
							$mods	= [];
							$pmods	= $player->get_modifiers();
							$emods	= $enemy->get_modifiers();

							foreach ($pmods as $mod) {
								if($mod['instance']->buff_direction == 'friend') {
									$mods[]	= $mod;
								}
							}

							foreach ($emods as $mod) {
								if($mod['instance']->buff_direction == 'enemy') {
									$mods[]	= $mod;
								}
							}

							foreach ($mods as $mod) {
								if ($mod['instance']->item_type_id != 7) {
									$stats	= $mod['instance']->player_item()->stats();

									if($is_kill) {
										$stats->kills++;

										if($is_critical) {
											$stats->kills_with_crit++;
										}

										if($is_precision) {
											$stats->kills_with_precision++;
										}
									}

									$stats->save();
								}
							}
							*/
						}
					}

				}

				if ($enemy->for_life() <= 0 && $effects_enemy['last_hit_dont_die']) {
					$enemy->less_life	= $enemy->for_life(true) - 1;

					if (method_exists($enemy, 'save')) {
						$enemy->save();
					}
				}

				/*
					if ($player->for_life() <= 0 && $effects['last_hit_dont_die']) {
						$player->less_life	= $player->for_life(true) - 1;

						if (method_exists($player, 'save')) {
							$player->save();
						}
					}
				*/

				$entry	.= partial('shared/battle_item', ['player' => $player, 'item' => $item, 'id' => $tooltip_id]) . '&nbsp;';
				$log[]	= $entry;

				if(!$i) {
					$entry	.= "<br /><br />";
				}

				if(!$is_error && $enemy->for_life() <= 0 && !$can_draw) {
					break;
				}
			}

			$this->log	= $log;
		}
	}
