<?php
trait EffectManager {
	function add_effect($item, $effect, $chance, $duration, $direction = 'player') {
		if (has_chance($chance)) {
			$this->_alloc_effects();
			$effects	= $this->get_effects();

			if ($effect->stun) {
				$duration++;
			}

			if (!isset($effects[$direction][$item->id])) {
				$effects[$direction][$item->id]	= [];
			}

			if (isset($effects[$direction][$item->id][$effect->id])) {
				$effects[$direction][$item->id][$effect->id]->turns	= $duration;
			} else {
				$new_effect					= new stdClass();
				$new_effect->id				= $effect->id;
				$new_effect->turns			= $duration;
				$new_effect->total_turns	= $duration;
				$new_effect->soruce_id		= $item->id;
				$new_effect->type			= 'item';
				$new_effect->direction		= $direction;
				$new_effect->secret			= $effect->secret;
				$new_effect->revealed		= false;

				$effects[$direction][$item->id][$effect->id]	= $new_effect;
			}

			SharedStore::S($this->build_effects_uid(), $effects);
		}
	}

	function add_fixed_effect($source, $type, $effect, $direction = 'player') {
		$this->_alloc_effects();
		$effects	= $this->get_effects();

		if (!isset($effects[$direction][$type])) {
			$effects[$direction][$type]	= [];
		}

		if (isset($effects[$direction][$type][$effect->id])) {
			return;
		} else {
			$new_effect					= new stdClass();
			$new_effect->id				= $effect->id;
			$new_effect->turns			= $source->item_type_id == 6 ? 'fixed' : 'infinity';
			$new_effect->total_turns	= $source->item_type_id == 6 ? 'fixed' : 'infinity';
			$new_effect->soruce_id		= $source->item_type_id == 6 ? $source->id : 'item';
			$new_effect->type			= $type;
			$new_effect->direction		= $direction;
			$new_effect->secret			= $effect->secret;
			$new_effect->revealed		= false;

			$effects[$direction][$type][$effect->id]	= $new_effect;
		}

		SharedStore::S($this->build_effects_uid(), $effects);
	}

	function add_ability_speciality_effect($source, $type, $effect, $chance, $duration, $direction) {
		if (has_chance($chance)) {
			$this->_alloc_effects();
			$effects	= $this->get_effects();

			$fetch_condition	= function ($type, $value) {
				if (($type == 'player' && $value > 0) || ($type == 'enemy' && $value < 0)) {
					return true;
				}

				return false;
			};

			if (
				$effect->removes_stun || $effect->remove_slowness || $effect ->remove_bleeding || $effect->remove_confusion ||
				$effect->increase_bleeding_duration || $effect->increase_slowness_duration || $effect->increase_confusion_duration/* ||
				$effect->heals_mana || $effect->heals_life*/
			) {
				if ($fetch_condition($direction, $effect->removes_stun)) {
					$this->_remove_effects_with(['stun']);
				}

				if ($fetch_condition($direction, $effect->remove_slowness)) {
					$this->_remove_effects_with(['slowness']);
				}

				if ($fetch_condition($direction, $effect->remove_bleeding)) {
					$this->_remove_effects_with(['bleeding']);
				}

				if ($fetch_condition($direction, $effect->remove_confusion)) {
					$this->_remove_effects_with(['confusion']);
				}

				if ($fetch_condition($direction, $effect->increase_bleeding_duration)) {
					$this->increase_effects_with(['bleeding' => abs($effect->increase_bleeding_duration)]);
				}

				if ($fetch_condition($direction, $effect->increase_slowness_duration)) {
					$this->increase_effects_with(['slowness' => abs($effect->increase_slowness_duration)]);
				}

				if ($fetch_condition($direction, $effect->increase_confusion_duration)) {
					$this->increase_effects_with(['confusion' => abs($effect->increase_confusion_duration)]);
				}

				// if ($fetch_condition($direction, $effect->heals_life)) {
				// 	$this->less_life	-= $effect->heals_life;
				// 	if ($this->less_life < 0) {
				// 		$this->less_life	= 0;
				// 	}
				// }

				// if ($fetch_condition($direction, $effect->heals_mana)) {
				// 	$this->less_mana	-= $effect->heals_mana;
				// 	if ($this->less_mana < 0) {
				// 		$this->less_mana	= 0;
				// 	}
				// }

				// Instant don't go to the effect array
				return;
			}

			if ($effect->heals_mana || $effect->heals_life) {
				if ($fetch_condition($direction, $effect->heals_life)) {
					$this->less_life	-= $effect->heals_life;
					if ($this->less_life < 0) {
						$this->less_life	= 0;
					}
				}

				if ($fetch_condition($direction, $effect->heals_mana)) {
					$this->less_mana	-= $effect->heals_mana;
					if ($this->less_mana < 0) {
						$this->less_mana	= 0;
					}
				}

				--$duration;

				if ($duration <= 0) {
					return;
				}
			}

			if (!isset($effects[$direction][$type])) {
				$effects[$direction][$type]	= [];
			}

			if (isset($effects[$direction][$type][$effect->id])) {
				$effects[$direction][$type][$effect->id]->turns	= $duration;
			} else {
				$new_effect					= new stdClass();
				$new_effect->id				= $effect->id;
				$new_effect->turns			= $duration;
				$new_effect->total_turns	= $duration;
				$new_effect->soruce_id		= $type == 'ability' ? $source->character_ability_id : $source->character_speciality_id;
				$new_effect->type			= $type;
				$new_effect->direction		= $direction;
				$new_effect->secret			= $effect->secret;
				$new_effect->revealed		= false;

				$effects[$direction][$type][$effect->id]	= $new_effect;
			}

			SharedStore::S($this->build_effects_uid(), $effects);
		}
	}

