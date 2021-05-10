<?php
class BattleNpcsController extends Controller
{
	use BattleSharedMethods;

	public function index()
	{
		$player		= Player::get_instance();
		$animes		= Anime::find($_SESSION['universal'] ? '1 = 1' : 'active = 1', [
			'cache'		=> true,
			'reorder'	=> 'id asc'
		]);

		// Nova regra de npc
		$player_stats = PlayerStat::find_first('player_id = ' . $player->id);
		if ($player_stats->npc_anime_id && $player_stats->npc_character_id) {
			$npc	= new NpcInstance($player, $player_stats->npc_anime_id, [], NULL, NULL, NULL, NULL, $player_stats->npc_character_id, NULL);
		} else {
			$npc	= new NpcInstance($player);

			// Salva o NPC atual no player
			$anime = Character::find_first("id=" . $npc->character_id);
			$player_stats->npc_anime_id 	= $anime->anime_id;
			$player_stats->npc_character_id = $npc->character_id;
			$player_stats->save();
		}

		// Cleanups -->
			SharedStore::S('last_battle_item_of_' . $player->id, 0);
			SharedStore::S('last_battle_npc_item_of_' . $player->id, 0);

			$player->clear_ability_lock();
			$player->clear_speciality_lock();
			$player->clear_technique_locks();
			$player->clear_effects();
			$player->save_npc($npc);
		// <--

		$player->refresh_talents();

		$this->assign('player',					$player);
		$this->assign('npc',					$npc);
		$this->assign("animes",					$animes);
		$this->assign("player_battle_stats",	PlayerBattleStat::find_first("player_id=".$player->id));

		// $this->assign('max_npc_count',		$this->npc_limit + $player->attributes()->sum_bonus_daily_npc);
		// $this->assign('current_npc_count',	$player->battle_counters()->current_npc_made);
	}
	function change_oponent() {
		$this->as_json			= true;
		$this->json->success	= false;

		$player					= Player::get_instance();
		$user					= User::get_instance();
		$errors					= [];

		if(!isset($_POST['character_id']) || (isset($_POST['character_id']) && !is_numeric($_POST['character_id']))) {
			$errors[]	= t('battles.errors.invalid');
		} else {
			if ($user->credits < 1) {
				$errors[]	= t("battles.errors.not_enough_credits");
			}
		}
		if (!sizeof($errors)) {
			$character = Character::find_first("id=" . $_POST['character_id']);

			// Gasta Créditos do Jogador.
			$user->spend(1);

			// Troca o NPC Salvo do Cara.
			$npc = PlayerStat::find_first("player_id=".$player->id);
			$npc->npc_anime_id = $character->anime_id;
			$npc->npc_character_id = $character->id;
			$npc->save();

			$this->json->success	= true;
		} else {
			$this->json->messages	= $errors;
		}
	}
	function accept() {
		$this->as_json			= true;
		$this->json->success	= false;

		$player					= Player::get_instance();
		$npc					= $player->get_npc();
		$errors					= [];

		// Limite Npc Diário
		$counters				= $player->battle_counters();
		$limit_npc 				= $counters->current_npc_made;

		if (!is_a($npc, 'NpcInstance')) {
			$errors[]	= t('battles.npc.errors.no_instance');
		}

		if ($player->is_pvp_queued) {
			$errors[]	= t('battles.npc.errors.pvp_queue');
		}

		if ($limit_npc >= NPC_DAILY_LIMIT && $_POST['type'] != 6) {
			$errors[]	= t('battles.npc.errors.limit');
		}

		if ($player->at_low_stat()) {
			$errors[]	= t('battles.errors.low_stat');
		}

		if ($player->for_stamina() < NPC_COST) {
			$errors[]	= t('battles.errors.no_stamina');
		}
		if ($_POST['type'] == 3) {
			$errors[]	= "Luta inválida";
		}

		if (!sizeof($errors)) {
			$battle					= new BattleNpc();
			$battle->player_id		= $player->id;
			$battle->battle_type_id	= $_POST['type'];
			$battle->save();

			if (!has_chance($player->get_parsed_effects()['no_consume_stamina'])) {
				$player->less_stamina	+= NPC_COST;
			}
			if ($_POST['type'] != 6){
				// Adiciona o total de npc diário
				$counters->current_npc_made++;
				$counters->save();
			}

			$player->battle_npc_id	= $battle->id;
			$player->save();

			$npc				= $player->get_npc();
			$npc->battle_npc_id	= $battle->id;
			$player->save_npc($npc);

			$this->json->success	= true;
		} else {
			$this->json->messages	= $errors;
		}
	}
	function accept_challenge() {
		$this->as_json			= true;
		$this->json->success	= false;
		$player					= Player::get_instance();
		$npc					= $player->get_npc_challenge();
		$errors					= [];

		if (!is_a($npc, 'NpcInstance')) {
			$errors[]	= t('battles.npc.errors.no_instance');
		}

		if ($player->is_pvp_queued) {
			$errors[]	= t('battles.npc.errors.pvp_queue');
		}

		// if ($player->battle_counters()->current_npc_made >= ($this->npc_limit + $player->attributes()->sum_bonus_daily_npc)) {
		// 	$errors[]	= t('battles.npc.errors.limit');
		// }

		if ($player->at_low_stat()) {
			$errors[]	= t('battles.errors.low_stat');
		}

		if ($player->for_stamina() < NPC_COST) {
			$errors[]	= t('battles.errors.no_stamina');
		}
		if ($_POST['type'] != 3) {
			$errors[]	= "Luta inválida";
		}

		if(!sizeof($errors)) {
			$battle					= new BattleNpc();
			$battle->player_id		= $player->id;
			$battle->battle_type_id	= $_POST['type'];
			$battle->save();

			if (!has_chance($player->get_parsed_effects()['no_consume_stamina'])) {
				$player->less_stamina	+= NPC_COST;
			}

			$player->battle_npc_id	= $battle->id;
			$player->battle_npc_challenge = 1;
			$player->save();

			$npc				= $player->get_npc_challenge();
			$npc->battle_npc_id	= $battle->id;
			$player->save_npc_challenge($npc);

			$this->json->success	= true;
		} else {
			$this->json->messages	= $errors;
		}
	}

