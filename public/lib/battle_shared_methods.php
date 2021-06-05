<?php
trait BattleSharedMethods {
	function ability_speciality() {
		$this->layout		= false;
		$this->as_json		= true;
		$errors				= [];
		$player				= Player::get_instance();
		$battle				= $player->battle_pvp_id ? $player->battle_pvp() : $player->battle_npc();
		$enemy				= $player->battle_pvp_id ? $battle->enemy() : ($player->battle_npc_challenge ? $player->get_npc_challenge() : $player->get_npc());
		$is_technique_copy	= false;
		$kill_with_one_hit	= false;

		if ($_POST['item'] == 'ability' || $_POST['item'] == 'speciality') {
			$target		= $_POST['item'] == 'ability' ? $player->ability() : $player->speciality();
			$target_var	= $_POST['item'] == 'ability' ? 'ability' : 'speciality';

			if($target->consume_mana > $player->for_mana()) {
				$errors[]	= t('battles.errors.no_mana', ['mana' => t('formula.for_mana.' . $player->character()->anime_id)]);
			}

			if (SharedStore::G('battle_used_' . $target_var . '_' . $player->id)) {
				$errors[]	= t('battles.errors.used_' . $target_var);
			}

			foreach ($target->effects() as $key => $effect) {
				if($effect->kill_with_one_hit){
					$rand = rand(0, 100);
					if($rand <= $effect->kill_with_one_hit){
						$kill_with_one_hit	= true;
					}
				}
				if ($effect->copy_last_technique) {
					$is_technique_copy	= true;
					$got_technique		= is_a($enemy, 'Player') ? SharedStore::G('last_battle_item_of_' . $enemy->id) : SharedStore::G('last_battle_npc_item_of_' . $player->id);

					if (!$got_technique) {
						$errors[]	= t('battles.errors.needs_technique_before');
					}
				}

				if ($effect->renew_random_cooldown) {
					if (!sizeof($player->get_technique_locks())) {
						$errors[]	= t('battles.errors.needs_technique_cooldown');
					}
				}
			}
		} else {
			$errors[]	= t('battles.errors.invalid');
		}

		if ($player->battle_pvp_id) {
			if($battle->current_id != $player->id) {
				$errors[]	= t('battles.errors.not_my_turn');
			}
		}

		if ($player->{'has_' . $target_var . '_lock'}()) {
			$errors[]	= t('battles.errors.used_' . $target_var);
		}

		if (!sizeof($errors)) {
			if ($player->battle_pvp_id) {
				$_SESSION['pvp_used_' . $target_var]	= true;
			}

			$player->less_mana	+= $target->consume_mana;

			$chances	= explode(',', $target->effect_chances);
			$durations	= explode(',', $target->effect_duration);

			SharedStore::S('battle_used_' . $target_var . '_' . $player->id, true);

			foreach ($target->effects() as $key => $effect) {
				$player->add_ability_speciality_effect($player, $target_var, $effect, $chances[$key], $durations[$key], 'player');
				$enemy->add_ability_speciality_effect($player, $target_var, $effect, $chances[$key], $durations[$key], 'enemy');
			}

			if ($_POST['item'] == 'ability') {
				$player->add_ability_lock();
			} else {
				$player->add_speciality_lock();
			}

			$log_field	= $battle->player_id == $player->id ? 'player_effect_log' : 'enemy_effect_log';
			$tooltip_id	= 'bi-' . uniqid(uniqid(), true);
			$log		= @unserialize($battle->{$log_field});

			$battle->{$log_field}	= serialize(array_merge(is_array($log) ? $log : [], [
				t('battles.modifier_text', [
					'player'	=> $player->name,
					'item'		=> $target->description()->name,
					'tooltip'	=> $tooltip_id
				]) . '&nbsp;' . partial('shared/battle_ability_speciality', ['who' => $player, 'target' => $target, 'id' => $tooltip_id]) . '<br />'
			]));

			$battle->save();
			$player->save();

			if($effect->kill_with_one_hit){
				if($kill_with_one_hit){
					$_POST['item']	= 1722;
				}else{
					$_POST['item']	= 1723;
				}
				$this->attack(NULL, 'kill');
				return;
			}

			if ($is_technique_copy) {
				$_POST['item']	= $got_technique;
				$this->attack('copy', NULL);
				return;
			}
		} else {
			$this->json->messages	= $errors;
		}

		$this->_techniques_to_json($player);
		$this->_stats_to_json($player, $enemy, $battle, true, true);
	}

	function ping() {
		$this->as_json	= true;
		$player			= Player::get_instance();
		$battle			= $player->battle_pvp_id ? $player->battle_pvp() : $player->battle_npc();
		$enemy			= $player->battle_pvp_id ? $battle->enemy() : ($player->battle_npc_challenge ? $player->get_npc_challenge() : $player->get_npc());

		$this->_stats_to_json($player, $enemy, $battle);

		if ($player->battle_pvp_id) {
			$this->_techniques_to_json($player);
		}
	}

	private function _techniques_to_json($player) {
		$this->json->tooltips		= [];

		foreach($player->get_techniques() as $technique) {
			$this->json->tooltips[]	= [
				'item'		=> $technique->parent_id ? $technique->parent_id : $technique->item_id,
				'tooltip'	=> $technique->item()->tooltip(true)
			];
		}

		$this->json->tooltips[]	= [
			'item'		=> 'ability',
			'tooltip'	=> $player->ability()->tooltip($player)
		];

		$this->json->tooltips[]	= [
			'item'		=> 'speciality',
			'tooltip'	=> $player->speciality()->tooltip($player)
		];
	}