	function get_effects() {
		$this->_alloc_effects();
		return SharedStore::G($this->build_effects_uid(), null);
	}

	function get_parsed_effects($return_with_duration = false) {
		$this->_alloc_effects();

		$return	= [
			'bleeding'						=> 0,
			'stun'							=> 0,
			'slowness'						=> 0,
			'confusion'						=> 0,
			'for_atk'						=> 0,
			'for_def'						=> 0,
			'for_crit'						=> 0,
			'for_crit_inc'					=> 0,
			'for_abs'						=> 0,
			'for_abs_inc'					=> 0,
			'for_prec'						=> 0,
			'for_init'						=> 0,
			'for_hit'						=> 0,

			'bleeding_percent'				=> 0,
			'slowness_percent'				=> 0,
			'confusion_percent'				=> 0,

			'slowness_percent'				=> 0,
			'for_atk_percent'				=> 0,
			'for_def_percent'				=> 0,
			'for_crit_percent'				=> 0,
			'for_crit_inc_percent'			=> 0,
			'for_abs_percent'				=> 0,
			'for_abs_inc_percent'			=> 0,
			'for_prec_percent'				=> 0,
			'for_init_percent'				=> 0,
			'for_hit_percent'				=> 0,

			'attack_speed'					=> 0,
			'attack_speed_percent'			=> 0,

			'next_mana_cost'				=> 0,
			'lock_random_technique'			=> 0,
			'copy_last_technique'			=> 0,
			'last_hit_dont_die'				=> 0,
			'null_next_attack'				=> 0,
			'turns_attack_to_neutral'		=> 0,
			'turns_attack_to_elemental'		=> 0,
			'turns_attack_to_fighter'		=> 0,
			'turns_attack_to_magic'			=> 0,
			'turns_attack_to_warrior'		=> 0,
			'turns_attack_to_ranger'		=> 0,
			'removes_stun'					=> 0,
			'remove_slowness'				=> 0,
			'remove_bleeding'				=> 0,
			'remove_confusion'				=> 0,
			'double_bleeding_chance'		=> 0,
			'double_stun_chance'			=> 0,
			'double_slowness_chance'		=> 0,
			'double_confusion_chance'		=> 0,
			'increase_bleeding_duration'	=> 0,
			'increase_slowness_duration'	=> 0,
			'increase_confusion_duration'	=> 0,
			'remove_attack_weakness'		=> 0,
			'double_strong_effect'			=> 0,
			'heals_life'					=> 0,
			'heals_mana'					=> 0,
			'renew_random_cooldown'			=> 0,
			'next_is_critical'				=> 0,
			'next_is_absorb'				=> 0,
			'next_is_precise'				=> 0,
			'next_will_hit'					=> 0,

			'elemental_damage'				=> 0,
			'fighter_damage'				=> 0,
			'magic_damage'					=> 0,
			'warrior_damage'				=> 0,
			'ranger_damage'					=> 0,

			'elemental_damage_percent'		=> 0,
			'fighter_damage_percent'		=> 0,
			'magic_damage_percent'			=> 0,
			'warrior_damage_percent'		=> 0,
			'ranger_damage_percent'			=> 0,

			'bleeding_chance'				=> 0,
			'slowness_chance'				=> 0,
			'confusion_chance'				=> 0,
			'stun_chance'					=> 0,
			'strong_effect'					=> 0,
			'weak_effect'					=> 0,
			'heals_by_turn'					=> 0,
			'mana_through_turns'			=> 0,
			'item_find'						=> 0,
			'fragment_find'					=> 0,
			'grimoire_find'					=> 0,

			'damage_in_bleeding'			=> 0,
			'damage_in_slowness'			=> 0,
			'damage_in_stun'				=> 0,
			'damage_in_confusion'			=> 0,
			'generic_attack_damage'			=> 0,
			'unique_attack_damage'			=> 0,
			'attack_half_life'				=> 0,
			'defense_half_life'				=> 0,
			'exp_reward_extra'				=> 0,
			'currency_reward_extra'			=> 0,

			'damage_in_bleeding_percent'	=> 0,
			'damage_in_slowness_percent'	=> 0,
			'damage_in_stun_percent'		=> 0,
			'damage_in_confusion_percent'	=> 0,
			'generic_attack_damage_percent'	=> 0,
			'unique_attack_damage_percent'	=> 0,
			'attack_half_life_percent'		=> 0,
			'defense_half_life_percent'		=> 0,
			'exp_reward_extra_percent'		=> 0,
			'currency_reward_extra_percent'	=> 0,

			'generic_defense'				=> 0,
			'generic_defense_percent'		=> 0,

			'increase_bleeding_damage'		=> 0,
			'increase_slowness_damage'		=> 0,
			'increase_confusion_damage'		=> 0,

			'increase_bleeding_damage_percent'	=> 0,
			'increase_slowness_damage_percent'	=> 0,
			'increase_confusion_damage_percent'	=> 0,


			'no_consume_stamina'			=> 0,
			'next_turn_life'				=> 0,
			'pets_find'						=> 0,

			'low_technique_no_cost'			=> 0,
			'mid_technique_no_cooldown'		=> 0,
			'high_technique_half_cost'		=> 0,

			'low_technique_no_cost_percent'		=> 0,
			'mid_technique_no_cooldown_percent'	=> 0,
			'high_technique_half_cost_percent'	=> 0,

			'double_effect_pets'			=> 0,
			'enemy_absorb_reduction'		=> 0,
			'half_weak_damage_chance'		=> 0,

			'damage_increase_in_confusion'	=> 0,
			'damage_increase_in_slowness'	=> 0,

			// Novos efeitos
			'kill_with_one_hit'		=> 0,
			'cancel_regen_mana'		=> 0,
			'steal_health' 			=> 0,
			'dodge_technique'		=> 0,
			'remove_mana'			=> 0,
			'steal_mana'			=> 0,

			'damage_increase_in_confusion_percent'	=> 0,
			'damage_increase_in_slowness_percent'	=> 0,

			'bonus_stamina_max'				=> 0,
			'next_turn_mana'				=> 0,
			'reduce_critical_damage'		=> 0,
			'mana_half_life'				=> 0,
			'bonus_stamina_heal'			=> 0,

			'bonus_stamina_max_percent'		=> 0,
			'bonus_stamina_heal_percent'	=> 0,

			'bonus_gold_mission'			=> 0,
			'bonus_gold_mission_percent'	=> 0,

			'bonus_exp_mission'				=> 0,
			'bonus_exp_mission_percent'		=> 0
		];

		$fetch_condition	= function ($type, $value) {
			if (($type == 'player' && $value > 0) || ($type == 'enemy' && $value < 0)) {
				return true;
			}

			return false;
		};

		foreach (['player', 'enemy'] as $type) {
			$current_effects	= $this->get_effects();
			if (!isset($current_effects[$type])) {
				$current_effects[$type]	= [];
			}

			foreach ($current_effects[$type] as $item_key => $item) {
				foreach ($item as $effect) {
					$effect_data	= ItemEffect::find($effect->id);
					$array_data		= $effect_data->as_array();
					$is_percent		= $effect_data->effect_type == 'percent';

					if ($effect_data->effect_direction == 'buff') {
						foreach ($array_data as $key => $value) {
							$percent_key	= $key . '_percent';

							if (isset($return[$percent_key]) && $is_percent) {
								$choosen_key	= $percent_key;
							} else {
								$choosen_key	= $key;
							}

							if ($fetch_condition($type, $value)) {
								if (array_key_exists($key, $return)) {
									if ($return_with_duration) {
										if (!is_array($return[$choosen_key])) {
											$return[$choosen_key]	= [];
										}

										if (!isset($return[$choosen_key][$effect->turns])) {
											$return[$choosen_key][$effect->turns]	= 0;
										}

										$return[$choosen_key][$effect->turns]	+= $value;
									} else {
										$return[$choosen_key]	+= $value;
									}
								}
							}
						}
					} else {
						foreach ($array_data as $key => $value) {
							$percent_key	= $key . '_percent';

							if (isset($return[$percent_key]) && $is_percent) {
								$choosen_key	= $percent_key;
							} else {
								$choosen_key	= $key;
							}

							if ($fetch_condition($type, $value)) {
								if (array_key_exists($key, $return)) {
									// we need to invert the sign for debuff + player, ebemies already have a minus sign
									if ($type == 'player') {
										$value	= -$value;
									}

									if ($return_with_duration) {
										if (!is_array($return[$choosen_key])) {
											$return[$choosen_key]	= [];
										}

										if (!isset($return[$choosen_key][$effect->turns])) {
											$return[$choosen_key][$effect->turns]	= 0;
										}

										$return[$choosen_key][$effect->turns]	+= $value;
									} else {
										$return[$choosen_key]	+= $value;
									}
								}
							}
						}
					}
				}
			}
		}

		return $return;
	}

