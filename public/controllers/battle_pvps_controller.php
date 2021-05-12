<?php
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class BattlePvpsController extends Controller {
	use BattleSharedMethods;

	function ranked() {
		$player								= Player::get_instance();
		$leagues							= Ranked::find('started = 1 order by league asc');
		$best_rank							= PlayerRanked::find_first("player_id=" . $player->id . " ORDER BY rank ASC LIMIT 1");

		// Verifica se você tem liga completa - Conquista
		$player->achievement_check("league");

		if (!$_POST)	$league				= Ranked::find_first('started = 1 order by league desc');
		else			$league				= Ranked::find_first($_POST['leagues']);

		if ($league)	$player_ranked		= $player->ranked($league->league);
		else			$player_ranked		= FALSE;

		$this->assign('player',				$player);
		$this->assign('league',				$league);
		$this->assign('leagues',			$leagues);
		$this->assign('player_ranked',		$player_ranked);
		$this->assign('player_tutorial',	$player->player_tutorial());

		if ($best_rank) {
			$ranked_total					= Recordset::query("SELECT SUM(wins) AS total_wins, SUM(losses) AS total_losses, SUM(draws) AS total_draws FROM player_rankeds WHERE player_id = {$player->id}");
			$this->assign('best_rank',		$best_rank);
			$this->assign('ranked_total',	$ranked_total->result_array()[0]);
		}
	}

	function reward() {
		$this->as_json			= true;
		$this->json->success	= false;

		$player					= Player::get_instance();
		$user					= User::get_instance();
		$errors					= [];
		$content				= "";

		if (isset($_POST['id']) && is_numeric($_POST['id'])) {
			$league			= Ranked::find($_POST['id']);
			if ($league || !$league->finished) {
				$player_ranked	=  $player->ranked($league->league);
				if (!$player_ranked) {
					$errors[]	= t('ranked.errors.not_league');
				} else {
					if ($player_ranked->reward) {
						$errors[]	= t('ranked.errors.no_reward');
					}
					if ($player->id != $player_ranked->player_id) {
						$errors[]	= t('ranked.errors.no_player');
					}
				}
			} else {
				$errors[]	= t('ranked.errors.not_league');
			}

			if (!sizeof($errors)) {
				$rewards  = RankedReward::find_first("league = {$league->league} and rank = {$player_ranked->rank}");
				$player_ranked->reward = 1;
				$player_ranked->save();

				if ($rewards->currency) {
					$player->earn($rewards->currency);
					$content .= highamount($rewards->currency) ." ". t('currencies.' . $player->character()->anime_id). "<br />";
				}
				if ($rewards->exp) {
					$player->earn_exp($rewards->exp);
					$content .= highamount($rewards->exp). " " . t('ranked.exp')."<br />" ;
				}
				if ($rewards->credits) {
					$user->earn($rewards->credits);
					$content .= highamount($rewards->credits) ." ". t('treasure.show.credits'). "<br />";

					// Verifica os créditos do jogador.
					$player->achievement_check("credits");
				}
				if ($rewards->exp_user) {
					$user->exp($rewards->exp_user);
					$content .= highamount($rewards->exp_user) . " " . t('ranked.exp_account')."<br />";
				}
				if ($rewards->headline_id) {
					$reward_headline				= new UserHeadline();
					$reward_headline->user_id		= $player->user_id;
					$reward_headline->headline_id	= $rewards->headline_id;
					$reward_headline->save();

					$content .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name . "<br />";
				}
				if ($rewards->character_id) {
					$reward_character				= new UserCharacter();
					$reward_character->user_id		= $player->user_id;
					$reward_character->character_id	= $rewards->character_id;
					$reward_character->was_reward	= 1;
					$reward_character->save();

					$content .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name . "<br />";
				}
				if ($rewards->character_theme_id) {
					$reward_theme						= new UserCharacterTheme();
					$reward_theme->user_id				= $player->user_id;
					$reward_theme->character_theme_id	= $rewards->character_theme_id;
					$reward_theme->was_reward			= 1;
					$reward_theme->save();

					$content .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name . "<br />";
				}
				if($rewards->item_id) {
					$item		= Item::find_first($rewards->item_id);
					$player->add_consumable($item, $rewards->quantity);

					$content .= highamount($rewards->quantity) ."x ". Item::find($rewards->item_id)->description()->name . "<br />";
				}

				$pm				= new PrivateMessage();
				$pm->to_id		= $player->id;
				$pm->subject	= t("ranked.reward_league") . " - ". ($player_ranked->rank == 0 ? "Rank All-Star" : "Rank {$player_ranked->rank}");
				$pm->content	= $content;
				$pm->save();

				$this->json->success = TRUE;
			}
		} else {
			$errors[]	= t('ranked.errors.not_league');
		}

		$this->json->messages	= $errors;
	}

	function index() {
		$player	= Player::get_instance();

		// Cleanups -->
		SharedStore::S('last_battle_item_of_' . $player->id, 0);

		$player->clear_ability_lock();
		$player->clear_speciality_lock();
		$player->clear_technique_locks();
		$player->clear_effects();
		// <--

		$player->refresh_talents();

		$_SESSION['pvp_used_buff']			= FALSE;
		$_SESSION['pvp_used_ability']		= FALSE;
		$_SESSION['pvp_used_speciality']	= FALSE;
		$_SESSION['pvp_time_reduced']		= 0;

		$this->assign('player', $player);
		$this->assign('player_tutorial', $player->player_tutorial());
	}
	function training(){

	}
	function waiting(){
		$player			= Player::get_instance();
		$this->assign('player', $player);
	}
	function room_create(){
		$this->as_json			= true;
		$this->json->success	= false;
		$player					= Player::get_instance();
		$errors					= [];

		if (!$_POST['nome']) {
			$errors[]	= t('battles.waiting.errors.not_name');
		}
		if ($player->is_pvp_queued) {
			$errors[]	= t('battles.npc.errors.pvp_queue');
		}
		if ($player->battle_pvp_id) {
			$errors[]	= t('battles.waiting.errors.pvp_queue');
		}
		if ($player->battle_npc_id) {
			$errors[]	= t('battles.npc.errors.pvp_queue');
		}
		if ($player->at_low_stat()) {
			$errors[]	= t('battles.errors.low_stat');
		}

		if(!sizeof($errors)) {

			$battle_room 				= new BattleRoom();
			$battle_room->player_id 	= $player->id;
			$battle_room->room_name		= $_POST['nome'];
			$battle_room->save();

			$player->battle_room_id = $battle_room->id;
			$player->save();

			$this->assign('rooms', "");
			$this->assign('player', "");

			$this->json->success	= true;
		} else {
			$this->json->messages	= $errors;
		}

	}
	function room_list() {
		$this->layout			= false;
		$player					= Player::get_instance();

		$this->assign('player', $player);
		$this->assign('rooms', BattleRoom::all());
	}
	function decline(){
		$this->as_json			= true;
		$this->json->success	= false;
		$player					= Player::get_instance();
		$errors					= [];

		if($player->battle_pvp_id) {
			$errors[]	= t('battles.waiting.errors.pvp_queue');
		}
		if(!$player->battle_room_id) {
			$errors[]	= t('battles.waiting.errors.room_not_found');
		}
		if(!sizeof($errors)) {

			// Destroi a sala do jogador
			$battle_room = BattleRoom::find_first("id=".$player->battle_room_id);
			$battle_room->destroy();

			// Tira o jogador da sala
			$player->battle_room_id	= 0;
			$player->save();

			$this->json->success	= true;
		} else {
			$this->json->messages	= $errors;
		}
	}
	function waiting_queue(){
		$this->as_json	= true;
		$player			= Player::get_instance();

		$battle = BattlePvp::find_first("enemy_id=".$player->id." AND battle_type_id=4 AND finished_at is null");

		if($battle) {

			// Destroi a sala do jogador
			$battle_room = BattleRoom::find_first("id=".$player->battle_room_id);
			$battle_room->destroy();

			// Jogador em espera
			$player->battle_pvp_id = $battle->id;
			$player->save();

			// Apaga o número da sala da player
			$player->battle_room_id = 0;
			$player->save();

			$this->json->redirect	= make_url('battle_pvps#fight');
		} else {
			// Destroi a sala do jogador
			/*$battle_room = BattleRoom::find_first("id=".$player->battle_room_id);
			$battle_room->destroy();

			// Apaga o número da sala da player
			$player->battle_room_id = 0;
			$player->save();

			$this->json->redirect	= make_url('characters#status');*/
		}
	}
	function accept() {
		$this->as_json			= true;
		$this->json->success	= false;
		$player					= Player::get_instance();
		$errors					= [];

		if (isset($_POST['id']) && is_numeric($_POST['id'])) {
			$room  			=  BattleRoom::find($_POST['id']);
			if (!$room)
				$errors[]	= t('battles.waiting.errors.room_invalid');
			else {
				$player_enemy 	= Player::find($room->player_id);

				if ($player->is_pvp_queued) {
					$errors[]	= t('battles.npc.errors.pvp_queue');
				}
				if ($player->battle_pvp_id) {
					$errors[]	= t('battles.waiting.errors.pvp_queue');
				}
				if ($player->battle_npc_id) {
					$errors[]	= t('battles.npc.errors.pvp_queue');
				}
				if ($player->at_low_stat()) {
					$errors[]	= t('battles.errors.low_stat');
				}
				if ($player_enemy->battle_pvp_id){
					$errors[]	= t('battles.waiting.errors.room_invalid');
				}
			}

		}else{
			$errors[]	= t('battles.waiting.errors.room_invalid');
		}
		if(!sizeof($errors)) {
			$battle					= new BattlePvp();
			$battle->player_id		= $player->id;
			$battle->current_id		= $player->id;
			$battle->last_atk		= now(true);
			$battle->enemy_id		= $room->player_id;
			$battle->battle_type_id	= 4;
			$battle->save();


			// Jogador que clicou no botão
			$player->battle_pvp_id	= $battle->id;
			$player->save();

			// Jogador em espera
			/*$player_enemy->battle_pvp_id = $battle->id;
			$player_enemy->save();*/

			$this->json->success	= TRUE;
		} else {
			$this->json->messages	= $errors;
		}
	}
	function enter_queue() {
		$this->as_json			= TRUE;
		$this->json->success	= FALSE;

		$errors					= [];
		$player					= Player::get_instance();

		if ($player->at_low_stat()) {
			$errors[]	= t('battles.errors.low_stat');
		}
		if ($player->is_pvp_queued) {
			$errors[]	= t('battles.waiting.errors.is_pvp_queued');
		}

		if ($player->for_stamina() < PVP_COST) {
			$errors[]	= t('battles.errors.no_stamina');
		}

		if (!sizeof($errors)) {
			$this->json->success	= TRUE;
			$connection				= new AMQPConnection(PVP_SERVER, PVP_PORT, 'guest', 'guest');
			$channel				= $connection->channel();
			$channel->queue_declare(PVP_CHANNEL, FALSE, FALSE, FALSE, FALSE);

			if (date('w') == 0 || date('w') == 2 || date('w') == 4) {
				$has_league		= Ranked::find_first('started = 1 and finished = 0 order by league desc');
				if ($has_league) {
					$battle_type_id	= 5;
				} else {
					$battle_type_id	= 2;
				}
			} else {
				$battle_type_id = 2;
			}

			$message	= new AMQPMessage(json_encode([
				'method'			=> 'enter_queue',
				'queue_id'			=> null,
				'id'				=> $player->id,
				'name'				=> $player->name,
				'level'				=> (int)$player->level,
				'init'				=> (int)$player->for_init(),
				'graduation'		=> $player->graduation()->sorting,
				'won'				=> $player->won_last_battle,
				'battle_type_id'	=> $battle_type_id,
				'ip'				=> $player->last_ip
			]), [
				'delivery_mode'	=> 2 # persistent mode
			]);

			$player->less_stamina	+= PVP_COST;
			$player->is_pvp_queued	= TRUE;
			$player->save();

			$channel->basic_publish($message, '', PVP_CHANNEL);

			$channel->close();
			$connection->close();
		} else {
			$this->json->messages	= $errors;
		}
	}
	function check_queue() {
		$this->as_json	= TRUE;
		$player			= Player::get_instance();

		if ($player->pvp_queue_found > now()) {
			$diff = $player->pvp_queue_found - now();

			$this->json->found		= TRUE;
			$this->json->seconds	= $diff;
		} else {
			$this->json->found		= FALSE;

			if ($player->battle_pvp_id) {
				$this->json->redirect	= make_url('battle_pvps#fight');
			}
		}
	}
	function accept_queue() {
		$this->as_json	= TRUE;
		$player			= Player::get_instance();

		if ($player->pvp_queue_found) {
			// Cleanups -->
			SharedStore::S('last_battle_item_of_' . $player->id, 0);

			$player->clear_ability_lock();
			$player->clear_speciality_lock();
			$player->clear_technique_locks();
			$player->clear_effects();
			// <--

			$player->refresh_talents();

			$_SESSION['pvp_used_buff']			= FALSE;
			$_SESSION['pvp_used_ability']		= FALSE;
			$_SESSION['pvp_used_speciality']	= FALSE;
			$_SESSION['pvp_time_reduced']		= 0;

			$connection = new AMQPConnection(PVP_SERVER, PVP_PORT, 'guest', 'guest');
			$channel	= $connection->channel();
			$channel->queue_declare(PVP_CHANNEL, FALSE, FALSE, FALSE, FALSE);

			$message	= new AMQPMessage(json_encode([
				'method'		=> 'accept_queue',
				'id'			=> $player->id
			]), [
				'delivery_mode'	=> 2 # persistent mode
			]);

			$channel->basic_publish($message, '', PVP_CHANNEL);

			$channel->close();
			$connection->close();
		}
	}
	function exit_queue() {
		$this->as_json				= TRUE;
		$this->json->success		= FALSE;

		$errors						= [];
		$player						= Player::get_instance();

		/*if ($player->pvp_queue_found >= now()) {
			$errors[]	= t('battles.waiting.errors.pvp_match_found');
		}*/
		if (!$player->is_pvp_queued) {
			$errors[]	= t('battles.waiting.errors.no_pvp_queued');
		}

		if (!sizeof($errors)) {
			$this->json->success	= TRUE;

			$connection				= new AMQPConnection(PVP_SERVER, PVP_PORT, 'guest', 'guest');
			$channel				= $connection->channel();
			$channel->queue_declare(PVP_CHANNEL, FALSE, FALSE, FALSE, FALSE);

			$message	= new AMQPMessage(json_encode([
				'method'		=> 'exit_queue',
				'id'			=> $player->id
			]), [
				'delivery_mode'	=> 2 # persistent mode
			]);

			$player->less_stamina	-= PVP_COST;
			if ($player->less_stamina < 0) {
				$player->less_stamina	= 0;
			}

			$player->pvp_queue_found	= NULL;
			$player->is_pvp_queued		= 0;
			$player->save();

			$channel->basic_publish($message, '', PVP_CHANNEL);

			$channel->close();
			$connection->close();
		} else {
			$this->json->messages	= $errors;
		}
	}

	function fight() {
		$player		   = Player::get_instance();
		$battle		   = $player->battle_pvp();
		$enemy		   = $battle->enemy();
		$player_wanted = PlayerWanted::find_first("player_id=".$player->id." AND death=0");
		$enemy_wanted  = PlayerWanted::find_first("player_id=".$enemy->id." AND death=0");

		$word_player	= ($battle->player_id == $player->id ? 'player' : 'enemy') . '_mana';

		// Regra de range para os talentos do AASG!
		if($player->user()->level < 46 || $enemy->user()->level < 46){
			if(!$player->no_talent){
				if($player->user()->level > $enemy->user()->level+10){
					$player->no_talent = 2;
					$player->save();
				}
			}
			if(!$enemy->no_talent){
				if($enemy->user()->level > $player->user()->level+10){
					$enemy->no_talent = 2;
					$enemy->save();
				}
			}
		}

		if (!$battle->{$word_player}) {
			$battle->{$word_player} = $player->for_mana();
			$battle->save();
		}

		$stats		= PlayerBattlePvpLog::find_first('player_id=' . $player->id . ' AND enemy_id=' . $enemy->id);

		if(!$stats) {
			$stats				= new PlayerBattlePvpLog();
			$stats->player_id	= $player->id;
			$stats->enemy_id	= $enemy->id;
			$stats->save();
		}

		// magic, don't touch -->
		$player->clear_fixed_effects('fixed');
		$player->apply_battle_effects($enemy);
		// <--


		$this->assign('player_wanted', $player_wanted);
		$this->assign('enemy_wanted', $enemy_wanted);
		$this->assign('player', $player);
		$this->assign('battle', $battle);
		$this->assign('enemy', $enemy);
		$this->assign('stats', $stats);
		$this->assign('techniques', $player->get_techniques());
		$this->assign('target_url', make_url('battle_pvps'));
		$this->assign('log', @unserialize($player->battle_pvp()->battle_log));
	}

	function attack($is_copy = null, $is_kill = null) {
		$this->as_json				= true;

		$player						= Player::get_instance();
		$battle						= $player->battle_pvp();
		$enemy						= $battle->enemy();
		$log						= $battle->get_log();
		$errors						= [];
		$is_skip					= isset($_POST['item']) && $_POST['item'] == 'skip';
		$is_copy					= $is_copy == 'copy';
		$is_kill					= $is_kill == 'kill';
		$can_run_action				= true;
		$should_update_mana			= false;

		if(!is_array($log)) {
			$log	= [];
		}

		if ($is_skip) {
			$_POST['item']	= 0;
		}

		if(!isset($_POST['item']) || (isset($_POST['item']) && !is_numeric($_POST['item']))) {
			$errors[]	= t('battles.errors.invalid');
		} else {
			if ($is_skip) {
				$item	= new SkipTurnItem();
			} else {
				if ($is_copy) {
					$player_item	= new FakePlayerItem($_POST['item'], $player);
					$item			= $player_item->item();

					$item->set_player($player);

					if (!$item->is_generic) {
						$item->set_character_theme($enemy->character_theme());
					} else {
						$item->set_anime($enemy->character()->anime_id);
					}
				}elseif($is_kill){
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
				if (!$is_copy && !$is_kill) {
					if($item->formula()->consume_mana > $player->for_mana()) {
						$can_run_action	= false;
						$errors[]	= t('battles.errors.no_mana', ['mana' => t('formula.for_mana.' . $item->anime()->id)]);
					}

					if($player->has_technique_lock($item->id)) {
						$can_run_action	= false;
						$errors[]		= t('battles.errors.locked');
					}

					if (!$is_skip) {
						if ($player->has_effects_with('stun')) {
							$can_run_action	= false;
							$errors[]		= t('battles.errors.stunned');
						}
					}
				}

				if($battle->current_id != $player->id) {
					$can_run_action	= false;
					$errors[]	= t('battles.errors.not_my_turn');
				}

				if ($can_run_action) {
					if ($item->is_buff) {
						$_SESSION['pvp_used_buff']	= true;
					}

					// extreme black magic, don't touch -->
					$player->clear_fixed_effects('fixed');
					$enemy->clear_fixed_effects('fixed');

					$player->apply_battle_effects($enemy);
					$enemy->apply_battle_effects($player);
					// <--

					// Clean up ability/speciality lock
					SharedStore::S('battle_used_ability_' . $player->id, false);
					SharedStore::S('battle_used_speciality_' . $player->id, false);

					SharedStore::S('battle_used_ability_' . $enemy->id, false);
					SharedStore::S('battle_used_speciality_' . $enemy->id, false);

					$was_draw			= false;
					$battle->last_atk	= now(true);
					$word_player		= $battle->player_id == $player->id ? 'player' : 'enemy';
					$word_enemy			= $battle->player_id == $player->id ? 'enemy' : 'player';

					$field				= $battle->current_id == $player->id ? $word_player . '_id' : $word_enemy . '_id';
					$field_item_mine	= $battle->current_id == $player->id ? $word_player . '_item_id' : $word_enemy . '_item_id';
					$field_item_enemy	= $battle->current_id == $player->id ? $word_enemy . '_item_id' : $word_player . '_item_id';
					$field_copy_mine	= $battle->current_id == $player->id ? $word_player . '_copy_id' : $word_enemy . '_copy_id';
					$field_copy_enemy	= $battle->current_id == $player->id ? $word_enemy . '_copy_id' : $word_player . '_copy_id';
					$field_kill_mine	= $battle->current_id == $player->id ? $word_player . '_kill_id' : $word_enemy . '_kill_id';
					$field_kill_enemy	= $battle->current_id == $player->id ? $word_enemy . '_kill_id' : $word_player . '_kill_id';

					$player_effect_log_field	= $battle->current_id == $player->id ? $word_player . '_effect_log' : $word_enemy . '_effect_lod';
					$enemy_effect_log_field		= $battle->current_id == $player->id ? $word_enemy . '_effect_log' : $word_player . '_effect_lod';

					$player_effects	= $player->get_parsed_effects();
					$enemy_effects	= $enemy->get_parsed_effects();

					// Technique locks
					if (!$is_copy && !$is_kill) {
						$player->add_technique_lock($item);
					}

					// Now we inform the enemy queue that the page should ping for new data -->
					$battle->{$word_enemy . '_should_refresh'}	= 1;
					// <--

					if($battle->should_process) {
						$is_enemy_copy	= false;
						$is_enemy_kill	= false;

						if (!$battle->{$field_item_enemy}) {
							$enemy_item	= new SkipTurnItem();
						} else {
							// Trickhy way, if the current player
							if ($battle->{$field_copy_enemy}) {
								$is_enemy_copy		= true;

								$enemy_player_item	= new FakePlayerItem($battle->{$field_copy_enemy}, $enemy);
								$enemy_item			= $enemy_player_item->item();

								$enemy_item->set_player($enemy);

								if ($enemy_item->is_generic) {
									$enemy_item->set_anime($player->character()->anime_id);
								} else {
									$enemy_item->set_character_theme($player->character_theme());
								}
							}elseif($battle->{$field_kill_enemy}){
								$is_enemy_kill		= true;

								$enemy_player_item	= new FakePlayerItem($battle->{$field_kill_enemy}, $enemy);
								$enemy_item			= $enemy_player_item->item();

								$enemy_item->set_player($enemy);

								if ($enemy_item->is_generic) {
									$enemy_item->set_anime($player->character()->anime_id);
								} else {
									$enemy_item->set_character_theme($player->character_theme());
								}
							} else {
								$enemy_player_item	= $enemy->get_technique($battle->{$field_item_enemy});
								$enemy_player_item->set_player($enemy);
								$enemy_item		= $enemy_player_item->item();
							}
						}

						$player_init	= $player->for_init();
						$enemy_init		= $enemy->for_init();

						$battle_instance				= new BattleInstance();
						$battle_instance->battle_pvp_id	= $player->battle_pvp_id;

						// first players, then items, then effects
						$battle_instance->set_player($player);
						$battle_instance->set_enemy($enemy);

						$battle_instance->set_player_item($item);
						$battle_instance->set_enemy_item($enemy_item);

						$battle_instance->add_effect($item, $player, $enemy);
						$battle_instance->add_effect($enemy_item, $enemy, $player);

						// runs da battle mothafocka
						$battle_instance->run();

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

						if (!$is_enemy_copy && !$is_enemy_kill) {
							$should_consume_mana	= true;
							$consume_half			= false;

							if ($enemy_item->formula()->consume_mana <= 3 && has_chance($enemy_effects['low_technique_no_cost'])) {
								$should_consume_mana	= false;
							}

							if(between($enemy_item->formula()->consume_mana, 4, 7) && has_chance($enemy_effects['mid_technique_no_cooldown'])) {
								$enemy->remove_technique_lock($enemy_item->id);
							}

							if ($enemy_item->formula()->consume_mana >= 8 && has_chance($enemy_effects['high_technique_half_cost'])) {
								$consume_half	= true;
							}

							if ($should_consume_mana) {
								$enemy->less_mana	+= $consume_half ? floor($enemy_item->formula()->consume_mana / 2) : $enemy_item->formula()->consume_mana;
							}
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
									$enemy->less_mana	-= 1;
								} else {
									$enemy->less_mana	+= 1;
								}
							}

							if (has_chance(abs($enemy_effects['next_turn_life']))) {
								if ($enemy_effects['next_turn_life'] > 0) {
									$enemy->less_life	-= 50;
								} else {
									$enemy->less_life	+= 50;
								}
							}
						}

						if ($player->less_life < 0) {
							$player->less_life	= 0;
						}

						if ($enemy->less_life < 0) {
							$enemy->less_life	= 0;
						}

						$enemy->rotate_technique_locks();
						$enemy->rotate_effects();
						$enemy->rotate_ability_lock();
						$enemy->rotate_speciality_lock();

						$player->rotate_technique_locks();
						$player->rotate_effects();
						$player->rotate_ability_lock();
						$player->rotate_speciality_lock();

						$battle_log	= $battle_instance->log;

						if ($battle_instance->first == 'player') {
							if ($battle->player_effect_log) {
								$battle_log[0]	= join('', unserialize($battle->player_effect_log)) . (isset($battle_log[0]) ? $battle_log[0] : '');
							}

							if ($battle->enemy_effect_log) {
								$battle_log[1]	= join('', unserialize($battle->enemy_effect_log)) . (isset($battle_log[1]) ? $battle_log[1] : '');
							}
						} else {
							if ($battle->enemy_effect_log) {
								$battle_log[0]	= join('', unserialize($battle->enemy_effect_log)) . (isset($battle_log[0]) ? $battle_log[0] : '');
							}

							if ($battle->player_effect_log) {
								$battle_log[1]	= join('', unserialize($battle->player_effect_log)) . (isset($battle_log[1]) ? $battle_log[1] : '');
							}
						}

						$battle_log	= join('<br />', $battle_log);
						$battle_log	= [ $battle_log ];
						$battle->save_log(array_merge($log, $battle_log));

						$battle->current_id			= $enemy->id;
						$battle->{$field_copy_enemy}	= 0;
						$battle->{$field_copy_mine}	= 0;
						$battle->{$field_kill_enemy}	= 0;
						$battle->{$field_kill_mine}	= 0;
						$battle->should_process		= 0;
						$battle->player_effect_log	= null;
						$battle->enemy_effect_log	= null;
						$battle->current_turn++;

						$should_update_mana			= true;

						function __check_dead(&$who) {
							return $who->for_life() <= 0;
						}

						$finished	= false;
						$can_draw	= $player_init == $enemy_init;

						if($can_draw && __check_dead($enemy) && __check_dead($player)) {
							$battle->won	= 0;
							$battle->draw	= 1;
							$finished		= true;
							$was_draw		= true;
						}

						if(!$was_draw) {
							if($player_init >= $enemy_init) {
								if(__check_dead($enemy)) {
									$battle->won	= $player->id;
									$finished		= true;
								} else {
									if(__check_dead($player)) {
										$battle->won	= $enemy->id;
										$finished		= true;
									}
								}
							} elseif($player_init < $enemy_init) {
								if(__check_dead($player)) {
									$battle->won	= $enemy->id;
									$finished		= true;
								} else {
									if(__check_dead($enemy)) {
										$battle->won	= $player->id;
										$finished		= true;
									}
								}
							}
						}

						if($finished) {
							$battle->finished_at	= now(true);
						} else {
							// Restore attribute
							if ($player->less_mana > 0) {
								if(!$player_effects['cancel_regen_mana']){
									$player->less_mana	-= $player->less_mana == 1 ? 1 : 2;
								}
								// Remove 2 de mana do enemy  e adiciona ao player.
								if($player_effects['steal_mana']){
									$player->less_mana	+= $player_effects['steal_mana'];
									$enemy->less_mana	-= $player_effects['steal_mana'];
								}
								// Remove 2 de mana do enemy.
								if($player_effects['remove_mana']){
									$enemy->less_mana	-= $player_effects['remove_mana'];
								}
							}
							if($player->less_mana > $player->for_mana(true)){
								$player->less_mana = $player->for_mana(true);
							}

							if ($enemy->less_mana > 0) {
								if(!$enemy_effects['cancel_regen_mana']){
									$enemy->less_mana	-= $enemy->less_mana == 1 ? 1 : 2;
								}
								// Remove 2 de mana do jogador e adiciona ao npc.
								if($enemy_effects['steal_mana']){
									$player->less_mana	-= $enemy_effects['steal_mana'];
									$enemy->less_mana	+= $enemy_effects['steal_mana'];
								}
								// Remove 2 de mana do enemy.
								if($enemy_effects['remove_mana']){
									$player->less_mana	-= $enemy_effects['remove_mana'];
								}
							}
							if($enemy->less_mana > $enemy->for_mana(true)){
								$enemy->less_mana = $enemy->for_mana(true);
							}
						}
					} else {
						$battle->should_process		= 1;
						$battle->current_id			= $enemy->id;
						$battle->{$field_item_mine}	= $item->id;
						$battle->draw				= $was_draw ? 1 : 0;

						if ($is_copy) {
							$battle->{$field_copy_mine}	= $item->id;
						}
						if ($is_kill) {
							$battle->{$field_kill_mine}	= $item->id;
						}

						$this->_techniques_to_json($player);
					}

					$battle->save();
					$player->save();
					$enemy->save();

					$_SESSION['can_apply_buff']	= TRUE;
				}
			}
		}

		$this->json->messages	= $errors;
		$this->_stats_to_json($player, $enemy, $battle, $can_run_action, FALSE, $should_update_mana);
	}
}