	private function _stats_to_json($p, $e, $battle, $action_was_made = false, $is_ability = false, $should_update_mana = false) {
		$is_pvp	= $p->battle_pvp_id;

		// extreme black magic for pvp~~~~~~ don't touch -->
		if ($p->battle_pvp_id) {
			$p->clear_fixed_effects('fixed');
			$e->clear_fixed_effects('fixed');

			$p->apply_battle_effects($e);
			$e->apply_battle_effects($p);
		}
		// <--
		if ($is_pvp) {
			$word_player	= $battle->player_id == $p->id ? 'player' : 'enemy';
			$word_enemy		= $battle->player_id == $p->id ? 'enemy' : 'player';

			if ($should_update_mana && !$is_ability) {
				$battle->{$word_player . '_mana'} = $p->for_mana();
				$battle->{$word_enemy . '_mana'} = $e->for_mana();
				$battle->save();
			}
		}

		$this->json->player				= new stdClass();
		$this->json->player->life		= $p->for_life();
		$this->json->player->life_max	= $p->for_life(true);

		if ($is_pvp) {
			if ($battle->current_id == $p->id) {
				$this->json->player->mana	= $p->for_mana();
			} else {
				$this->json->player->mana	= $battle->{$word_player . '_mana'};
			}
		} else {
			$this->json->player->mana	= $p->for_mana();
		}

		$this->json->player->real_mana	= $p->for_mana();
		$this->json->player->mana_max	= $p->for_mana(true);

		$status							= new stdClass();
		$status->atk					= $p->for_atk();
		$status->def					= $p->for_def();
		$status->crit					= $p->for_crit();
		$status->crit_inc				= $p->for_crit_inc();
		$status->abs					= $p->for_abs();
		$status->abs_inc				= $p->for_abs_inc();
		$status->prec					= $p->for_prec();
		$status->init					= $p->for_init();

		$this->json->player->status		= $status;

		$this->json->enemy				= new stdClass();
		$this->json->enemy->life		= $e->for_life();
		$this->json->enemy->life_max	= $e->for_life(true);

		if (!$is_pvp) {
			$this->json->enemy->mana	= $e->for_mana();
		} else {
			$this->json->enemy->mana	= $battle->{$word_enemy . '_mana'};
		}

		$this->json->enemy->real_mana	= $e->for_mana();
		$this->json->enemy->mana_max	= $e->for_mana(true);

		$status							= new stdClass();
		$status->atk					= $e->for_atk();
		$status->def					= $e->for_def();
		$status->crit					= $e->for_crit();
		$status->crit_inc				= $e->for_crit_inc();
		$status->abs					= $e->for_abs();
		$status->abs_inc				= $e->for_abs_inc();
		$status->prec					= $e->for_prec();
		$status->init					= $e->for_init();

		$this->json->enemy->status		= $status;
		$this->json->effects_roundup	= new stdClass();

		// Batalha acabou!
		if ($battle->finished_at) {
			$player_battle_stats	= PlayerBattleStat::find_first("player_id=".$p->id);
			if ($battle->battle_type_id == 5) {
				$player_ranked	= $p->ranked();
				$tier			= $player_ranked->tier();
			}

			// Verifica a conquista de liga pvp
			$p->achievement_check("battle_league_pvp");
			// Objetivo de Round
			$p->check_objectives("battle_league_pvp");

			if ($battle->battle_type_id == 7 || $battle->battle_type_id == 8) {
				$link		= make_url('guilds#dungeon');
				$link_text	= t('battles.links.dungeon');
			} elseif ($battle->battle_type_id == 3 && $p->challenge_id) {
				$link		= make_url('challenges#show/' . $p->challenge_id);
				$link_text	= t('battles.links.challenges');
			} elseif ($battle->battle_type_id == 6) {
				$link		= make_url('maps#preview');
				$link_text	= t('battles.links.maps_preview');
			} elseif ($battle->battle_type_id == 9 && isset($_SESSION['history_mode'])) {
				$link		= make_url('history_mode#show/' . $_SESSION['history_mode']);
				$link_text	= t('battles.links.history_mode');
				unset($_SESSION['history_mode']);
			} elseif ($battle->battle_type_id == 1) {
				$link		= make_url('battle_npcs');
				$link_text	= t('battles.links.battle_npcs');
			} elseif ($battle->battle_type_id == 2) {
				$link		= make_url('battle_npcs');
				$link_text	= t('battles.links.battle_pvp');
			} else {
				$link		= make_url('characters#status');
				$link_text	= t('battles.links.characters_status');
			}

			$this->json->redirect	= $link;

			// Variaveis dos drops
			$drop_message	= '<br />';
			$drop_message_e	= '<br />';

			// Carrega o último ganhador da batalha de animes
			$bonus_active	= false;
			$last_winner	= EventAnime::find_first("completed=1 ORDER BY id desc LIMIT 1");
			if ($last_winner) {
				if ($last_winner->anime_win_id == $p->character()->anime_id) {
					$bonus_active = true;
				}
			}

			// Carrega a batalha de anime atual
			$event_anime = EventAnime::find_first("completed=0");

			// Carrega o contador de batalhas
			$counters	= $p->battle_counters();

			if ($is_pvp) {
				// Carrega o stats de batalha entre os dois jogadores
				$stats	= PlayerBattlePvpLog::find_first('player_id=' . $p->id . ' AND enemy_id=' . $e->id);

				// Incrementa o total de batalhas PVP
				$counters->total_pvp_made++;

				// Define a quantidade de pontos a ser transferido da batalha de animes
				$eventAnimePts	= 10;

				// Não executa se for uma batalha de treino
				if ($battle->battle_type_id != 4) {
					// Remove um contador da vantagem de sem talentos
					if ($p->has_item(1715) && $p->no_talent == 1) {
						$item1715 = PlayerItem::find_first("player_id = {$p->id} AND item_id = 1715");
						if ($item1715->quantity <= 1) {
							$item1715->quantity = 0;
							$p->no_talent = 0;
						} else {
							$item1715->quantity--;
						}
						$item1715->save();
					}
				}
			} else {
				// Limpa as informações do NPC atual
				$player_stats = PlayerStat::find_first("player_id = " . $p->id);
				$player_stats->npc								= 0;
				$player_stats->npc_anime_id						= 0;
				$player_stats->npc_character_id					= 0;
				$player_stats->npc_challenge_character_id		= 0;
				$player_stats->npc_challenge_character_theme_id	= 0;
				$player_stats->npc_challenge_anime_id			= 0;
				$player_stats->npc								= 0;
				$player_stats->save();

				// Incrementa o total de batalhas NPC
				$counters->total_npc_made++;

				// Define a quantidade de pontos a ser transferido da batalha de animes
				$eventAnimePts	= 1;
			}

			// Salva as informações do contador de batalha do jogador
			$counters->save();

			// Faz a transição de pontos para a batalha de animes
				// Carrega a batalha de anime atual
				$event_anime = EventAnime::find_first("completed=0");
				if ($event_anime) {
					if ($event_anime->anime_a_id == $p->character()->anime_id) {
						if ($event_anime->points_a + $eventAnimePts > 2000) {
							$event_anime->points_a = 2000;
						} else {
							$event_anime->points_a += $eventAnimePts;
						}

						if ($event_anime->points_b - $eventAnimePts < 0) {
							$event_anime->points_b = 0;

							// Marca que o anime A ganhou
							$event_anime->anime_win_id = $p->character()->anime_id;
							$event_anime->finished_at = now(true);
						} else {
							$event_anime->points_b -= $eventAnimePts;
						}

						$event_anime->save();
					} elseif ($event_anime->anime_b_id == $p->character()->anime_id) {
						if ($event_anime->points_a - $eventAnimePts < 0) {
							$event_anime->points_a = 0;

							// Marca que o anime B ganhou
							$event_anime->anime_win_id = $p->character()->anime_id;
							$event_anime->finished_at = now(true);
						} else {
							$event_anime->points_a -= $eventAnimePts;
						}

						if ($event_anime->points_b + $eventAnimePts > 2000) {
							$event_anime->points_b = 2000;
						} else {
							$event_anime->points_b += $eventAnimePts;
						}

						$event_anime->save();
					}
				}
			// Faz a transição de pontos para a batalha de animes

			// Adiciona Felicidade em seu Mascote
			$activePet = $p->get_active_pet();
			if ($activePet) {
				// Adiciona 2 pontos de felicidade ao mascote equipado
				if ($activePet->happiness < 100) {
					if (($activePet->happiness + 2) >= 100) {
						$activePet->happiness = 100;
					} else {
						$activePet->happiness += 2;
					}
				}

				// Adiciona exp para o mascote porque esta ativo em luta.
				$petAddExp = percent(20, $e->battle_exp() * EXP_RATE);
				$activePet->exp += $petAddExp > 0 ? $petAddExp : 0;
				$activePet->save();

				// Verifica de o mascote pode evoluir
				$p->check_pet_level($activePet, true);

				// Conquista para verificar a quantidade de pet / hapiness
				$p->achievement_check("pets");
				$p->check_objectives('pets');
			}

			$p->battle_npc_id	= 0;
			$p->battle_pvp_id	= 0;

			$p->less_mana		= 0;
			$p->less_life		= 0;

			$extras				= $p->attributes();
			$currency_name		= t('currencies.' . $p->character_theme()->anime()->id);
			$effects			= $p->get_parsed_effects();

			// Clean up ability/speciality lock
			SharedStore::S('battle_used_ability_' . $p->id, false);
			SharedStore::S('battle_used_speciality_' . $p->id, false);

			// Variaveis para as missões de conta
			$fragment_drop  = false;
			$blood_drop 	= false;
			$sand_drop 		= false;
			$equip_drop 	= false;
			$page_drop		= false;
			$pet_drop 		= false;
			$wanted_dead 	= false;
			$treasure_still	= false;

			// Chances de drop
			if ($is_pvp) {	// Chance de drop para PvP
				$drop_chance_page		 = (10	* DROP_RATE);
				$drop_chance_fragment	 = (15	* DROP_RATE);
				$drop_chance_equipment	 = (10	* DROP_RATE) + ($bonus_active ? 10 : 0);	// +10% se o anime do jogador tiver ganhado o último evento de anime
				$drop_chance_pet 	 	 = (10	* DROP_RATE) + ($bonus_active ? 10 : 0);	// +10% se o anime do jogador tiver ganhado o último evento de anime
				$drop_areia 			 = (5	* DROP_RATE);
				$drop_sangue			 = (1	* DROP_RATE);
				$drop_event		 		 = (5	* DROP_RATE);
			} else {		// Chance de drop para NPC
				$drop_chance_page		 = (5	* DROP_RATE);
				$drop_chance_fragment	 = (7.5	* DROP_RATE);
				$drop_chance_equipment	 = (5	* DROP_RATE) + ($bonus_active ? 10 : 0);	// +10% se o anime do jogador tiver ganhado o último evento de anime
				$drop_chance_pet 	 	 = (5	* DROP_RATE) + ($bonus_active ? 10 : 0);	// +10% se o anime do jogador tiver ganhado o último evento de anime
				$drop_areia 			 = (2.5	* DROP_RATE);
				$drop_sangue			 = (0.5	* DROP_RATE);
				$drop_event		 		 = (2.5	* DROP_RATE);
			}

			// Realiza o sorteio e verificação dos drops
			if (($battle->won != $p->id && $battle->inactivity == 1) || $battle->battle_type_id == 4) {
				// Não dropa nada!
			} else {
				// Drop de Areia Estelar
				if (has_chance($drop_areia + $extras->sum_bonus_drop)) {
					$item_1719 = PlayerItem::find_first("player_id =". $p->id. " AND item_id = 1719");
					if ($item_1719) {
						$player_areia			= $p->get_item(1719);
						$player_areia->quantity += 1;
						$player_areia->save();
					} else {
						$player_areia	= new PlayerItem();
						$player_areia->item_id	= 1719;
						$player_areia->player_id	= $p->id;
						$player_areia->quantity = 1;
						$player_areia->save();
					}
					$drop_message	.= t('battles.finished.drop', [
						'item'	=> $player_areia->item()->description()->name
					]);

					// Mensagem Global
					global_message('hightlights.sand', TRUE, [ $p->name ]);

					// Verifica a conquista de areia - Conquista
					$p->achievement_check("sands");
					$p->check_objectives('sands');

					// Diz que ganhou a Areia
					$sand_drop  = true;
				}

				// Drop de Sangue de Deus
				if (has_chance($drop_sangue + $extras->sum_bonus_drop)) {
					$item_1720 = PlayerItem::find_first("player_id =". $p->id. " AND item_id=1720");
					if ($item_1720){
						$player_sangue			= $p->get_item(1720);
						$player_sangue->quantity += 1;
						$player_sangue->save();
					} else {
						$player_sangue	= new PlayerItem();
						$player_sangue->item_id	= 1720;
						$player_sangue->player_id	= $p->id;
						$player_sangue->quantity = 1;
						$player_sangue->save();
					}
					$drop_message	.= t('battles.finished.drop', [
						'item'	=> $player_sangue->item()->description()->name
					]);

					// Mensagem Global
					global_message('hightlights.blood', TRUE, [ $p->name ]);

					// Verifica a conquista de areia - Conquista
					$p->achievement_check("bloods");
					$p->check_objectives('bloods');

					// Diz que ganhou o Sangue
					$blood_drop  = true;
				}

				// Drop de Evento
				if (EVENT_ACTIVE && has_chance($drop_event + $extras->sum_bonus_drop)) {
					$item_event = PlayerItem::find_first("player_id =". $p->id. " AND item_id=" . EVENT_ITEM);
					if ($item_event) {
						$player_event				= $p->get_item(EVENT_ITEM);
						$player_event->quantity 	+= 1;
						$player_event->save();
					} else {
						$player_event				= new PlayerItem();
						$player_event->item_id		= EVENT_ITEM;
						$player_event->player_id	= $p->id;
						$player_event->quantity 	= 1;
						$player_event->save();
					}

					$drop_message	.= t('battles.finished.drop', [
						'item'	=> $player_event->item()->description()->name
					]);
				}

				// Drop de Pergaminhos de Grimorio
				if (has_chance($drop_chance_page + $effects['grimoire_find'] + $extras->sum_bonus_drop)){
					$grimoire_card  = Item::find_first('item_type_id=11', ['reorder' => 'RAND()']);
					if (!$p->has_item($grimoire_card->id)) {
						$player_grimoire_card				= new PlayerItem();
						$player_grimoire_card->item_id		= $grimoire_card->id;
						$player_grimoire_card->player_id	= $p->id;
						$player_grimoire_card->save();

						$drop_message	.= t('battles.finished.drop_grimoire_text', [
							'name' => $grimoire_card->description()->name
						]);
					}

					// Diz que ganhou a Pagina Perdida
					$page_drop  = true;
				}

				// Drop de Fragmento de Alma
				if (has_chance($drop_chance_fragment + $effects['fragment_find'] + $extras->sum_bonus_drop)) {
					$item_446 = PlayerItem::find_first("player_id =". $p->id. " AND item_id=446");
					$fragments = rand(1, 10);
					if ($item_446) {
						$player_fragment			= $p->get_item(446);
						$player_fragment->quantity	+= $fragments;
						$player_fragment->save();
					} else {
						$player_fragment	= new PlayerItem();
						$player_fragment->item_id	= 446;
						$player_fragment->player_id	= $p->id;
						$player_fragment->quantity	= $fragments;
						$player_fragment->save();
					}
					$drop_message	.= t('battles.finished.drop', [
						'item'	=> 'x' . $fragments . ' ' . $player_fragment->item()->description()->name
					]);

					// Verifica a conquista de fragmentos - Conquista
					$p->achievement_check("fragments");
					$p->check_objectives('fragments');

					// Diz que ganhou o Fragmento
					$fragment_drop  = true;
				}

				if (has_chance($drop_chance_equipment + $effects['item_find'] + $extras->sum_bonus_drop)) {
					$dropped	= Item::generate_equipment($p);
					if ($dropped) {
						$drop_message	.= t('battles.finished.drop_text', [
							'name'		=> t('slots.' . $p->character()->anime_id) . ' ' . $dropped->attributes()->name(),
							'rarity'	=> $dropped->rarity
						]);

						// Verifica a conquista de fragmentos - Conquista
						$p->achievement_check("equipment");
						$p->check_objectives('equipment');

						// Diz que ganhou a Equip
						$equip_drop  = true;
					}
				}

				if (has_chance($drop_chance_pet + $effects['pets_find'] + $extras->sum_bonus_drop)) {
					$pet	= Item::find_first('item_type_id=3 AND is_initial=1', ['reorder' => 'RAND()']);
					if (has_chance($pet->drop_chance + $effects['item_find'])) {
						if (!$p->has_item($pet->id)) {
							$player_pet				= new PlayerItem();
							$player_pet->item_id	= $pet->id;
							$player_pet->player_id	= $p->id;
							$player_pet->save();
							$drop_message	.= t('battles.finished.drop_pet_text', [
								'name'		=> $pet->description()->name,
								'rarity'	=> $pet->rarity
							]);

							// Verifica se você tem pets - Conquista
							$p->achievement_check("pets");
							$p->check_objectives('pets');

							// Diz que ganhou o Mascote
							$pet_drop  = true;
						}
					}
				}
				// Premiação para todos independente do resultado da batalha
			}

			// Missões de Conta
			$user_quests   = $p->account_quests();
			if ($user_quests) {
				foreach ($user_quests as $user_quest) {
					switch ($user_quest->type) {
						case "fragment":
							// Dropou Fragmento
							if ($fragment_drop && $p->character()->anime_id == $user_quest->anime_id) {
								$user_quest->total++;
							}
							break;
						case "blood":
							// Dropou Sangue de Deus
							if ($blood_drop && $p->character()->anime_id == $user_quest->anime_id) {
								$user_quest->total++;
							}
							break;
						case "sand":
							// Dropou uma Areia Estelar
							if ($sand_drop && $p->character()->anime_id == $user_quest->anime_id) {
								$user_quest->total++;
							}
							break;
						case "equip":
							// Dropou Fragmento
							if ($equip_drop && $p->character()->anime_id == $user_quest->anime_id) {
								$user_quest->total++;
							}
							break;
						case "page":
							// Dropou uma Página Perdida
							if ($page_drop && $p->character()->anime_id == $user_quest->anime_id) {
								$user_quest->total++;
							}
							break;
						case "pet":
							// Dropou um Mascote
							if ($pet_drop && $p->character()->anime_id == $user_quest->anime_id) {
								$user_quest->total++;
							}
							break;
					}
					$user_quest->save();
				}
			}
			// Missões de Conta

			// Não faz quando for batalha de treino.
			if ($battle->battle_type_id != 4) {
				// Verifica as missões diarias
				$player_quests_daily   = $p->daily_quests();
				if ($player_quests_daily) {
					foreach ($player_quests_daily as $player_quest_daily) {
						switch ($player_quest_daily->type) {
							case "battle":
								// Duelar PVP ou NPC de um anime
								if ($player_quest_daily->anime_id && !$player_quest_daily->character_id) {
									if ($player_quest_daily->anime_id == $e->character()->anime_id) {
										$player_quest_daily->total++;
									}
								// Duelar PVP ou NPC de um anime e com personagem
								} elseif ($player_quest_daily->anime_id && $player_quest_daily->character_id) {
									if ($player_quest_daily->character_id == $e->character_id) {
										$player_quest_daily->total++;
									}
								// Duelar com qualquer um
								} else {
									$player_quest_daily->total++;
								}
								break;
							case "battle_npc":
								if (!$is_pvp) {
									// Matar NPC de um anime
									if ($player_quest_daily->anime_id && !$player_quest_daily->character_id) {
										if ($player_quest_daily->anime_id == $e->character()->anime_id){
											$player_quest_daily->total++;
										}
									// Matar NPC de um anime e com personagem
									} elseif ($player_quest_daily->anime_id && $player_quest_daily->character_id) {
										if ($player_quest_daily->character_id == $e->character_id) {
											$player_quest_daily->total++;
										}
									// Matar qualquer NPC
									} else {
										$player_quest_daily->total++;
									}
								}
								break;
							case "battle_pvp":
								if ($is_pvp) {
									// Matar PVP de um anime
									if ($player_quest_daily->anime_id && !$player_quest_daily->character_id) {
										if ($player_quest_daily->anime_id == $e->character()->anime_id) {
											$player_quest_daily->total++;
										}
									// Matar PVP de um anime e com personagem
									} elseif ($player_quest_daily->anime_id && $player_quest_daily->character_id) {
										if ($player_quest_daily->character_id == $e->character_id) {
											$player_quest_daily->total++;
										}
									// Matar qualquer PVP
									} else {
										$player_quest_daily->total++;
									}
								}
								break;
						}
						$player_quest_daily->save();
					}
				}
			}

			// A batalha empatou
			if ($battle->draw) {
				// Por ser empate, recebe apenas 50% das recompemsas de experiencia e moedas
				$exp			= round($e->battle_exp() / 2);
				$currency		= round($e->battle_currency() / 2);

				$exp_extra		= percent($extras->exp_battle + ($bonus_active ? 10 : 0), $exp);
				$currency_extra	= percent($extras->currency_battle + ($bonus_active ? 10 : 0), $currency);

				$exp_extra		+= percent($effects['exp_reward_extra_percent'], $exp) + $effects['exp_reward_extra'];
				$currency_extra	+= percent($effects['currency_reward_extra_percent'], $currency) + $effects['currency_reward_extra'];

				// Não faz quando for batalha de treino.
				if ($battle->battle_type_id != 4) {
					if ($is_pvp) {
						if ($battle->battle_type_id == 5) {
							if ($player_ranked) {
								$player_ranked->points	+= $tier->points_draws;
								$player_ranked->draws++;
								$player_ranked->save();
							}
						}
						$p->draws_pvp++;
						$stats->draws++;

						// rank de combates diário, semanal e mensal
						$player_battle_stats->draws_pvp++;
						$player_battle_stats->draws_pvp_weekly++;
						$player_battle_stats->draws_pvp_monthly++;
					} else {
						$p->draws_npc++;

						// rank de combates diário, semanal e mensal
						$player_battle_stats->draws_npc++;
						$player_battle_stats->draws_npc_weekly++;
						$player_battle_stats->draws_npc_monthly++;
					}
				}

				$this->json->end_type	= 0;

				$p->exp			+= ($exp ? $exp : 0) + $exp_extra ;
				$p->currency	+= ($currency ? $currency : 0) + $currency_extra;

				$exp_text = ($exp + $exp_extra);
				if ($exp_extra) {
					$exp_text .= " (" . highamount($exp);
					if ($exp_extra)
						$exp_text .= " <span class=\"verde\">+ " . highamount($exp_extra) . "</span>";
					$exp_text .= ')';
				}

				$currency_text = ($currency + $currency_extra);
				if ($currency_extra) {
					$currency_text .= " (" . highamount($currency);
					if ($currency_extra)
						$currency_text .= " <span class=\"verde\">+ " . highamount($currency_extra) . "</span>";
					$currency_text .= ')';
				}

				$finished_message		= partial('shared/info', [
					'id'		=> 3,
					'title'		=> 'battles.finished.draw_title',
					'message'	=> t('battles.finished.draw_text', [
						'value'		=> $currency_text,
						'exp'		=> $exp_text,
						'link'		=> $link,
						'currency'	=> $currency_name
					]) . $drop_message . '<br /><br /><a href="' . $link . '" class="btn btn-primary close-button">' . $link_text . '</a>'
				]);
			} else {
				if ($battle->won == $p->id) {
					$exp			= $e->battle_exp(true);
					$currency		= $e->battle_currency(true);

					$dropped		= false;

					$exp_extra		= percent($extras->exp_battle + ($bonus_active ? 10 : 0), $exp);
					$currency_extra	= percent($extras->currency_battle + ($bonus_active ? 10 : 0), $currency);

					$exp_extra		+= percent($effects['exp_reward_extra_percent'], $exp) + $effects['exp_reward_extra'];
					$currency_extra	+= percent($effects['currency_reward_extra_percent'], $currency) + $effects['currency_reward_extra'];

					// adiciona as novas flags de como o jogador matou os jogadores
					if ($is_pvp && (
						isset($_SESSION['pvp_used_buff']) ||
						isset($_SESSION['pvp_used_ability']) ||
						isset($_SESSION['pvp_used_speciality'])
					)) {
						$addPlayerKill	= false;
						$player_kills	= new PlayerKill();
						if (isset($_SESSION['pvp_used_buff']) && !$_SESSION['pvp_used_buff']) {
							$addPlayerKill	= true;
							$player_kills->kills_wo_buff++;
						}

						if (isset($_SESSION['pvp_used_ability']) && !$_SESSION['pvp_used_ability']) {
							$addPlayerKill	= true;
							$player_kills->kills_wo_ability++;
						}

						if (isset($_SESSION['pvp_used_speciality']) && !$_SESSION['pvp_used_speciality']) {
							$addPlayerKill	= true;
							$player_kills->kills_wo_speciality++;
						}

						if ($addPlayerKill) {
							$player_kills->player_id = $p->id;
							$player_kills->enemy_id  = $e->id;
							$player_kills->save();
						}
					}

					// Verifica se o jogador matou um alvo dos procurados
					if ($battle->battle_type_id != 4 && $is_pvp) {
						// Verifica se o inimigo morto era um procurado
						$enemy_wanted = PlayerWanted::find_first("player_id = {$e->id} and death=0");
						if ($enemy_wanted) {
							switch ($enemy_wanted->type_death) {
								case 1:		$campo = "kills_with_crit";			break;
								case 2:		$campo = "kills_with_precision";	break;
								case 3:		$campo = "kills_with_stronger";		break;
								case 4:		$campo = "kills_with_slowness";		break;
								case 5:		$campo = "kills_with_confusion";	break;
								case 6:		$campo = "kills_with_bleeding";		break;
								case 7:		$campo = "kills_with_stun";			break;
								case 8:		$campo = "kills_wo_buff";			break;
								case 9:		$campo = "kills_wo_ability";		break;
								case 10:	$campo = "kills_wo_speciality";		break;
							}

							// Verifica se o procurado ficou afk
							if ($battle->won == $p->id && $battle->inactivity == 1) {
								// Adiciona a recompensa para o player
								$p->currency += $e->won_last_battle > 100 ? 100 * 250 : $e->won_last_battle * 250;

								// Marca o procurado como morto
								$enemy_wanted->death 		= 1;
								$enemy_wanted->enemy_id 	= $p->id;
								$enemy_wanted->finished_at  = now(true);
								$enemy_wanted->save();

								// Mensagem Global
								global_message('hightlights.wanteds', true, [
									$p->name,
									$e->name
								]);

								// Reseta o contador de vitorias do jogador inimigo
								$e->won_last_battle = 0;
								$e->save();

								// Seta uma flag para dizer que o jogador matou um procurado
								$wanted_dead = true;
							} elseif ($battle->won == $p->id && $battle->inactivity != 1) {
								$player_kill = PlayerKill::find_last("player_id = {$p->id} and enemy_id = {$e->id} and {$campo} = 1");
								if ($player_kill) {
									if (strtotime($player_kill->created_at) >= strtotime($enemy_wanted->created_at)) {
										// Adiciona a recompensa para o player
										$p->currency += $e->won_last_battle > 100 ? 100 * 250 : $e->won_last_battle * 250;
									}
								} else {
									// Adiciona a recompensa para o player
									$p->currency += $e->won_last_battle > 100 ? 100 * 125 : $e->won_last_battle * 125;
								}

								// Marca o Procurado como morto
								$enemy_wanted->death 		= 1;
								$enemy_wanted->enemy_id 	= $p->id;
								$enemy_wanted->finished_at  = now(true);
								$enemy_wanted->save();

								// Mensagem Global
								global_message('hightlights.wanteds', true, [
									$p->name,
									$e->name
								]);

								// Reseta o contador de vitorias do jogador inimigo
								$e->won_last_battle = 0;
								$e->save();

								// Seta uma flag para dizer que o jogador matou um procurado
								$wanted_dead = true;
							}
						}
					}

					// Missões de Conta
					$user_quests   = $p->account_quests();
					if ($user_quests) {
						foreach ($user_quests as $user_quest) {
							switch ($user_quest->type) {
								case "wanted":
									// Matou um Procurado
									if ($wanted_dead && $p->character()->anime_id == $user_quest->anime_id) {
										$user_quest->total++;
									}
									break;
							}
							$user_quest->save();
						}
					}

					// Não faz quando for batalha de treino.
					if ($battle->battle_type_id != 4) {
						// Missões Organização Semanais
						$guild_quests_daily   = $p->guild_daily_quests();
						if ($guild_quests_daily && $is_pvp) {
							foreach ($guild_quests_daily as $guild_quest_daily) {
								switch ($guild_quest_daily->type) {
									case "kill_g":
										// Derrotar jogadores de uma determinada Organização
										if ($guild_quest_daily->guild_enemy_id && !$guild_quest_daily->enemy_id) {
											if ($guild_quest_daily->guild_enemy_id == $e->guild_id) {
												$guild_quest_daily->total++;
											}
										}
										break;
									case "kill_j_g":
										// Derrotar um jogador especifico de uma determinada Organização
										if ($guild_quest_daily->guild_enemy_id && $guild_quest_daily->enemy_id) {
											if ($guild_quest_daily->enemy_id == $e->id && $guild_quest_daily->guild_enemy_id == $e->guild_id) {
												$guild_quest_daily->total++;
											}
										}
										break;
									case "still_g":
										// Roubar um tesouro de uma determinada Organização
										if ($guild_quest_daily->guild_enemy_id && !$guild_quest_daily->enemy_id) {
											if ($e->treasure_atual > 0 && $guild_quest_daily->guild_enemy_id == $e->guild_id) {
												$guild_quest_daily->total++;
											}
										}
										break;
									case "still_j_g":
										// Roubar um tesouro de um Jogado Especifico em uma determinada Organização
										if ($guild_quest_daily->guild_enemy_id && $guild_quest_daily->enemy_id) {
											if ($e->treasure_atual > 0 && $guild_quest_daily->enemy_id == $e->id && $guild_quest_daily->guild_enemy_id == $e->guild_id) {
												$guild_quest_daily->total++;
											}
										}
										break;
									case "kill_a_g":
										// Derrotar jogadores de qualquer organização
										if (!$guild_quest_daily->guild_enemy_id && !$guild_quest_daily->enemy_id) {
											if ($e->guild_id && $p->guild_id != $e->guild_id) {
												$guild_quest_daily->total++;
											}
										}
										break;
									case "still_a_g":
										// Roubar jogadores de qualquer organização
										if (!$guild_quest_daily->guild_enemy_id && !$guild_quest_daily->enemy_id) {
											if ($e->guild_id && $e->treasure_atual > 0 && $p->guild_id != $e->guild_id) {
												$guild_quest_daily->total++;
											}
										}
										break;
								}
								$guild_quest_daily->save();
							}
						}

						if ($is_pvp) {
							if ($p->guild_id != $e->guild_id && $p->guild_id && $e->guild_id) {
								if ($e->treasure_atual > 0) {
									$p->treasure_atual++;
									$p->save();

									$e->treasure_atual--;
									$e->save();

									$drop_message	.= t('battles.finished.treasure');

									// Verifica se você ganhou treasure - Conquista
									$p->achievement_check("treasure");
									$p->check_objectives('treasure');

									// Seta como roubado um tesouro
									$treasure_still = true;
								}

							}

							// Missões de Conta
							$user_quests   = $p->account_quests();
							if ($user_quests) {
								foreach ($user_quests as $user_quest) {
									switch ($user_quest->type) {
										case "treasure":
											if ($treasure_still && $p->character()->anime_id == $user_quest->anime_id) {
												$user_quest->total++;
											}
											break;
									}
									$user_quest->save();
								}
							}
						}

						if ($p->pvp_quest_id && $is_pvp) {
							$player_quest   = $p->player_pvp_quest($p->pvp_quest_id);
							if ($e->level >= $p->level) {
								$player_quest->req_same_level++;
							}

							if ($e->level < $p->level) {
								$player_quest->req_low_level++;
							}

							if (!$_SESSION['pvp_used_buff']) {
								$player_quest->req_kill_wo_buff++;
							}

							if (!$_SESSION['pvp_used_ability']) {
								$player_quest->req_kill_wo_ability++;
							}

							if (!$_SESSION['pvp_used_speciality']) {
								$player_quest->req_kill_wo_speciality++;
							}
							$player_quest->save();
						}

						if ($is_pvp) {
							if ($battle->battle_type_id == 5) {
								if ($player_ranked) {
									$player_ranked->points	+= $tier->points_win;
									$player_ranked->wins++;
									$player_ranked->save();
								}

								// Verifica a conquista de liga pvp
								$p->achievement_check("battle_league_pvp");
								$p->check_objectives('battle_league_pvp');
							}

							$stats->wins++;
							$p->wins_pvp++;
							$p->won_last_battle++;

							// rank de combates diário, semanal e mensal
							$player_battle_stats->victory_pvp++;
							$player_battle_stats->victory_pvp_weekly++;
							$player_battle_stats->victory_pvp_monthly++;

							// Adiciona a cabeça do jogador nos procurados.
							if ($p->won_last_battle > 9) {
								$wanted = PlayerWanted::find_first("player_id = {$p->id} and death = 0");
								if (!$wanted) {
									$wanted_new				= new PlayerWanted();
									$wanted_new->player_id	= $p->id;
									$wanted_new->type_death	= rand(1, 10);
									$wanted_new->save();
								}
							}

							// Adiciona o contador da batalha especifica para a conquista
							$achievement_stats = PlayerAchievementStat::find_first("player_id=".$p->id." AND anime_id=".$e->character()->anime_id." AND character_id=".$e->character_id." AND faction_id=".$e->faction_id);
							if (!$achievement_stats) {
								$player_achievement_stats = new PlayerAchievementStat();
								$player_achievement_stats->player_id 	= $p->id;
								$player_achievement_stats->anime_id 	= $e->character()->anime_id;
								$player_achievement_stats->faction_id 	= $e->faction_id;
								$player_achievement_stats->character_id = $e->character_id;
								$player_achievement_stats->quantity++;
								$player_achievement_stats->save();
							} else {
								$achievement_stats->quantity++;
								$achievement_stats->save();
							}

							// Verifica se você tem batalhas - Conquista
							$p->achievement_check("battle_pvp");
							$p->check_objectives('battle_pvp');

							// Verifica se você tem batalhas - Conquista
							$p->achievement_check("wanted");
							$p->check_objectives('wanted');

						} else {
							// Premiação do NPC de Mapa
							if ($battle->battle_type_id == 6) {
								$rewards = MapReward::find_first("map_id = {$p->map_id} and is_npc = 1");
								if ($rewards) {
									$rand 		= rand(1, 100);
									if ($rand <= $rewards->chance) {
										$user = $p->user();
										// Prêmios ( CHARACTERS )
										if ($rewards->character_id && !$user->is_character_bought($rewards->character_id)) {
											$reward_character				= new UserCharacter();
											$reward_character->user_id		= $p->user_id;
											$reward_character->character_id	= $rewards->character_id;
											$reward_character->was_reward	= 1;
											$reward_character->save();

											$drop_message	.= t('map.character_map') . ' <span class="azul_claro">'. Character::find($rewards->character_id)->description()->name .'</span>';

											// verifica se desbloqueou novo personagem - conquista
											$p->achievement_check("character");
											$p->check_objectives('character');
										}

										// Prêmios ( THEME )
										if ($rewards->character_theme_id && !$user->is_theme_bought($rewards->character_theme_id)) {
											$reward_theme						= new UserCharacterTheme();
											$reward_theme->user_id				= $p->user_id;
											$reward_theme->character_theme_id	= $rewards->character_theme_id;
											$reward_theme->was_reward			= 1;
											$reward_theme->save();

											$drop_message	.= t('map.character_theme_map') . ' <span class="azul_claro">'. CharacterTheme::find($rewards->character_theme_id)->description()->name .'</span>';
										}
									}
								}
							}

							// Premiação do NPC de Mapa
							if ($e->specific_id) {
								$npc_user						= new UserHistoryModeNpc();
								$npc_user->user_id				= $p->user_id;
								$npc_user->player_id			= $p->id;
								$npc_user->history_mode_npc_id	= $e->specific_id;
								$npc_user->save();

								$npc_subgroup	= HistoryModeNpc::find($e->specific_id)->subgroup();

								$subgroup_complete = UserHistoryModeSubgroup::find('history_mode_subgroup_id='. $npc_subgroup->id .' AND user_id='. $p->user_id);

								if ($npc_subgroup->completed($p)) {

									if($subgroup_complete){
										//Level da Conta ( Modo Aventura )
										$user = $p->user();
										$user->exp	+= (25 * sizeof($npc_subgroup->npcs($p)));
										$user->save();
									}else{
										//Level da Conta ( Modo Aventura )
										$user = $p->user();
										$user->exp	+= (25 * sizeof($npc_subgroup->npcs($p)));
										$user->save();

										// Trava de Prêmio por Facção
										$user_subgroup									= new UserHistoryModeSubgroup();
										$user_subgroup->user_id 						= $p->user_id;
										$user_subgroup->history_mode_subgroup_id 		= $npc_subgroup->id;
										$user_subgroup->complete 						= 1;
										$user_subgroup->save();

										if ($npc_subgroup->reward_currency) {
											$p->earn($npc_subgroup->reward_currency);
										}

										if ($npc_subgroup->reward_exp) {
											$p->exp	+= $npc_subgroup->reward_exp;
										}

										if ($npc_subgroup->reward_headline_id) {
											$reward_headline				= new UserHeadline();
											$reward_headline->user_id		= $p->user_id;
											$reward_headline->headline_id	= $npc_subgroup->reward_headline_id;
											$reward_headline->save();
										}

										if ($npc_subgroup->reward_random_equipment_chance && has_chance($npc_subgroup->reward_random_equipment_chance + $effects['item_find'])) {
											Item::generate_equipment($p);
										}

										if ($npc_subgroup->reward_pet_chance && has_chance($npc_subgroup->reward_pet_chance + $effects['pets_find'])) {
											if ($npc_subgroup->reward_item_id) {
												$npc_pet	= Item::find($npc_subgroup->reward_item_id);
											} else {
												$npc_pet	= Item::find_first('item_type_id=3 and is_initial=1', ['reorder' => 'RAND()']);
											}

											if (!$p->has_item($npc_pet->id)) {
												$player_pet				= new PlayerItem();
												$player_pet->item_id	= $npc_pet->id;
												$player_pet->player_id	= $p->id;
												$player_pet->save();
											}
										}

										if ($npc_subgroup->reward_item_chance && has_chance($npc_subgroup->reward_item_chance + $effects['item_find'])) {
											// Quando o item é comida
											if ($npc_subgroup->reward_quantity) {
												$player_item_exist			= PlayerItem::find_first("item_id=".$npc_subgroup->reward_item_id." AND player_id=". $p->id);
												if (!$player_item_exist) {
													$player_item			= new PlayerItem();
													$player_item->item_id	= $npc_subgroup->reward_item_id;
													$player_item->quantity	= $npc_subgroup->reward_quantity;
													$player_item->player_id	= $p->id;
													$player_item->save();
												} else {
													$player_item_exist->quantity += $npc_subgroup->reward_quantity;
													$player_item_exist->save();
												}
											} else {
												// Quando o item é golpe
												$reward_item_instance	= Item::find_first($npc_subgroup->reward_item_id);

												$player_item			= new PlayerItem();
												$player_item->item_id	= $reward_item_instance->id;
												$player_item->player_id	= $p->id;

												if ($reward_item_instance->item_type_id == 1) {
													$player_item->removed	= 1;
												}

												$player_item->save();

												//Adiciona o Item na Tabela de Usuário para que todos personagens da conta do cara ganhe um dia.
												$user_player_item				= new UserPlayerItem();
												$user_player_item->item_id		= $reward_item_instance->id;
												$user_player_item->user_id		= $p->user_id;
												$user_player_item->save();
											}

										}

										if ($npc_subgroup->reward_character_id &&!$user->is_character_bought($npc_subgroup->reward_character_id)) {
											$reward_character				= new UserCharacter();
											$reward_character->user_id		= $p->user_id;
											$reward_character->character_id	= $npc_subgroup->reward_character_id;
											$reward_character->was_reward	= 1;
											$reward_character->save();

											// verifica se desbloqueou novo personagem - conquista
											$p->achievement_check("character");
											$p->check_objectives('character');
										}

										if ($npc_subgroup->reward_character_theme_id &&!$user->is_theme_bought($npc_subgroup->reward_character_theme_id)) {
											$reward_theme						= new UserCharacterTheme();
											$reward_theme->user_id				= $p->user_id;
											$reward_theme->character_theme_id	= $npc_subgroup->reward_character_theme_id;
											$reward_theme->was_reward			= 1;
											$reward_theme->save();

											// verifica se desbloqueou novo personagem - conquista
											$p->achievement_check("character_theme");
											$p->check_objectives('character_theme');
										}
										// Verifica se finalizou uma modo historia - Conquista
										$p->achievement_check("history_mode");
										$p->check_objectives('history_mode');
									}
								}
							}

							// Não dá score ao vencer o npc da arena
							if ($battle->battle_type_id <> 3) {
								$p->wins_npc++;
							}

							// rank de combates diário, semanal e mensal
							$player_battle_stats->victory_npc++;
							$player_battle_stats->victory_npc_weekly++;
							$player_battle_stats->victory_npc_monthly++;

							// Verifica se você tem pets
							$p->achievement_check("battle_npc");
							$p->check_objectives('battle_npc');
						}
					}

					if (!$is_pvp && ($battle->battle_type_id == 7 || $battle->battle_type_id == 8)) {
						if ($battle->battle_type_id == 7) {
							$session = new GuildMapObjectSession();
							$session->down								= 1;
							$session->guild_id					= $p->guild_id;
							$session->guild_map_object_id		= $e->guild_map_object_id;
							$session->guild_accepted_event_id	= $p->guild_accepted_event_id;
							$session->player_id							= $p->id;
						} else {
							$session		= GuildMapObjectSession::find_first('player_id=0 AND guild_accepted_event_id=' . $p->guild_accepted_event_id . ' AND guild_id=' . $p->guild_id . ' AND guild_map_object_id=' . $e->guild_map_object_id);
							$session->down	= 1;
						}
						$session->save();

						$p->guild()->check_event_finished($p);
					}

					// Não faz quando for batalha de treino.
					if ($battle->battle_type_id != 4) {
						$this->json->end_type	= 1;

						$p->exp			+= $exp + $exp_extra;
						$p->currency	+= $currency + $currency_extra;


						$exp_text = ($exp + $exp_extra);
						if ($exp_extra) {
							$exp_text .= " (" . highamount($exp);
							if ($exp_extra)
								$exp_text .= " <span class=\"verde\">+ " . highamount($exp_extra) . "</span>";
							$exp_text .= ')';
						}
						$currency_text = ($currency + $currency_extra);
						if ($currency_extra) {
							$currency_text .= " (" . highamount($currency);
							if ($currency_extra)
								$currency_text .= " <span class=\"verde\">+ " . highamount($currency_extra) . "</span>";
							$currency_text .= ')';
						}
						$finished_message		= partial('shared/info', [
							'id'		=> 5,
							'title'		=> 'battles.finished.win_title',
							'message'	=> t('battles.finished.win_text', [
								'value'		=> $currency_text,
								'exp'		=> $exp_text,
								'link'		=> $link,
								'currency'	=> $currency_name
							]) . $drop_message . '<br /><br /><a href="' . $link . '" class="btn btn-primary close-button">' . $link_text . '</a>'
						]);
					} else {
						$p->currency	+= 0;
						$p->exp			+= 0;

						$this->json->end_type	= 1;
						$finished_message		= partial('shared/info', [
							'id'		=> 5,
							'title'		=> 'battles.finished.win_title',
							'message'	=> t('battles.finished.win_text', [
								'value'		=> "0",
								'exp'		=> "0",
								'link'		=> $link,
								'currency'	=> $currency_name
							]) . $drop_message . '<br /><br /><a href="' . $link . '" class="btn btn-primary close-button">' . $link_text . '</a>'
						]);
					}
				} else {
					$drop_message_e = "";

					$exp			= $e->battle_exp();
					$currency		= $e->battle_currency();

					if (in_array($battle->battle_type_id, [1, 3, 6, 7, 8, 9])) {
						$exp		/= 2;
						$currency	/= 2;
					}

					$exp_extra		= percent($extras->exp_battle + ($bonus_active ? 10 : 0), $exp);
					$currency_extra	= percent($extras->currency_battle + ($bonus_active ? 10 : 0), $currency);

					$exp_extra		+= percent($effects['exp_reward_extra_percent'], $exp) + $effects['exp_reward_extra'];
					$currency_extra	+= percent($effects['currency_reward_extra_percent'], $currency) + $effects['currency_reward_extra'];

					// Não faz quando for batalha de treino.
					if ($battle->battle_type_id != 4){
						if ($is_pvp) {
							if ($p->guild_id != $e->guild_id && $p->guild_id && $e->guild_id){
								if ($p->treasure_atual > 0) {
									$drop_message_e	.= t('battles.finished.treasure2');
								}
							}
						}

						if ($is_pvp) {
							if ($battle->battle_type_id == 5) {
								if ($player_ranked) {
									$player_ranked->points	-= $tier->points_loose;
									$player_ranked->losses++;
									$player_ranked->save();
								}
							}

							$stats->losses++;
							$p->losses_pvp++;

							// rank de combates diário, semanal e mensal
							$player_battle_stats->looses_pvp++;
							$player_battle_stats->looses_pvp_weekly++;
							$player_battle_stats->looses_pvp_monthly++;

							// Faz alguma coisa sobre os procurados
							$enemy_player_wanted = PlayerWanted::find_first("player_id=".$p->id." AND death=0");
							if (!$enemy_player_wanted) {
								$p->won_last_battle	= 0;
							}
						} else {
							$p->losses_npc++;

							// rank de combates diário, semanal e mensal
							$player_battle_stats->looses_npc++;
							$player_battle_stats->looses_npc_weekly++;
							$player_battle_stats->looses_npc_monthly++;
						}
					}

					// Não da premiação para o jogador que inativa
					if (
						($battle->won != $p->id && $battle->inactivity == 1) ||
						$battle->battle_type_id == 4 || $battle->battle_type_id == 9
					) {
						// $p->hospital	= 1;

						$p->currency	+= 0;
						$p->exp			+= 0;

						$finished_message		= partial('shared/info', [
							'id'		=> 3,
							'title'		=> 'battles.finished.loss_title',
							'message'	=> t('battles.finished.loss_text', [
								'value'		=> "0",
								'exp'		=> "0",
								'link'		=> $link,
								'currency'	=> $currency_name
							]) . $drop_message_e . '<br /><br /><a href="' . $link . '" class="btn btn-primary close-button">' . $link_text . '</a>'
						]);
					} else {
						// $p->hospital	= 1;

						// Não faz quando for batalha de treino.
						$p->exp			+= round($exp + $exp_extra);
						$p->currency	+= round($currency + $currency_extra);

						$exp_text = ($exp + $exp_extra);
						if ($exp_extra) {
							$exp_text .= " (" . highamount($exp);
							if ($exp_extra)
								$exp_text .= " <span class=\"verde\">+ " . highamount($exp_extra) . "</span>";
							$exp_text .= ')';
						}
						$currency_text = ($currency + $currency_extra);
						if ($currency_extra) {
							$currency_text .= " (" . highamount($currency);
							if ($currency_extra)
								$currency_text .= " <span class=\"verde\">+ " . highamount($currency_extra) . "</span>";
							$currency_text .= ')';
						}
						$finished_message		= partial('shared/info', [
							'id'		=> 3,
							'title'		=> 'battles.finished.loss_title',
							'message'	=> t('battles.finished.loss_text', [
								'value'		=> $currency_text,
								'exp'		=> $exp_text,
								'link'		=> $link,
								'currency'	=> $currency_name
							]) . $drop_message_e . $drop_message . '<br /><br /><a href="' . $link . '" class="btn btn-primary close-button">' . $link_text . '</a>'
						]);
					}
				}
			}

			/** Sistema de ligas */
			if ($battle->battle_type_id == 5) {
				// Recarrega o rank de player na season
				$player_ranked = $p->ranked();
				if ($player_ranked) {
					$player_ranked->update();
				}
			}
			/* / Sistema de liga */

			// Não faz quando for batalha de treino ou perder por inatividade.
			if (($battle->won != $p->id && $battle->inactivity == 1) || $battle->battle_type_id == 4){
			} else {
				//Level da Conta ( Batalha NPC e PVP )
				$user = $p->user();

				if ($is_pvp) {
					$user->exp	+= percent(20, $exp + $exp_extra);
				} else {
					$user->exp	+= percent(10, $exp + $exp_extra);
				}
				$user->save();
			}

			# Corrigi o no_talent
			if ($p->no_talent == 2) {
				$p->no_talent = 0;
			}

			$p->clear_ability_lock();
			$p->clear_speciality_lock();
			$p->clear_technique_locks();
			$p->clear_effects();
			$p->save();

			// Salva o rank diario, semanal e mensal de combate
			$player_battle_stats->name 					= $p->name;
			$player_battle_stats->character_id 			= $p->character_id;
			$player_battle_stats->character_theme_id 	= $p->character_theme_id;
			$player_battle_stats->faction_id 			= $p->faction_id;
			$player_battle_stats->graduation_id 		= $p->graduation_id;
			$player_battle_stats->anime_id 				= $p->character()->anime_id;
			$player_battle_stats->save();

			// Checa o dinheiro do player - Conquista
			$p->achievement_check("currency");
			$p->check_objectives('currency');

			$this->json->finished	= $finished_message;

			if (!$is_pvp) {
				$p->save_npc([]);
			} else {
				$stats->save();
			}
		}

		$p_effects		= [];
		$e_effects		= [];
		$player_locks	= [];
		$enemy_locks	= [];

		foreach (['p_effects' => 'p', 'e_effects' => 'e'] as $container => $target) {
			$who		= $$target;
			$effects	=& $$container;
			$secrets	= [];
			$exclude	= [];
			$parsed		= $who->get_parsed_effects(true);

			foreach (['player', 'enemy'] as $effect_direction) {
				$getEffects = $who->get_effects();
				if (!$getEffects) {
					$content	= json_encode([
						'getEffects'	=> $getEffects
					]);
					Recordset::insert('log', [
						'user_id'	=> 0,
						'player_id'	=> 0,
						'content'	=> $content
					]);
				} else {
					$this->json->{$effect_direction . '_' . $container . '_' . $target} = $getEffects;

					foreach ($getEffects[$effect_direction] as $key => $effect_list) {
						foreach ($effect_list as $effect_id => $effect_data) {
							if ($effect_data->secret && !$effect_data->revealed) {
								$item		= Item::find($effect_data->soruce_id);

								$player_id	= is_numeric($$target->id) ? $$target->id : '0';
								if (!$item) {
									$content	= json_encode([
										'effect_data'	=> $effect_data,
										'item'			=> $item
									]);
									Recordset::insert('log', [
										'user_id'	=> 0,
										'player_id'	=> $player_id,
										'content'	=> $content
									]);
								} else {
									$itemEffects	= $item->effects();
									$content	= json_encode([
										'itemEffects'	=> $itemEffects,
										'effect_data'	=> $effect_data,
										'item'			=> $item
									]);
									Recordset::insert('log', [
										'user_id'	=> 0,
										'player_id'	=> 0,
										'content'	=> $content
									]);
									if ($itemEffects && isset($itemEffects->effect_direction)) {
										if ($itemEffects->effect_direction == 'buff') {
											if ($effect_data->direction != 'enemy') {
												$condition = $who->id != Player::get_instance()->id;
											} else {
												$condition = $who->id == Player::get_instance()->id;
											}
										} else {
											if ($effect_data->direction != 'enemy') {
												$condition = $who->id != Player::get_instance()->id;
											} else {
												$condition = $who->id == Player::get_instance()->id;
											}
										}

										if ($condition) {
											$secrets[]	= $effect_data->id;
											continue;
										}
									}
								}
							}

							$effects[]	= $effect_data;
						}
					}
				}
			}

			foreach ($secrets as $secret) {
				$effect	= ItemEffect::find_first($secret, ['cache' => true])->as_array();

				foreach ($effect as $key => $value) {
					if ((int)$value != 0)
						unset($parsed[$key]);
				}
			}

			$this->json->effects_roundup->{$target}	= $parsed;
		}

		foreach (['player_locks' => $p, 'enemy_locks' => $e] as $lock_target => $instance) {
			$lock_target_final	=& $$lock_target;

			if ($lock_target != 'enemy_locks') {
				foreach ($instance->get_technique_locks() as $key => $lock) {
					$l				= new stdClass();
					$l->remaining	= $lock['turns'];
					$l->id			= $key;
					$l->type		= 'item';

					$lock_target_final[]		= $l;
				}
			}

			if ($lock = $instance->has_ability_lock()) {
				if ($instance->has_visible_effect($instance->ability()->effects()[0]->id) || $instance->id == Player::get_instance()->id) {
					$l				= new stdClass();
					$l->remaining	= $lock['duration'];
					$l->type		= 'ability';

					$lock_target_final[]		= $l;
				}
			}

			if ($lock = $instance->has_speciality_lock()) {
				if ($instance->has_visible_effect($instance->speciality()->effects()[0]->id) || $instance->id == Player::get_instance()->id) {
					$l				= new stdClass();
					$l->remaining	= $lock['duration'];
					$l->type		= 'speciality';

					$lock_target_final[]		= $l;
				}
			}
		}

		if (!isset($_SESSION['pvp_time_reduced']))
			$_SESSION['pvp_time_reduced']	= 0;

		if ($is_pvp) {
			$this->json->attack_text	= $battle->current_id == $p->id ? t('battles.mine_action', [
				'turn' => $battle->current_turn
			]) : t('battles.enemy_action', [
				'turn' => $battle->current_turn
			]);
			$this->json->my_turn		= $battle->current_id == $p->id;

			if (!isset($_SESSION['pvp_time_reduced']))
				$_SESSION['pvp_time_reduced']	= 0;

			$battle_now			= BattlePVP::find_first($battle->id);
			$current			= now();
			$future				= strtotime('+' . (PVP_TURN_TIME - $_SESSION['pvp_time_reduced']) . ' seconds', strtotime($battle_now->last_atk));
			$timer_diff			= get_time_difference($current, $future);
			$this->json->timer	= [
				'minutes'	=> $timer_diff['minutes'] < 0 ? 0 : $timer_diff['minutes'],
				'seconds'	=> $timer_diff['seconds'] < 0 ? 0 : $timer_diff['seconds']
			];

			// if ($action_was_made) {
			// 	if ($timer_diff['minutes'] < 1 && $timer_diff['seconds'] < 30) {
			// 		if ($_SESSION['pvp_time_reduced'] < 60)
			// 			$_SESSION['pvp_time_reduced']	+= 30;
			// 	}
			// }

			if ($current > $future && !$battle_now->finished_at) {
				$battle->finished_at	= now(true);
				$battle->won			= $battle_now->current_id == $p->id ? $e->id : $p->id;
				$battle->inactivity 	= 1;
				$battle->save();
			}
		} else {
			$this->json->attack_text	= t('battles.mine_action', [
				'turn' => $battle->current_turn
			]);
		}

		$this->json->log				= $battle->get_log();
		$this->json->player->effects	= $p_effects;
		$this->json->player->locks		= $player_locks;
		$this->json->enemy->locks		= $enemy_locks;
		$this->json->enemy->effects		= $e_effects;
		$this->json->current_turn		= $battle->current_turn;

		// if (isset($_GET['initial']) || !$is_pvp)
		// 	$this->json->enemy->update_existent_locks		= TRUE;
		// else {
		// 	// So quando a trigger fizer a ação que não zera os dados
		// 	if ($is_pvp && $battle->should_process) {
		// 		$this->json->enemy->locks					= [];
		// 		$this->json->enemy->effects					= [];
		// 		$this->json->enemy->update_existent_locks	= FALSE;
		// 	} elseif($is_pvp && !$battle->should_process)
		// 		$this->json->enemy->update_existent_locks	= TRUE;
		// }
	}
}