	function clear_effects() {
		$this->_alloc_effects(true);
	}

	function rotate_effects($got_technique = false) {
		$this->_alloc_effects();
		$new_effects	= ['player' => [], 'enemy' => []];

		$fetch_condition	= function ($type, $value) {
			if (($type == 'player' && $value > 0) || ($type == 'enemy' && $value < 0)) {
				return true;
			}

			return false;
		};

		foreach (['player', 'enemy'] as $type) {
			foreach ($this->get_effects()[$type] as $item_key => $item) {
				foreach ($item as $effect) {
					$effect_data	= ItemEffect::find($effect->id);
					if ($effect_data->effect_direction == 'buff') {
						if ($fetch_condition($type, $effect_data->heals_life)) {
							$this->less_life	-= $effect_data->heals_life;
							if ($this->less_life < 0) {
								$this->less_life	= 0;
							}
						}

						if ($fetch_condition($type, $effect_data->heals_mana)) {
							$this->less_mana	-= $effect_data->heals_mana;
							if ($this->less_mana < 0) {
								$this->less_mana	= 0;
							}
						}

						if ($fetch_condition($type, $effect_data->renew_random_cooldown)) {
							$locks	= $this->get_technique_locks();
							if ($locks) {
								$this->remove_technique_lock(array_random_key($locks));
							}
						}

						if ($fetch_condition($type, $effect_data->heals_by_turn)) {
							$this->less_life	-= $effect_data->heals_by_turn;
							if ($this->less_life < 0) {
								$this->less_life	= 0;
							}
						}

						if ($fetch_condition($type, $effect_data->mana_through_turns)) {
							if ($this->battle_npc_id) {
								$current_turn	= $this->battle_npc()->current_turn;
							} else {
								$current_turn	= $this->battle_pvp()->current_turn;
							}

							if (!($current_turn % $effect_data->mana_through_turns)) {
								$this->less_mana	-= 1;
							}

							if ($this->less_mana < 0) {
								$this->less_mana	= 0;
							}
						}

						if ($fetch_condition($type, $effect_data->mana_half_life)) {
							if (has_chance($effect_data->mana_half_life) && $this->for_mana() <= $this->for_mana(true) / 2) {
								$this->less_mana	-= 1;

								if ($this->less_mana < 0) {
									$this->less_mana	= 0;
								}
							}
						}
					} else {

						if ($fetch_condition($type, $effect_data->heals_life)) {
							$this->less_life	+= $effect_data->heals_life;
							if ($this->less_life > $this->for_life()) {
								$this->less_life	= $this->for_life();
							}
						}

						if ($fetch_condition($type, $effect_data->heals_mana)) {
							$this->less_mana	+= $effect_data->heals_mana;
							if ($this->less_mana > $this->for_mana(true)) {
								$this->less_mana	= $this->for_mana(true);
							}
						}

						if ($fetch_condition($type, $effect_data->mana_through_turns)) {
							if ($this->battle_npc_id) {
								$current_turn	= $this->battle_npc()->current_turn;
							} else {
								$current_turn	= $this->battle_pvp()->current_turn;
							}

							if (!($current_turn % $effect_data->mana_through_turns)) {
								$this->less_mana	+= -1;
							}

							if ($this->less_mana > $this->for_mana()) {
								$this->less_mana	= $this->for_mana();
							}
						}

						if ($fetch_condition($type, $effect_data->lock_random_technique)) {
							$this->add_technique_lock(array_random_item($this->get_techniques())->item(), $effect->total_turns);
							$effect->turns		= 0;
						}

						if ($fetch_condition($type, $effect_data->increase_bleeding_duration)) {
							$this->increase_effects_with(['bleeding' => abs($effect_data->increase_bleeding_duration)]);
						}

						if ($fetch_condition($type, $effect_data->increase_slowness_duration)) {
							$this->increase_effects_with(['slowness' => abs($effect_data->increase_slowness_duration)]);
						}

						if ($fetch_condition($type, $effect_data->increase_confusion_duration)) {
							$this->increase_effects_with(['confusion' => abs($effect_data->increase_confusion_duration)]);
						}

						if ($fetch_condition($type, $effect_data->heals_by_turn)) {
							$this->less_life	+= abs($effect_data->heals_by_turn);
							if ($this->less_life > $this->for_life(true)) {
								$this->less_life	= $this->for_life(true);
							}
						}

						if ($fetch_condition($type, $effect_data->mana_half_life)) {
							if (has_chance($effect_data->mana_half_life) && $this->for_mana() <= $this->for_mana(true) / 2) {
								$this->less_mana	+= 1;
								if ($this->less_mana > $this->for_mana(true)) {
									$this->less_mana	= $this->for_mana(true);
								}
							}
						}
					}

					if (!in_array($effect->turns, ['infinity', 'fixed'])) {
						if ($effect->turns - 1 > 0) {
							$effect->turns--;

							if ($effect->secret) {
								$effect->revealed	= true;
							}

							$new_effects[$type][$item_key][$effect->id]	= $effect;
						} else {
							unset($new_effects[$type][$item_key][$effect->id]);
						}
					} else {
						// infinity effects never change, to infinity and beyoooond~
						$new_effects[$type][$item_key][$effect->id]	= $effect;
					}
				}
			}
		}

		$this->_fix_effect_array($new_effects);
		SharedStore::S($this->build_effects_uid(), $new_effects);
	}

