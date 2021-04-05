<?php
	class Item extends Relation {
		static	$always_cached				= true;

		public	$is_turn_skip				= false;

		private	$_character_theme_id		= null;
		private	$_anime_id					= null;
		private	$_formula					= null;
		private	$_player					= null;
		private $_player_item				= null;
		private $_orig_req_for_hit_chance	= 0;
		private	$_forced_attack_type_id		= null;

		function after_assign() {
			$this->formula();
		}

		function set_player(&$instance) {
			$this->_player	=& $instance;
		}

		function set_player_item(&$instance) {
			$this->_player_item	=& $instance;
		}

		function attack_type() {
			if (!is_null($this->_forced_attack_type_id)) {
				return ItemAttackType::find_first($this->_forced_attack_type_id);
			}

			$description	= $this->description();

			if ($description) {
				return ItemAttackType::find_first($description->item_attack_type_id);
			} else {
				return false;
			}
		}

		function force_attack_type($id) {
			$this->_forced_attack_type_id	= $id;
		}

		function formula($regen = false) {
			if(!$regen && $this->_formula) {
				return $this->_formula;
			}

			$plus	= '#237fd3';
			$minus	= '#ED1818';

			$formula						= new stdClass();
			$formula->damage				= 0;
			$formula->defense				= 0;
			$formula->consume_mana			= $this->mana_cost;
			$formula->generic				= $this->is_generic;
			$formula->cooldown				= $this->cooldown;
			$formula->duration				= $this->duration;
			$formula->critical				= $this->_player_item ? $this->_player->for_crit() : 0;
			$formula->hit_chance			= $this->for_hit;
			$formula->attack_speed			= $this->attack_speed;
			$formula->is_player_item		= $this->_player_item ? true : false;
			$formula->color_types			= new stdClass();

			$formula->color_types->attack_speed	= '';
			$formula->color_types->consume_mana	= '';
			$formula->color_types->damage		= '';
			$formula->color_types->defense		= '';
			$formula->color_types->hit_chance	= '';

			//$value	= $this->mana_cost * 15 + $this->attack_speed / 2 - ($this->item_effect_ids ? $this->attack_speed/2 : 0);
			$value = $this->damage;

			// Buffs doesn't have attack of defense
			if (!$this->is_buff) {
				if($this->is_defensive) {
					$formula->defense		= floor($value);
				} else {
					$formula->damage		= floor($value);
				}
			}

			if($this->_player) {
				if ($attack_type = $this->attack_type()) {
					$attack_type	= $attack_type->id;
				}

				$effects			= $this->_player->get_parsed_effects();
				$extras 			= $this->_player->attributes();

				if ($formula->cooldown) {
					$formula->cooldown	+= (($effects['slowness_percent'] || $effects['slowness'])  ? 1 : 0);
					//$formula->cooldown	-= $this->_player->attributes()->sum_bonus_cooldown;
				}

				$formula->attack_speed	+= -($effects['slowness'] + percent($effects['slowness_percent'], $this->attack_speed));
				$formula->attack_speed	-= $effects['attack_speed'] + percent($effects['attack_speed_percent'], $this->attack_speed);

				$formula->hit_chance	+= ($effects['confusion'] + percent($effects['confusion_percent'], $this->for_hit))
										+  $effects['for_hit'] + percent($effects['for_hit_percent'], $this->for_hit);

				$formula->consume_mana	-= $effects['next_mana_cost'];

				if ($effects['slowness'] || $effects['slowness_percent']) {
					$formula->attack_speed	+= -($effects['increase_slowness_damage'] + percent($effects['increase_slowness_damage_percent'], $this->attack_speed));
				}

				if ($effects['confusion'] || $effects['confusion_percent']) {
					$formula->hit_chance	+= $effects['increase_confusion_damage'] + percent($effects['increase_confusion_damage_percent'], $this->for_hit);
				}

				// New effects
				if (!$this->is_defensive && !$this->is_buff) {
					if ($attack_type == 1) {
						$formula->damage	+= $effects['elemental_damage'] + percent($effects['elemental_damage_percent'], $value);
					}

					if ($attack_type == 2) {
						$formula->damage	+= $effects['fighter_damage'] + percent($effects['fighter_damage_percent'], $value);
					}

					if ($attack_type == 3) {
						$formula->damage	+= $effects['magic_damage'] + percent($effects['magic_damage_percent'], $value);
					}

					if ($attack_type == 4) {
						$formula->damage	+= $effects['warrior_damage'] + percent($effects['warrior_damage_percent'], $value);
					}

					if ($attack_type == 5) {
						$formula->damage	+= $effects['ranger_damage'] + percent($effects['ranger_damage_percent'], $value);
					}

					if ($this->is_generic) {
						$formula->damage	+= $effects['generic_attack_damage'] + percent($effects['generic_attack_damage_percent'], $value);
						$formula->damage	+=  percent($extras->generic_technique_damage, $value);
					}

					if (!$this->is_generic) {
						$formula->damage	+= $effects['unique_attack_damage'] + percent($effects['unique_attack_damage_percent'], $value);
						$formula->damage	+=  percent($extras->unique_technique_damage, $value);
					}
				} elseif ($this->is_defensive && !$this->is_buff) {
					$formula->defense	+= $effects['generic_defense'] + percent($effects['generic_defense_percent'], $value);
					$formula->defense	+=  percent($extras->defense_technique_extra, $value);
				}

				if ($formula->attack_speed > $this->attack_speed) {
					$formula->color_types->attack_speed	= $minus;
				} elseif ($formula->attack_speed < $this->attack_speed) {
					$formula->color_types->attack_speed	= $plus;
				}

				if ($formula->hit_chance > $this->for_hit) {
					$formula->color_types->hit_chance	= $plus;
				} elseif ($formula->hit_chance < $this->for_hit) {
					$formula->color_types->hit_chance	= $minus;
				}

				if ($formula->consume_mana > $this->mana_cost) {
					$formula->color_types->consume_mana	= $minus;
				} elseif ($formula->consume_mana < $this->mana_cost) {
					$formula->color_types->consume_mana	= $plus;
				}

				if($this->is_defensive) {
					if ($this->_player->for_def() > $this->_player->for_def(true) || $formula->defense > $value) {
						$formula->color_types->defense	= $plus;
					} elseif ($this->_player->for_def() < $this->_player->for_def(true) || $formula->defense < $value) {
						$formula->color_types->defense	= $minus;
					}
				} else {
					if ($this->_player->for_atk() > $this->_player->for_atk(true) || $formula->damage > $value) {
						$formula->color_types->damage	= $plus;
					} elseif ($this->_player->for_atk() < $this->_player->for_atk(true) || $formula->damage < $value) {
						$formula->color_types->damage	= $minus;
					}
				}
			}

			if($formula->cooldown < 0) {
				$formula->cooldown	= 0;
			}

			if($this->item_type_id == 3 || $this->item_type_id == 4) { // Ability and speciality don't use mana
				$formula->consume_mana	= 0;
			} else {
				/*
				$formula->consume_mana	= floor($this->mana_cost);

				if($this->_player) {
					$formula->consume_mana	-= percent($this->_player->attributes()->sum_bonus_mana_consume, $formula->consume_mana);
				}
				*/
			}

			$this->_formula	= $formula;
			return $this->_formula;
		}

		function effects() {
			$chances	= explode(',', $this->effect_chances);
			$effects	= ItemEffect::find('id IN(' . $this->item_effect_ids . ')', ['cache' => true]);

			if ($this->_player) {
				$player_effects	= $this->_player->get_parsed_effects();
			}

			foreach ($effects as $key => $effect) {
				$raw_chance	= $chances[$key];
				if ($this->_player) {
					if ($effect->bleeding && $player_effects['double_bleeding_chance']) {
						$chances[$key]	= $raw_chance * 2;
					}

					if ($effect->stun && $player_effects['double_stun_chance']) {
						$chances[$key]	= $raw_chance * 2;
					}

					if ($effect->slowness && $player_effects['double_slowness_chance']) {
						$chances[$key]	= $raw_chance * 2;
					}

					if ($effect->confusion && $player_effects['double_confusion_chance']) {
						$chances[$key]	= $raw_chance * 2;
					}

					if ($effect->bleeding && $player_effects['bleeding_chance']) {
						$chances[$key]	+= $player_effects['bleeding_chance'];
					}

					if ($effect->slowness && $player_effects['slowness_chance']) {
						$chances[$key]	+= $player_effects['slowness_chance'];
					}

					if ($effect->confusion && $player_effects['confusion_chance']) {
						$chances[$key]	+= $player_effects['confusion_chance'];
					}

					if ($effect->stun && $player_effects['stun_chance']) {
						$chances[$key]	+= $player_effects['stun_chance'];
					}
				}

				$effect->chance		= $chances[$key];
				$effect->duration	= $this->duration;
			}

			return $effects;
		}

		function set_character_theme($theme) {

			if(is_numeric($theme)) {
				$this->_character_theme_id	= $theme;
			} else {
				$this->_character_theme_id	= $theme->id;
				$this->set_anime($theme->character()->anime_id);
			}
		}

		function set_anime($id) {
			$this->_anime_id	= $id;
		}

		function anime() {
			if(!$this->_anime_id) {
				return false;
			}

			return Anime::find($this->_anime_id);
		}
		function anime_description($anime_id) {
			return AnimeDescription::find_first('anime_id=' . $anime_id . ' AND language_id=' . $_SESSION['language_id'], array('cache' => true));
		}

		function description($anime_id = null, $language_id = null) {
			
			$anime_id	= $anime_id ? $anime_id : $this->_anime_id;
			
			/*if($_SESSION['universal'] && $this->id==36){
				echo "<pre>";
				print_r($this);
				die();
			}
			*/
			if (($this->item_type_id == 3 && $this->_player) || ($this->item_type_id == 6 && $this->is_generic) || ($this->item_type_id == 11 && $this->is_generic)) {
				return ItemDescription::find_first('item_id=' . $this->id . ' AND language_id=' . $_SESSION['language_id'], array('cache' => true));
			}
			
			if(($this->is_generic && (!$anime_id || in_array($this->item_type_id, [5,7,9,10,12,13,15,16]))) || in_array($this->id, [112, 113])) {
				if($this->item_type_id == 3){
					$description	= ItemDescription::find_first('item_id=' . $this->id . ' AND language_id=' . $_SESSION['language_id'], array('cache' => true));
				}else{
					if(!$this->parent_id){
						$description	= ItemDescription::find_first('item_id=' . $this->id . ' AND anime_id=0 AND language_id=' . $_SESSION['language_id'], array('cache' => true));
					}else{
						$description	= ItemDescription::find_first('item_id=' . $this->parent_id . ' AND anime_id=0 AND language_id=' . $_SESSION['language_id'], array('cache' => true));

					}
	
				}
			} else {
				if(!$this->_character_theme_id) {
					if(!$anime_id) {
						throw new Exception("Anime not specified to get #{$this->id}", 1);
					}
					if(!$this->parent_id){
						$description	= ItemDescription::find_first('item_id=' . $this->id . ' AND anime_id=' . $anime_id . ' AND language_id=' . $_SESSION['language_id'], array('cache' => true));
					}else{
						$description	= ItemDescription::find_first('item_id=' . $this->parent_id . ' AND anime_id=' . $anime_id . ' AND language_id=' . $_SESSION['language_id'], array('cache' => true));

					}
				} else {
					if(!$this->parent_id){
						$description	= CharacterThemeItem::find_first('character_theme_id=' . $this->_character_theme_id . ' AND item_id=' . $this->id .' AND language_id=' . $_SESSION['language_id'], array('cache' => true));
					}else{
						$description	= CharacterThemeItem::find_first('character_theme_id=' . $this->_character_theme_id . ' AND item_id=' . $this->parent_id .' AND language_id=' . $_SESSION['language_id'], array('cache' => true));

					}
				}
			}

			if($this->_player_item && $this->_player_item->level > 1) {
				$description->name	.= " Lvl. " . $this->_player_item->level;
			}

			return $description;
		}

		function descriptions($language_id) {
			return ItemDescription::find('item_id=' . $this->id . ' AND language_id=' . $_SESSION['language_id'], array('cache' => true));
		}

		function image($path_only = false) {
			$description	= $this->description();
			/*if($_SESSION['universal']){
			print_r($description);
			die();
			}*/
			if ($this->item_type_id == 3 && $this->_player) {
				$path	= "pets/" . $description->image;

				if ($path_only) {
					return $path;
				} else {
					return '<img class="pet" src="' . image_url($path) . '" />';
				}
			}

			if ($this->item_type_id != 8) {
				$base	= 'items';

				if($this->item_type_id == 6) {
					$base	= 'talents';
				}

				if($this->_character_theme_id) {
					$path	= $base . '/' . $this->_anime_id . '/' . $this->_character_theme_id .'/' . $description->image;
				} else {
					if($this->is_generic && in_array($this->item_type_id, [5,6,7,10,12,13,15,16])) {
						$path	= $base . '/' . $description->image;
					} else {
						$path	= $base . '/' . $this->_anime_id . '/' . $description->image;
					}
				}

				if($path_only) {
					return $path;
				} else {
					return '<img src="' . image_url($path) . '" name="' . $description->name . '" title="' . $description->name . '" />';
				}
			} else {
				if (in_array($this->_anime_id, [6])) {
					$path	= 'equipments/' . $this->_anime_id . '/' . $this->_player_item->player()->character_id . '/' . $this->_player_item->rarity . '/' . $this->_player_item->slot_name . '.png';
				} else {
					$path	= 'equipments/' . $this->_anime_id . '/' . $this->_player_item->rarity . '/' . $this->_player_item->slot_name . '.png';
				}

				if($path_only) {
					return $path;
				} else {
					return '<img src="' . image_url($path) . '" />';
				}
			}
		}

		function has_requirement($player, $ignores = []) {
			$user = $player->user();
			$ok				= true;
			$log			= '<ul class="requirement-list">';
			$error			= '<li class="error"><i class="fa fa-times fa-fw"></i> %result</li>';
			$success		= '<li class="success"><i class="fa fa-ok fa-fw"></i> %result</li>';

			if ($this->item_type_id == 6) {
				$ok		= $this->mana_cost > $user->level ? false : $ok;
				$log	.= str_replace('%result', t('items.requirements.level', array('level' => $this->mana_cost)), $this->mana_cost > $user->level ? $error : $success);
			}

			return array('has_requirement' => $ok, 'requirement_log' => $log . "</ul>");
		}

		function technique_tooltip($battle_tooltip = false) {
			if($this->_character_theme_id) {
				$unique			= t('techniques.types.unique');
				$unique_class	= 'unique';
			} else {
				$unique			= t('techniques.types.generic');
				$unique_class	= 'generic';
			}
			if($this->item_type_id == 3) {
				$type		= t('techniques.types.ability');
				$type_class	= 'ability';
			} elseif($this->item_type_id == 4) {
				$type		= t('techniques.types.speciality');
				$type_class	= 'speciality';
			} elseif($this->item_type_id == 7) {
				$type			= t('techniques.types.weapons');
				$type_class		= 'buff';

				$unique_class	= 'unique';
				$unique			= t('techniques.types.unique');
			} else {
				if($this->is_buff) {
					$type		= t('techniques.types.buff');
					$type_class	= 'buff';
				} else {
					if($this->formula()->defense) {
						$type		= t('techniques.types.defense');
						$type_class	= 'defense';
					} else {
						$type		= t('techniques.types.attack');
						$type_class	= 'attack';
					}
				}
			}
			$assigns	= array(
				'item'				=> $this,
				'player_item'		=> $this->_player_item,
				'description'		=> $this->description(),
				'type'				=> $type,
				'type_class'		=> $type_class,
				'unique'			=> $unique,
				'unique_class'		=> $unique_class,
				'formula'			=> $this->formula(),
				'battle_tooltip'	=> $battle_tooltip,
				'player'			=> $this->_player
			);

			return partial('shared/technique_tooltip', $assigns);
		}

		function consumable_tooltip($battle_tooltip = false) {
			$assigns	= array(
				'item'				=> $this,
				'player_item'		=> $this->_player_item,
				'description'		=> $this->description(),
				'battle_tooltip'	=> $battle_tooltip
			);

			return partial('shared/consumable_tooltip', $assigns);
		}

		function technique_level_tooltip($battle_tooltip = false) {
			$ok				= true;
			$stats			= $this->_player_item->stats();
			$player_item	= $this->_player_item;
			$bonuses		= array();
			$tooltip		= array();

			if($player_item->level > 1) {
				$where		= ' AND is_generic=' . $this->is_generic; //($this->_character_theme_id ? '0' : '1');
				$where		.= ' AND is_defensive=' . $this->is_defensive;
				$where		.= ' AND is_buff=' . $this->is_buff;
				$where		.= ' AND req_graduation_sorting=' . $this->req_graduation_sorting;

				$levels	= ItemLevel::find('1=1 ' . $where, array('cache' => true));

				foreach($levels as $level) {
					if (!isset($bonuses[$level->sorting])) {
						$bonuses[$level->sorting]	= array();
					}

					extract($level->parse($stats, $this->_player, $this->is_buff));
					$bonus		= '';

					// Bonuses -->
						if($level->for_inc_crit) {
							$bonus	.= t('techniques.tooltip.level_req.for_inc_crit', array('count' => $level->for_inc_crit));
						}

						if($level->for_mana) {
							$bonus	.= t('techniques.tooltip.level_req.for_mana', array(
								'count' => $level->for_mana,
								'mana'	=> t('formula.for_mana.' . $this->_player->character()->anime_id)
							));
						}

						if($level->for_atk) {
							$bonus	.= t('techniques.tooltip.level_req.for_atk', array('count' => $level->for_atk));
						}

						if($level->for_def) {
							$bonus	.= t('techniques.tooltip.level_req.for_def', array('count' => $level->for_def));
						}

						if($level->for_hit_chance) {
							$bonus	.= t('techniques.tooltip.level_req.for_hit_chance', array('count' => $level->for_hit_chance));
						}

						if($level->for_hit_chance_inc) {
							$bonus	.= t('techniques.tooltip.level_req.for_hit_chance_inc', array('count' => $level->for_hit_chance_inc));
						}

						if ($this->is_buff) {
							if($level->cooldown) {
								$bonus	.= t('techniques.tooltip.level_req.duration', array('count' => $level->cooldown));
							}
						} else {
							if($level->cooldown) {
								$bonus	.= t('techniques.tooltip.level_req.cooldown', array('count' => $level->cooldown));
							}
						}
					// <--

					if($level->req_player_item_level > $player_item->level) {
						$ok	= false;
					}

					$bonuses[$level->sorting][$level->req_player_item_level]	= array(
						'req'	=> $req,
						'bonus'	=> $bonus,
						'ok'	=> $ok
					);
				}

				foreach($bonuses as $slot => $bonus) {
					if(!isset($tooltip[$slot])) {
						$tooltip[$slot]	= array();
					}

					$tooltip[$slot]['levels']		= [];
					$tooltip[$slot]['description']	= $bonus[2]['req'];
					$next_was_shown					= false;

					foreach($bonus as $level => $data) {
						if($next_was_shown || $level > $player_item->level) {
							break;
						}

						if($data['ok']) {
							if(isset($bonus[$level - 1])) {
								unset($tooltip[$slot]['levels'][$level - 1]);
							}

							if(isset($bonus[$level + 1])) {
								$tooltip[$slot]['description']	= $bonus[$level + 1]['req'];
							} else {
								$tooltip[$slot]['description']	= $bonus[$level]['req'];
							}
						} else {
							$next_was_shown	= true;
						}

						$tooltip[$slot]['levels'][$level]	= $data;
					}
				}

				if($this->id == 35) {
					//print_r($bonuses);
					//print_r($tooltip);
				}
			}

			return partial('shared/technique_level_tooltip', array(
				'item'				=> $this,
				'player_item'		=> $player_item,
				'stats'				=> $stats,
				'tooltip'			=> $tooltip,
				'battle_tooltip'	=> $battle_tooltip
			));
		}

		function talent_tooltip($battle_tooltip = false) {
			$assigns	= [
				'battle_tooltip'	=> $battle_tooltip,
				'item'				=> $this,
				'description'		=> $this->description(),
				'player_item'		=> $this->_player_item,
				'effects'			=> $this->effects(),
				'player'			=> $this->_player
			];

			return partial('shared/talent_tooltip', $assigns);
		}
		function weapon_tooltip($battle_tooltip = false) {
			return $this->technique_tooltip($battle_tooltip);
		}

		function tooltip($battle_tooltip = false) {
			if($this->item_type_id == 3) {
				return $this->pet_tooltip($battle_tooltip);
			} elseif($this->item_type_id == 5 || $this->item_type_id == 10 || $this->item_type_id == 12 || $this->item_type_id == 13 || $this->item_type_id == 15 || $this->item_type_id == 16) {
				return $this->consumable_tooltip($battle_tooltip);
			} elseif($this->item_type_id == 6) {
				return $this->talent_tooltip($battle_tooltip);
			} elseif($this->item_type_id == 7) {
				return $this->weapon_tooltip($battle_tooltip);
			} elseif($this->item_type_id == 8) {
				return $this->equipment_tooltip($battle_tooltip);
			} else {
				return $this->technique_tooltip($battle_tooltip);
			}
		}

		function exp_needed_for_level() {
			$rates	= [
				2	=> [2400, 3000, 3600, 4200, 4800, 5400],
				3	=> [3400, 4200, 5000, 5800, 6600, 7400],
				4	=> [4400, 5400, 6400, 7400, 8400, 9400],
				5	=> [5400, 6600, 7800, 9000, 10200, 11800],
				6	=> [6800, 7800, 9400, 10200, 11600, 13400]
			];

			return $rates[$this->_player_item->level + 1][$this->req_graduation_sorting - 1];
		}

		function player_item() {
			return $this->_player_item;
		}

		function chat_embed() {
			return '';
		}
		static function generate_equipment($player, $rarity_fragment = NULL, $slot = NULL) {
			$slots	= [
				'head',
				'shoulder',
				'chest',
				'neck',
				'hand',
				'leggings'
			];

			$attributes_by_slot	= [];
			foreach ($slots as $slot) {
				$attributes_by_slot[$slot]	= [];
			}

			$ignore_sums	= ['cooldown_reduction', 'for_stamina', 'npc_battle_count'];
			if (!$slot) {
				$choosen_slot	= $slots[rand(0, sizeof($slots) - 1)];
			} else {
				$choosen_slot	= $slot;
			}

			$rarity_drop_by_graduation	= [
				1	=> [
					'common'	=> 95,
					'rare'		=> 5,
					'legendary'	=> 0
				],
				2	=> [
					'common'	=> 80,
					'rare'		=> 20,
					'legendary'	=> 0
				],
				3	=> [
					'common'	=> 65,
					'rare'		=> 30,
					'legendary'	=> 5
				],
				4	=> [
					'common'	=> 50,
					'rare'		=> 40,
					'legendary'	=> 10
				],
				5	=> [
					'common'	=> 35,
					'rare'		=> 50,
					'legendary'	=> 15
				],
				6	=> [
					'common'	=> 20,
					'rare'		=> 60,
					'legendary'	=> 20
				]
			];

			$bonuses_by_rarity	= [
				'common'	=> [ 1 ],
				'rare'		=> [ 2 ],
				'legendary'	=> [ 3 ]
			];

			$additional_chance_by_graduation	= [
				[ 10,	35,	50,	5,	1 ],
				[ 15,	30,	45,	10,	1 ],
				[ 20,	25,	40,	15,	1 ],
				[ 25,	20,	35,	20,	1 ],
				[ 30,	15,	30,	25,	1 ],
				[ 35,	10,	25,	30,	1 ]
			];

			$choosables	= [
				'cooldown_reduction'		=> 'cooldown_reduction_id',
				'technique_attack_increase'	=> 'technique_attack_increase_id',
				'technique_mana_reduction'	=> 'technique_mana_reduction_id',
				'technique_crit_increase'	=> 'technique_crit_increase_id',
				'technique_zero_mana'		=> 'technique_zero_mana_id'
			];

			$bases	= [
				 [
					[
						'for_atk'		=> [1, 2, 3, 5],
						'for_def'		=> [1, 2, 3, 5],
						'for_crit'		=> [1, 2],
						'for_abs'		=> [1, 2],
						'for_inc_crit'	=> [1, 3],
						'for_inc_abs'	=> [1, 3],
						'for_prec'		=> [1, 2],
						'for_init'		=> [1, 2]
					], [
						'for_atk'		=> [1, 2, 3, 5],
						'for_def'		=> [1, 2, 3, 5],
						'for_crit'		=> [1, 2],
						'for_abs'		=> [1, 2],
						'for_inc_crit'	=> [1, 3],
						'for_inc_abs'	=> [1, 3],
						'for_prec'		=> [1, 2],
						'for_init'		=> [1, 2]
					], [
						'for_atk'		=> [1, 3, 3, 7],
						'for_def'		=> [1, 3, 3, 7],
						'for_crit'		=> [1, 3],
						'for_abs'		=> [1, 3],
						'for_inc_crit'	=> [1, 4],
						'for_inc_abs'	=> [1, 4],
						'for_prec'		=> [1, 3],
						'for_init'		=> [1, 3]
					], [
						'for_atk'		=> [1, 4, 3, 9],
						'for_def'		=> [1, 4, 3, 9],
						'for_crit'		=> [1, 4],
						'for_abs'		=> [1, 4],
						'for_inc_crit'	=> [1, 5],
						'for_inc_abs'	=> [1, 5],
						'for_prec'		=> [1, 4],
						'for_init'		=> [1, 4]
					], [
						'for_atk'		=> [1, 5, 4, 9],
						'for_def'		=> [1, 5, 4, 9],
						'for_crit'		=> [1, 5],
						'for_abs'		=> [1, 5],
						'for_inc_crit'	=> [1, 6],
						'for_inc_abs'	=> [1, 6],
						'for_prec'		=> [1, 5],
						'for_init'		=> [1, 5]
					], [
						'for_atk'		=> [2, 6, 4, 11],
						'for_def'		=> [2, 6, 4, 11],
						'for_crit'		=> [1, 6],
						'for_abs'		=> [1, 6],
						'for_inc_crit'	=> [1, 7],
						'for_inc_abs'	=> [1, 7],
						'for_prec'		=> [1, 6],
						'for_init'		=> [1, 6]
					]
				]
			];

			$values					= [];
			$current_grad			= $player->graduation()->sorting;

			$rarity_base			= $rarity_drop_by_graduation[$current_grad];
			$rarity_choosen_name	= '';
			$have_extras			= FALSE;
			
			if (is_null($rarity_fragment)) {
				while (TRUE) {
					$rarity_choosen_id	= 0;
	
					foreach ($rarity_base as $rarity => $chance) {
						if (rand(1, 100) <= $chance) {
							$rarity_choosen_name	= $rarity;
							break 2;
						}
	
						$rarity_choosen_id++;
					}
				}
			} else {
				switch ($rarity_fragment) {
					case 0:
						$rarity_choosen_name	= "common";
						$rarity_choosen_id		= 0;
						break;
					case 1:
						$rarity_choosen_name	= "rare";
						$rarity_choosen_id		= 1;
						break;
					case 2:
						$rarity_choosen_name	= "legendary";
						$rarity_choosen_id		= 2;
					break;
				}	
			}

			foreach ($bases as $block => $base) {
				$attribute_counter	= $bonuses_by_rarity[$rarity_choosen_name][$block];
				$extras				= $additional_chance_by_graduation[$current_grad - 1];
				$extra_chance		= $extras[$block];

				if (rand(1, 100) <= 25 && rand(1, 100) <= $extra_chance && !$have_extras) {
					$attribute_counter	+= $extras[4];
					$have_extras		= TRUE;
				}

				if ($attribute_counter) {
					while (TRUE) {
						foreach ($base[$current_grad - 1] as $attribute => $value) {
							if (in_array($attribute, $attributes_by_slot[$choosen_slot])) {
								continue;
							}

							if (isset($values[$attribute])) {
								continue;
							}

							if (rand(1, 100) > 10) {
								continue;
							}

							if (!in_array($attribute, $ignore_sums)) {
								if ($rarity_choosen_id == 2) {
									$value[0]++;
								}

								if ($rarity_choosen_id == 3 || $rarity_choosen_id == 4) {
									$value[0]	+= 2;
								}

								$values[$attribute]	= rand($value[0] * 10, $value[1] * 10) / 10;
							} else {
								$values[$attribute]	= rand($value[0] * 10, $value[1] * 10) / 10;
							}

							if (isset($choosables[$attribute])) {
								$items_query	= Recordset::query('SELECT id FROM items WHERE item_type_id = 1 AND id NOT IN (112, 113)', TRUE)->result_array();
								$values[$choosables[$attribute]]	= $items_query[rand(0, sizeof($items_query) - 1)]['id'];
							}

							$attribute_counter--;

							if (!$attribute_counter) {
								break 2;
							}
						}
					}
				}
			}

			$player_item			= new PlayerItem();
			$player_item->player_id	= $player->id;
			$player_item->item_id	= 114;
			$player_item->slot_name	= $choosen_slot;
			$player_item->rarity	= $rarity_choosen_name;
			$player_item->quantity	= 1;
			$player_item->save();

			$attribute						= new PlayerItemAttribute();
			$attribute->player_item_id		= $player_item->id;
			$attribute->graduation_sorting	= $player->graduation()->sorting;
			$attribute->have_extra			= $have_extras ? 1 : 0;

			foreach ($values as $property => $value) {
				$attribute->$property	= round($value, 2);
			}

			$attribute->save();

			return $player_item;
		}
		function equipment_tooltip() {
			$attributes			= [];
			$attribute_object	= $this->_player_item->attributes();

			foreach ($attribute_object->get_fields() as $key) {
				$attributes[$key]	= $attribute_object->$key;
			}

			$assigns	= [
				'attributes'	=> $attributes,
				'item'			=> $this,
				'player_item'	=> $this->_player_item,
				'ignores'		=> [
					'created_at',
					'id',
					'player_item_id'
				]
			];

			return partial('shared/equipment_tooltip', $assigns);
		}

		function pet_tooltip($battle_tooltip = false) {
			$assigns	= [
				'battle_tooltip'	=> $battle_tooltip,
				'effects'			=> $this->effects(),
				'item'				=> $this,
				'player_item'		=> $this->_player_item,
				'player'			=> $this->_player
			];

			return partial('shared/pet_tooltip', $assigns);
		}

		function embed() {
			// $attributes	= $this->_player_item->attributes();
			// $embed		= [
			// 	'attributes'	=> $attributes->as_array(),
			// 	'name'			=> $attributes->name(),
			// 	'rarity'		=> $this->_player_item->rarity,
			// 	'id'			=> $this->_player_item->id
			// ];

            // $iv = substr(CHAT_KEY, 0, 16);
			// return openssl_encrypt(json_encode($embed), 'AES-256-CBC', CHAT_KEY,0, $iv);
			return ;
		}

		function equipment_sell_price() {
			$attributes	= $this->_player_item->attributes()->as_array();
			$affixes	= 0;
			$rarities	= [
				'common'	=> 1,
				'rare'		=> 2,
				'epic'		=> 3,
				'legendary'	=> 4,
				'set'		=> 5
			];

			$price		= $rarities[$this->_player_item->rarity] * 50;

			foreach ($attributes as $attribute => $value) {
				if (in_array($attribute, ['id', 'player_item_id', 'graduation_sorting', 'have_extra', 'is_new', 'created_at'])) {
					continue;
				}

				if ($value > 0) {
					$affixes++;
				}
			}

			return $price + ($affixes * 25);
		}

		function is_strong_to($to) {
			$attack_type	= $this->attack_type();

			if (!$attack_type) {
				return false;
			}

			return $attack_type->strong_to == (int)$to;
		}

		function is_weak_to($to) {
			$attack_type	= $this->attack_type();

			if (!$attack_type) {
				return false;
			}

			return $attack_type->weak_to == (int)$to;
		}

		function get_weakness() {
			return ItemAttackType::find('strong_to=' . $this->attack_type()->id, ['cache' => true]);
		}

		function get_strenght() {
			return ItemAttackType::find('weak_to=' . $this->attack_type()->id, ['cache' => true]);
		}

		function get_buy_mode_for($player, $item_id = FALSE) {
		    if (!$item_id)
		        $item_id = $this->id;

            $bought_free		= PlayerStarItem::find_first("player_id=" . $player . " AND item_id=" . $item_id . " AND buy_mode = 0");
            $bought_currency	= PlayerStarItem::find_first("player_id=" . $player . " AND item_id=" . $item_id . " AND buy_mode = 1");

            $buy_mode = 0;
            if ($bought_free && !$bought_currency)
                $buy_mode = 1;
            elseif ($bought_free && $bought_currency)
                $buy_mode = 2;

            return $buy_mode;
		}
	}