	function fight() {
		$player		= Player::get_instance();
		$npc		= ($player->battle_npc_challenge ? $player->get_npc_challenge() : $player->get_npc());

		// magic, don't touch -->
			$player->clear_fixed_effects('fixed');
			$npc->clear_fixed_effects('fixed');

			$player->apply_battle_effects($npc);
			$npc->apply_battle_effects($player);

			if ($player->battle_npc_challenge) {
				$player->save_npc_challenge($npc);
			} else {
				$player->save_npc($npc);
			}
		// <--

		$this->assign('player',				$player);
		$this->assign('npc',				$npc);
		$this->assign('techniques',			$player->get_techniques());
		$this->assign('target_url',			make_url('battle_npcs'));
		$this->assign('log',				$player->battle_npc()->get_log());
		$this->assign('player_tutorial',	$player->player_tutorial());
	}

	function attack($is_copy = null, $is_kill = null) {
		$challenge 					= false;
		$player						= Player::get_instance();
		$npc						= ($player->battle_npc_challenge ? $player->get_npc_challenge() : $player->get_npc());
		$battle						= $player->battle_npc();
		$enemy_original_less_life	= $npc->less_life;

		if($player->challenge_id && $battle->battle_type_id == 3){
			$challenge  			= PlayerChallenge::find_first('player_id='. $player->id .' AND challenge_id='.$player->challenge_id .' AND complete = 0');
		}

		$log						= $battle->get_log();
		$errors						= [];
		$is_skip					= isset($_POST['item']) && $_POST['item'] == 'skip';
		$this->as_json				= true;
		$is_copy					= $is_copy == 'copy';
		$is_kill					= $is_kill == 'kill';

		if (!is_array($log)) {
			$log	= [];
		}

		if ($is_skip) {
			if (!isset($_SESSION['skipped'])) {
				$_SESSION['skipped']	= 0;
			}

			// $_SESSION['skipped']		+= 1;
			$_POST['item']				= 0;
		}

		if (!isset($_POST['item']) || (isset($_POST['item']) && !is_numeric($_POST['item']))) {
			$errors[]	= t('battles.errors.invalid');
		} elseif ($is_skip && $_SESSION['skipped'] > 2) {
			$errors[]	= t('battles.errors.can_not_skip');
		} else {
			if ($is_skip) {
				$item	= new SkipTurnItem();
			} else {
				$_SESSION['skipped'] = 0;

				if ($is_copy) {
					$player_item	= new FakePlayerItem($_POST['item'], $player);
					$item			= $player_item->item();

					$item->set_player($player);
					if (!$item->is_generic) {
						$item->set_character_theme($npc->character_theme());
					} else {
						$item->set_anime($npc->character()->anime_id);
					}
				} elseif ($is_kill){
					$player_item	= $player->get_technique($_POST['item']);
					$item			= $player_item->item();
				} else {
					$player_item	= $player->get_technique($_POST['item']);
					$item			= $player_item->item();
				}
			}

			// 1 = Golpes | 7 = Amplificadores
			$itemTypes = [1];
			if (!in_array($item->item_type_id, $itemTypes)) {
				$errors[]	= t('battles.errors.invalid');
			} else {
				$can_run_action	= true;

				if (!$is_copy && !$is_kill) {
					if ($item->formula()->consume_mana > $player->for_mana()) {
						$can_run_action	= false;
						$errors[]	= t('battles.errors.no_mana', ['mana' => strtolower(t('formula.for_mana.' . $item->anime()->id))]);
					}

					if ($player->has_technique_lock($item->id) && !$is_skip) {
						$can_run_action	= false;
						$errors[]	= t('battles.errors.locked');
					}

					if (!$is_skip) {
						if ($player->has_effects_with('stun')) {
							$can_run_action	= false;
							$errors[]		= t('battles.errors.stunned');
						}
					}
				}

				if ($can_run_action) {
					SharedStore::S('battle_used_ability_' . $player->id, false);
					SharedStore::S('battle_used_speciality_' . $player->id, false);

					$player_effects	= $player->get_parsed_effects();
					$enemy_effects	= $npc->get_parsed_effects();

					$player_init	= $player->for_init();
					$enemy_init		= $npc->for_init();
					$enemy_item		= $npc->choose_technique($item);

					$battle_instance				= new BattleInstance();
					$battle_instance->battle_npc_id	= $player->battle_npc_id;

					// Technique locks
					if (!$is_copy && !$is_kill) {
						$player->add_technique_lock($item);
					}

					$npc->add_technique_lock($item);

					// first players, then items, then effects
					$battle_instance->set_player($player);
					$battle_instance->set_enemy($npc);

					$battle_instance->set_player_item($item);
					$battle_instance->set_enemy_item($enemy_item);

					$battle_instance->add_effect($item, $player, $npc);
					$battle_instance->add_effect($enemy_item, $npc, $player);

					$battle_instance->run();

					$npc->shared_less_life += $npc->less_life - $enemy_original_less_life;

					// Consumes
					if (!$is_copy && !$is_kill) {
						$should_consume_mana	= true;
						$consume_half			= false;

						if ($item->formula()->consume_mana <= 3 && has_chance($player_effects['low_technique_no_cost'])) {
							$should_consume_mana	= false;
						}

						if(between($item->formula()->consume_mana, 4, 7) && has_chance($player_effects['mid_technique_no_cooldown'])) {
							$player->remove_technique_lock($item->id);
						}

						if ($item->formula()->consume_mana >= 8 && has_chance($player_effects['high_technique_half_cost'])) {
							$consume_half	= true;
						}

						if ($should_consume_mana) {
							$player->less_mana	+= $consume_half ? floor($item->formula()->consume_mana / 2) : $item->formula()->consume_mana;
						}
					}
					if ($challenge) {
						$npc->less_mana		+= $enemy_item->formula()->consume_mana - floor($challenge->quantity / 25);
					} else {
						$npc->less_mana		+= $enemy_item->formula()->consume_mana;
					}

					if (is_a($item, 'SkipTurnItem')) {
						if (has_chance(abs($player_effects['next_turn_mana']))) {
							if ($player_effects['next_turn_mana'] > 0) {
								$player->less_mana	-= 1;
							} else {
								$player->less_mana	+= 1;
							}
						}

						if (has_chance(abs($player_effects['next_turn_life']))) {
							if ($player_effects['next_turn_life'] > 0) {
								$player->less_life	-= 50;
							} else {
								$player->less_life	+= 50;
							}
						}
					}

					if (is_a($enemy_item, 'SkipTurnItem')) {
						if (has_chance(abs($enemy_effects['next_turn_mana']))) {
							if ($enemy_effects['next_turn_mana'] > 0) {
								$npc->less_mana	-= 1;
							} else {
								$npc->less_mana	+= 1;
							}
						}

						if (has_chance(abs($enemy_effects['next_turn_life']))) {
							if ($enemy_effects['next_turn_life'] > 0) {
								$npc->less_life	-= 50;
							} else {
								$npc->less_life	+= 50;
							}
						}
					}

					$npc->rotate_technique_locks();
					$npc->rotate_effects();
					$npc->rotate_ability_lock();
					$npc->rotate_speciality_lock();

					$player->rotate_technique_locks();
					$player->rotate_effects();
					$player->rotate_ability_lock();
					$player->rotate_speciality_lock();

					// Restore attribute
					if ($player->less_mana > 0) {
						if (!$player_effects['cancel_regen_mana']) {
							$player->less_mana	-= $player->less_mana == 1 ? 1 : 2;
						}

						// Remove 2 de mana do jogador e adiciona ao npc.
						if ($player_effects['steal_mana']) {
							$player->less_mana	+= $player_effects['steal_mana'];
							$npc->less_mana		-= $player_effects['steal_mana'];

							// trava para arrumar a mana negativa do npc
							$npc->less_mana		= $npc->less_mana > $npc->for_mana(true) ? $npc->for_mana(true) : $npc->less_mana;
						}

						// Remove 2 de mana do enemy.
						if ($player_effects['remove_mana']) {
							$npc->less_mana	-= $player_effects['remove_mana'];

							// trava para arrumar a mana negativa do npc
							$npc->less_mana		= $npc->less_mana > $npc->for_mana(true) ? $npc->for_mana(true) : $npc->less_mana;
						}
					}

					if ($player->less_mana > $player->for_mana(true)){
						$player->less_mana = $player->for_mana(true);
					}

					if ($npc->less_mana > 0) {
						if (!$enemy_effects['cancel_regen_mana']) {
							if ($challenge) {
								$npc->less_mana	-= (2 + floor($challenge->quantity / 20));
							}else{
								$npc->less_mana	-= $npc->less_mana == 1 ? 1 : 2;
							}
						}

						// Remove 2 de mana do jogador e adiciona ao npc.
						if($enemy_effects['steal_mana']){
							$npc->less_mana			+= $enemy_effects['steal_mana'];
							$player->less_mana		-= $enemy_effects['steal_mana'];
						}

						// Remove 2 de mana do enemy.
						if ($enemy_effects['remove_mana']) {
							$player->less_mana	-= $enemy_effects['remove_mana'];
						}
					}

					if ($npc->less_mana > $npc->for_mana(true)){
						$npc->less_mana = $npc->for_mana(true);
					}

					if ($player->less_life < 0) {
						$player->less_life	= 0;
					}

					if ($npc->less_life < 0) {
						$npc->less_life	= 0;
					}

					$battle_log	= $battle_instance->log;

					if ($battle_instance->first == 'player') {
						if ($battle->player_effect_log) {
							$battle_log[0]	= join('', unserialize($battle->player_effect_log)) . $battle_log[0];
						}

						if ($battle->enemy_effect_log) {
							$battle_log[1]	= join('', unserialize($battle->enemy_effect_log)) . $battle_log[1];
						}
					} else {
						if ($battle->enemy_effect_log) {
							$battle_log[0]	= join('', unserialize($battle->enemy_effect_log)) . $battle_log[0];
						}

						if ($battle->player_effect_log) {
							$battle_log[1]	= join('', unserialize($battle->player_effect_log)) . $battle_log[1];
						}
					}

					$battle_log	= join('<br />', $battle_log);
					$battle_log	= [ $battle_log ];

					if ($player->battle_npc_challenge) {
						$player->save_npc_challenge($npc);
					} else {
						$player->save_npc($npc);
					}

					$battle->save_log(array_merge($log, $battle_log));

					function __check_dead(&$who) {
						return $who->for_life() <= 0;
					}

					$finished		= false;
					$was_draw		= false;
					$can_draw		= $player_init == $enemy_init;

					// Empate
					if ($can_draw && __check_dead($npc) && __check_dead($player)) {
						$battle->won	= 0;
						$battle->draw	= 1;
						$finished		= true;
						$was_draw		= true;

						// Verifica se o jogador continua no desafio
						if ($battle->battle_type_id == 3 && $player->challenge_id) {
							$player_challenge	= PlayerChallenge::find_first("player_id =". $player->id. " AND challenge_id=". $player->challenge_id." AND complete=0");
							$challenge			= Challenge::find_first($player->challenge_id);

							$player_challenge->complete = 1;
							$player_challenge->completed_at = now(true);
							$player_challenge->save();

							$player->challenge_id = 0;
							$player->save();

							// Prêmios do Arena do Céu

							// Exp
							if ($player_challenge->quantity > 5) {
								$challenge_exp = $challenge->reward_exp * $player_challenge->quantity;
								if ($challenge_exp > 0) {
									$challenge_exp = 0;
								}
								$player->earn_exp($challenge_exp);
							}
							// Dinheiro
							if ($player_challenge->quantity > 5) {
								$challenge_money = $challenge->reward_gold * $player_challenge->quantity;
								if ($challenge_money > 0) {
									$challenge_money = 0;
								}
								$player->earn($challenge_money);
							}
							// Equipamento
							if ($player_challenge->quantity > 10 && $player_challenge->quantity < 21) {
								Item::generate_equipment($player, 0);
							} elseif ($player_challenge->quantity > 20 && $player_challenge->quantity < 46) {
								Item::generate_equipment($player, 1);
							} elseif ($player_challenge->quantity > 65) {
								Item::generate_equipment($player, 2);
							}
							// Mascote
							if ($player_challenge->quantity > 45 && $player_challenge->quantity < 81) {
								if (!$player->has_item($challenge->reward_pet_1)) {
									$player_pet				= new PlayerItem();
									$player_pet->item_id	= $challenge->reward_pet_1;
									$player_pet->player_id	= $player->id;
									$player_pet->save();
								}
							} elseif ($player_challenge->quantity > 80) {
								if (!$player->has_item($challenge->reward_pet_2)) {
									$player_pet				= new PlayerItem();
									$player_pet->item_id	= $challenge->reward_pet_2;
									$player_pet->player_id	= $player->id;
									$player_pet->save();
								}
							}
							// Titulo
							if ($player_challenge->quantity > 45 && $player_challenge->quantity < 81) {
								$user_headline	= UserHeadline::find_first("headline_id=".$challenge->reward_title_1." and user_id=".$player->user_id);
								if (!$user_headline) {
									$reward_headline				= new UserHeadline();
									$reward_headline->user_id		= $player->user_id;
									$reward_headline->headline_id	= $challenge->reward_title_1;
									$reward_headline->save();
								}
							} elseif ($player_challenge->quantity > 80) {
								$user_headline	= UserHeadline::find_first("headline_id=".$challenge->reward_title_2." and user_id=".$player->user_id);
								if (!$user_headline) {
									$reward_headline				= new UserHeadline();
									$reward_headline->user_id		= $player->user_id;
									$reward_headline->headline_id	= $challenge->reward_title_2;
									$reward_headline->save();
								}
							}
							// Estrelas
							if ($player_challenge->quantity > 100) {
								$user = $player->user();
								$user->credits += 3;
								$user->save();

								// Verifica os créditos do jogador.
								$player->achievement_check("credits");
								// Objetivo de Round
								$player->check_objectives("credits");
							}

							// Verifica a arena do jogador.
							$player->achievement_check("challenges");
							// Objetivo de Round
							$player->check_objectives("challenges");
						}
					}

					// Não empatou
					if (!$was_draw) {
						if ($player_init >= $enemy_init) {
							if (__check_dead($npc)) {
								$battle->won	= $player->id;
								$finished		= true;

								// Verifica se o jogador continua no desafio
								if ($battle->battle_type_id == 3 && $player->challenge_id) {
									$player_challenge	= PlayerChallenge::find_first("player_id =". $player->id. " AND challenge_id=". $player->challenge_id." AND complete=0");
									$player_challenge->quantity += 1;
									$player_challenge->save();

									// Verifica a arena do jogador.
									$player->achievement_check("challenges");
									// Objetivo de Round
									$player->check_objectives("challenges");
								}
							} else {
								if (__check_dead($player)) {
									$battle->won	= 0;
									$finished		= true;

									// Verifica se o jogador continua no desafio
									if ($battle->battle_type_id == 3 && $player->challenge_id) {
										$player_challenge	= PlayerChallenge::find_first("player_id =". $player->id. " AND challenge_id=". $player->challenge_id." AND complete=0");
										$challenge			= Challenge::find_first($player->challenge_id);
										$player_challenge->complete = 1;
										$player_challenge->completed_at = now(true);
										$player_challenge->save();

										$player->challenge_id = 0;
										$player->save();

										//Prêmios do Arena do Céu

										// Exp
										if ($player_challenge->quantity > 5) {
											$challenge_exp = $challenge->reward_exp * $player_challenge->quantity;
											if ($challenge_exp > 0) {
												$challenge_exp = 0;
											}
											$player->earn_exp($challenge_exp);
										}
										// Dinheiro
										if ($player_challenge->quantity > 5){
											$challenge_money = $challenge->reward_gold * $player_challenge->quantity;
											if ($challenge_money > 0) {
												$challenge_money = 0;
											}
											$player->earn($challenge_money);
										}
										// Equipamento
										if ($player_challenge->quantity > 10 && $player_challenge->quantity < 21) {
											Item::generate_equipment($player, 0);
										} elseif ($player_challenge->quantity > 20 && $player_challenge->quantity < 46) {
											Item::generate_equipment($player, 1);
										} elseif ($player_challenge->quantity > 65) {
											Item::generate_equipment($player, 2);
										}
										// Mascote
										if ($player_challenge->quantity > 45 && $player_challenge->quantity < 81) {
											if (!$player->has_item($challenge->reward_pet_1)) {
												$player_pet				= new PlayerItem();
												$player_pet->item_id	= $challenge->reward_pet_1;
												$player_pet->player_id	= $player->id;
												$player_pet->save();
											}

										} elseif ($player_challenge->quantity > 80) {
											if (!$player->has_item($challenge->reward_pet_2)) {
												$player_pet				= new PlayerItem();
												$player_pet->item_id	= $challenge->reward_pet_2;
												$player_pet->player_id	= $player->id;
												$player_pet->save();
											}
										}
										// Titulo
										if ($player_challenge->quantity > 45 && $player_challenge->quantity < 81) {
											$user_headline	= UserHeadline::find_first("headline_id=".$challenge->reward_title_1." and user_id=".$player->user_id);
											if (!$user_headline) {
												$reward_headline				= new UserHeadline();
												$reward_headline->user_id		= $player->user_id;
												$reward_headline->headline_id	= $challenge->reward_title_1;
												$reward_headline->save();
											}
										} elseif ($player_challenge->quantity > 80) {
											$user_headline	= UserHeadline::find_first("headline_id=".$challenge->reward_title_2." and user_id=".$player->user_id);
											if (!$user_headline) {
												$reward_headline				= new UserHeadline();
												$reward_headline->user_id		= $player->user_id;
												$reward_headline->headline_id	= $challenge->reward_title_2;
												$reward_headline->save();
											}
										}
										// Estrelas
										if ($player_challenge->quantity > 100) {
											$user = $player->user();
											$user->credits += 3;
											$user->save();

											// Verifica os créditos do jogador.
											$player->achievement_check("credits");
											// Objetivo de Round
											$player->check_objectives("credits");
										}

										// Verifica a arena do jogador.
										$player->achievement_check("challenges");
										// Objetivo de Round
										$player->check_objectives("challenges");
									}
								}
							}
						} elseif ($player_init < $enemy_init) {
							if (__check_dead($player)) {
								$battle->won	= 0;
								$finished		= true;

								// Verifica se o jogador continua no desafio
								if ($battle->battle_type_id == 3 && $player->challenge_id) {
									$player_challenge	= PlayerChallenge::find_first("player_id =". $player->id. " AND challenge_id=". $player->challenge_id." AND complete=0");
									$challenge			= Challenge::find_first($player->challenge_id);
									$player_challenge->complete = 1;
									$player_challenge->completed_at = now(true);
									$player_challenge->save();

									$player->challenge_id = 0;
									$player->save();

									//Prêmios do Arena do Céu

									// Exp
									if ($player_challenge->quantity > 5){
										$challenge_exp = $challenge->reward_exp * $player_challenge->quantity;
										if ($challenge_exp > 0) {
											$challenge_exp = 0;
										}
										$player->earn_exp($challenge_exp);
									}
									// Dinheiro
									if ($player_challenge->quantity > 5) {
										$challenge_money = $challenge->reward_gold * $player_challenge->quantity;
										if ($challenge_money > 0) {
											$challenge_money = 0;
										}
										$player->earn($challenge_money);
									}
									// Equipamento
									if ($player_challenge->quantity > 10 && $player_challenge->quantity < 21) {
										Item::generate_equipment($player, 0);
									} elseif ($player_challenge->quantity > 20 && $player_challenge->quantity < 46) {
										Item::generate_equipment($player, 1);
									} elseif ($player_challenge->quantity > 65) {
										Item::generate_equipment($player, 2);
									}
									// Mascote
									if ($player_challenge->quantity > 45 && $player_challenge->quantity < 81) {
										if (!$player->has_item($challenge->reward_pet_1)) {
											$player_pet				= new PlayerItem();
											$player_pet->item_id	= $challenge->reward_pet_1;
											$player_pet->player_id	= $player->id;
											$player_pet->save();
										}
									} elseif ($player_challenge->quantity > 80) {
										if (!$player->has_item($challenge->reward_pet_2)) {
											$player_pet				= new PlayerItem();
											$player_pet->item_id	= $challenge->reward_pet_2;
											$player_pet->player_id	= $player->id;
											$player_pet->save();
										}
									}
									// Titulo
									if ($player_challenge->quantity > 45 && $player_challenge->quantity < 81) {
										$user_headline	= UserHeadline::find_first("headline_id=".$challenge->reward_title_1." and user_id=".$player->user_id);
										if (!$user_headline) {
											$reward_headline				= new UserHeadline();
											$reward_headline->user_id		= $player->user_id;
											$reward_headline->headline_id	= $challenge->reward_title_1;
											$reward_headline->save();
										}
									} elseif ($player_challenge->quantity > 80) {
										$user_headline	= UserHeadline::find_first("headline_id=".$challenge->reward_title_2." and user_id=".$player->user_id);
										if (!$user_headline) {
											$reward_headline				= new UserHeadline();
											$reward_headline->user_id		= $player->user_id;
											$reward_headline->headline_id	= $challenge->reward_title_2;
											$reward_headline->save();
										}
									}
									// Estrelas
									if ($player_challenge->quantity > 100) {
										$user = $player->user();
										$user->credits += 3;
										$user->save();

										// Verifica os créditos do jogador.
										$player->achievement_check("credits");
										// Objetivo de Round
										$player->check_objectives("credits");
									}

									// Verifica a arena do jogador.
									$player->achievement_check("challenges");
									// Objetivo de Round
									$player->check_objectives("challenges");
								}
							} else {
								if (__check_dead($npc)) {
									$battle->won	= $player->id;
									$finished		= true;

									// Verifica se o jogador continua no desafio
									if ($battle->battle_type_id == 3 && $player->challenge_id) {
										$player_challenge	= PlayerChallenge::find_first("player_id =". $player->id. " AND challenge_id=". $player->challenge_id." AND complete=0");
										$player_challenge->quantity += 1;
										$player_challenge->save();

										// Verifica a arena do jogador.
										$player->achievement_check("challenges");
										// Objetivo de Round
										$player->check_objectives("challenges");
									}
								}
							}
						}
					}

					if ($finished) {
						$battle->finished_at			= now(true);
						$player->battle_npc_challenge	= 0;
					}

					$battle->player_effect_log	= null;
					$battle->enemy_effect_log	= null;
					$battle->current_turn++;

					$battle->save();
					$player->save();

					$this->_techniques_to_json($player);

					$_SESSION['can_apply_buff']	= true;
				}
			}
		}

		$this->json->log		= $battle->get_log();
		$this->json->messages	= $errors;
		$this->_stats_to_json($player, $npc, $battle);
		$this->_techniques_to_json($player);
	}
}