	private function _remove_effects_with($keys) {
		$new_effects	= [];

		$fetch_condition	= function ($type, $value) {
			if (($type == 'player' && $value > 0) || ($type == 'enemy' && $value < 0)) {
				return true;
			}

			return false;
		};

		foreach ($keys as $key) {
			foreach (['player', 'enemy'] as $type) {
				foreach ($this->get_effects()[$type] as $item_key => $item) {
					foreach ($item as $effect) {
						$effect_data	= ItemEffect::find($effect->id);
						if (!$fetch_condition($type, $effect_data->$key)) {
							$new_effects[$type][$item_key][$effect->id]	= $effect;
						}
					}
				}
			}
		}

		$this->_fix_effect_array($new_effects);
		SharedStore::S($this->build_effects_uid(), $new_effects);
	}

	function has_effects_with($keys) {
		$return	= [];

		if (!is_array($keys)) {
			$keys	= [$keys];
		}

		foreach (array_keys($keys) as $key) {
			$return[$key]	= false;
		}

		$fetch_condition	= function ($type, $value) {
			if (($type == 'player' && $value > 0) || ($type == 'enemy' && $value < 0)) {
				return true;
			}

			return false;
		};

		foreach ($keys as $key) {
			foreach (['player', 'enemy'] as $type) {
				foreach ($this->get_effects()[$type] as $item_key => $item) {
					foreach ($item as $effect) {
						$effect_data	= ItemEffect::find($effect->id);

						if ($fetch_condition($type, $effect_data->$key)) {
							$return[$key]	= true;
						}
					}
				}
			}
		}

		reset($return);

		return sizeof($return) == 1 ? current($return) : $return;
	}

	function increase_effects_with($keys) {
		$effects			= $this->get_effects();
		$fetch_condition	= function ($type, $value) {
			if (($type == 'player' && $value > 0) || ($type == 'enemy' && $value < 0)) {
				return true;
			}

			return false;
		};

		foreach ($keys as $key => $value) {
			foreach (['player', 'enemy'] as $type) {
				foreach ($effects[$type] as $item_key => $item) {
					foreach ($item as $effect_key => $effect) {
						$effect_data	= ItemEffect::find($effect->id);

						if ($fetch_condition($type, $effect_data->$key)) {
							$effects[$type][$item_key][$effect_key]->total_turns	+= $value;
							$effects[$type][$item_key][$effect_key]->turns			+= $value;
						}
					}
				}
			}
		}

		$this->_fix_effect_array($effects);
		SharedStore::S($this->build_effects_uid(), $effects);
	}

	function apply_battle_effects($enemy) {
		if (is_a($this, 'Player')) {
			if (!$this->battle_pvp_id && !$this->battle_npc_id) {
				return;
			}
		}

		// Add pets effects
			$pet	= $this->get_active_pet();
			if ($pet) {
				$pet_item	= $pet->item();
				foreach ($pet_item->effects() as $key => $effect) {
					$this->add_fixed_effect($pet_item, 'pet', $effect, 'player');
					$enemy->add_fixed_effect($pet_item, 'pet', $effect, 'enemy');
				}
			}
		// <--

		// Regra para esconder os talentos na luta
		if ($this->battle_pvp_id) {
			if ($this->no_talent || $enemy->no_talent) {
				// $this->refresh_talents($enemy);
			} else {
				$this->refresh_talents($enemy);
			}
		} else {
			$this->refresh_talents($enemy);
		}

	}

	public function clear_fixed_effects($kind) {
		$this->_alloc_effects();
		$effects		= $this->get_effects();
		$new_effects	= [];

		foreach (['player', 'enemy'] as $type) {
			if (!isset($new_effects[$type])) {
				$new_effects[$type]	= [];
			}

			if (isset($effects[$type])) {
				foreach ($effects[$type] as $item_key => $item) {
					if (!isset($new_effects[$type][$item_key])) {
						$new_effects[$type][$item_key]	= [];
					}

					foreach ($item as $effect_key => $effect) {
						if ($effect->turns != 'fixed') {
							$new_effects[$type][$item_key][$effect_key]	= $effect;
						}
					}
				}
			}
		}

		SharedStore::S($this->build_effects_uid(), $new_effects);
	}

	public function has_visible_effect($effect_id) {
		$this->_alloc_effects();

		$visible	= true;
		$effects	= $this->get_effects();
		if (!$effects) {
			$content	= json_encode([
				'hasVisibleEffect'	=> $effects
			]);
			Recordset::insert('log', [
				'user_id'	=> 0,
				'player_id'	=> 0,
				'content'	=> $content
			]);
		}

		foreach (['player', 'enemy'] as $type) {
			if ($effects) {
				foreach ($effects[$type] as $item_key => $item) {
					foreach ($item as $effect_key => $effect) {
						if ($effect->secret && !$effect->revealed && $effect->id == $effect_id) {
							$visible = false;
							break;
						}
					}
				}
			}
		}

		return $visible;
	}

	public function get_sum_effect($effect_name) {
		$this->_alloc_effects();

		$effects		= $this->get_effects();
		if (!$effects) {
			$content	= json_encode([
				'getSumEffect'	=> $effects
			]);
			Recordset::insert('log', [
				'user_id'	=> 0,
				'player_id'	=> 0,
				'content'	=> $content
			]);
		}
		$return			= 0;
		$effect_name	= str_replace('_percent', '', $effect_name);

		foreach (['player', 'enemy'] as $type) {
			if ($effects) {
				foreach ($effects[$type] as $item_key => $item) {
					foreach ($item as $effect_key => $effect) {
						$effect_data	= ItemEffect::find($effect->id);

						if ($effect_data->$effect_name) {
							if ($type == 'player') {
								$return	+= -$effect_data->$effect_name;
							} else {
								$return	+= $effect_data->$effect_name;
							}
						}
					}
				}
			}
		}

		return $return;
	}

	private function _alloc_effects($clear = false) {
		$memory	= SharedStore::G($this->build_effects_uid(), null);
		if (is_null($memory) || (is_array($memory) && !sizeof($memory)) || $clear) {
			$memory	= [
				'player' => [],
				'enemy' => []
			];
			SharedStore::S($this->build_effects_uid(), $memory);
		}
	}

	private function _fix_effect_array(&$array) {
		if (!isset($array['player'])) {
			$array['player']	= [];
		}

		if (!isset($array['enemy'])) {
			$array['enemy']	= [];
		}
	}
}
