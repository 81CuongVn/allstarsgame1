<?php
class GuildsController extends Controller {
	public	$credits_price	= 3;
	public	$currency_price	= 5000;
	public	$min_level		= 5;

	public	$max_players	= 8;

	function __construct() {
		Guild::$player_limit = $this->max_players;

		parent::__construct();
	}

	function events() {
		$player	= Player::get_instance();
		$events	= GuildEvent::find("removed=0");

		$this->assign('player', $player);
		$this->assign('events', $events);
	}

	function unlock() {
		$this->as_json			= true;
		$this->json->success	= false;
		$errors					= [];

		if (!isset($_POST['event_id']) || (isset($_POST['event_id']) && !is_numeric($_POST['event_id']))) {
			$errors[]	= t('history_mode.unlock.errors.invald');
		} else {
			$player	= Player::get_instance();
			$event	= GuildEvent::find($_POST['event_id']);

			if ($event->removed) {
				$event	= false;
			}

			if (!$event) {
				$errors[]	= t('history_mode.unlock.errors.invalid');
			} else {
				if ($_POST['mode'] == 1 && $player->currency < $event->currency) {
					$errors[]	= t('history_mode.unlock.errors.not_enough_currency');
				} elseif ($_POST['mode'] != 1 && $player->user()->credits < $event->credits) {
					$errors[]	= t('history_mode.unlock.errors.not_enough_vip');
				}
			}
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;

			if ($_POST['mode'] == 1) {
				$player->spend($event->currency);
			} else {
				$player->user()->spend($event->credits);
			}

			// Salva o Id na Tabela de Organização aceita
			$acccepted_event = new GuildAcceptedEvent();
			$acccepted_event->guild_id			= $player->guild_id;
			$acccepted_event->guild_event_id	= $event->id;
			$acccepted_event->player_id			= $player->id;
			$acccepted_event->save();
		} else {
			$this->json->messages	= $errors;
		}
	}

	function dungeon() {
		$player		= Player::get_instance();
		$position	= $player->position();
		$map		= GuildMap::find_first($player->position()->guild_map_id);

		$_SESSION['guild_dungeon_key']	= uniqid('', true);

		// TODO: Fazer uma verificação para ver se existe uma dungeon ativa

		$this->assign('player', $player);
		$this->assign('position', $position);
		$this->assign('map', $map);
	}

	function dungeon_move() {
		$this->as_json			= true;
		$this->json->success	= false;
		$this->json->players	= [];
		$this->json->objects	= [];

		$player		= Player::get_instance();
		$position	= $player->position();
		$map		= GuildMap::find_first($position->guild_map_id);
		$errors		= [];

		if ($_POST) {
			if (!isset($_POST['key']) || (isset($_POST['key']) && $_POST['key'] != $_SESSION['guild_dungeon_key'])) {
				$errors[] = 'Chave de autenticação do mapa é inválida, possivelmente você abriu outra aba >:(';
			}

			if (!sizeof($errors)) {
				$this->json->success = true;

				// upate my own position
				if (isset($_POST['x']) && isset($_POST['y']) && is_numeric($_POST['x']) && is_numeric($_POST['y'])) {
					$something = $map->at($_POST['x'], $_POST['y']);

					if ($something) {
						if ($something->kind == 'door') {
							$position->xpos			= $something->target_xpos;
							$position->ypos			= $something->target_ypos;
							$position->guild_map_id	= $something->target_guild_map_id;

							$this->json->reload = true;
						} elseif ($something->kind == 'shareditem') {

						} elseif ($something->kind == 'uniqueitem') {

						}
					} else {
						$position->xpos = $_POST['x'];
						$position->ypos = $_POST['y'];
					}

					$position->save();
				}

				$players = PlayerPosition::from_guild_with_map($player->guild_id, $position->guild_map_id, $player->guild_accepted_event_id);
				foreach ($players as $p) {
					$this->json->players[] = [
						'id' 		=> $p->player_id,
						'name' 		=> $p->player_name,
						'theme' 	=> $p->character_theme_id,
						'character' => $p->character_id,
						'x' 		=> $p->xpos,
						'y' 		=> $p->ypos
					];
				}

				foreach($map->objects() as $object) {
					if ($object->kind == 'sharednpc') {
						if (GuildMapObjectSession::find('player_id=0 AND down=1 AND guild_accepted_event_id=' . $player->guild_accepted_event_id . ' AND guild_id=' . $player->guild_id . ' AND guild_map_object_id=' . $object->id)) {
							continue;
						}
					} elseif ($object->kind == 'chest') {
						if (GuildMapObjectSession::find('down=1 AND guild_accepted_event_id=' . $player->guild_accepted_event_id . ' AND guild_id=' . $player->guild_id . ' AND guild_map_object_id=' . $object->id)) {
							continue;
						}
					} elseif ($object->kind == 'sharedchest') {
						if (GuildMapObjectSession::find('player_id=' . $player->id . ' AND down=1 AND guild_accepted_event_id=' . $player->guild_accepted_event_id . ' AND guild_id=' . $player->guild_id . ' AND guild_map_object_id=' . $object->id)) {
							continue;
						}
					} else {
						if (GuildMapObjectSession::find('down=1 AND player_id=' . $player->id . ' AND guild_accepted_event_id=' . $player->guild_accepted_event_id . ' AND guild_id=' . $player->guild_id . ' AND guild_map_object_id=' . $object->id)) {
							continue;
						}
					}

					$objekt = [
						'id'	=> $object->id,
						'name'	=> $object->description()->name,
						'kind'	=> $object->kind,
						'x'		=> $object->xpos,
						'y'		=> $object->ypos
					];

					if ($object->kind == 'npc' || $object->kind == 'sharednpc') {
						$objekt['theme']		= $object->character_theme_id;
						$objekt['character']	= CharacterTheme::find_first($object->character_theme_id)->character_id;
					}

					$this->json->objects[]	= $objekt;
				}
			} else {
				$this->json->messages		= $errors;
			}
		}
	}
	function dungeon_take() {
		$this->as_json			= true;
		$this->json->success	= false;

		$player		= Player::get_instance();
		$user		= User::get_instance();
		$position	= $player->position();
		$errors		= [];

		$object = GuildMapObject::find_first('guild_map_id=' . $position->guild_map_id . ' AND id=' . $_POST['id']);
		if ($object) {
			if ($object->kind == 'sharedchest') {
				if (GuildMapObjectSession::find('player_id=' . $player->id . ' AND down=1 AND guild_accepted_event_id=' . $player->guild_accepted_event_id . ' AND guild_id=' . $player->guild_id . ' AND guild_map_object_id=' . $object->id)) {
					$errors[] = t('guilds.errors.dungeon.dungeon_took_sharedchest');
				}
			} elseif($object->kind == 'chest') {
				if (GuildMapObjectSession::find('down=1 AND guild_accepted_event_id=' . $player->guild_accepted_event_id . ' AND guild_id=' . $player->guild_id . ' AND guild_map_object_id=' . $object->id)) {
					$errors[] = t('guilds.errors.dungeon.dungeon_took_chest');
				}
			} else {
				$errors[] = $errors[] = t('guilds.errors.dungeon.invalid_object');
			}
		} else {
			$errors[] = $errors[] = t('guilds.errors.dungeon.invalid_object');
		}

		if (!sizeof($errors)) {
			$this->json->success = true;

			$took = new GuildMapObjectSession();
			$took->player_id						= $player->id;
			$took->guild_id					= $player->guild_id;
			$took->guild_map_object_id		= $object->id;
			$took->guild_accepted_event_id	= $player->guild_accepted_event_id;
			$took->down								= 1;
			$took->save();

			// Recompensa para o caboclo
			$object_reward  = GuildRewardMap::find_first("guild_map_objects_id=". $object->id);

			// Prêmios ( EXP )
			if ($object_reward->exp) {
				$player->exp	+= $object_reward->exp;
			}

			// Enchant Points
			if ($object_reward->enchant_points) {
				$player->enchant_points_total	+= $object_reward->quantity;
			}

			// Prêmios ( GOLD )
			if ($object_reward->currency) {
				$player->earn($object_reward->currency);

				$player->achievement_check("currency");
				$player->check_objectives("currency");
			}

			// Prêmios ( VIPS )
			if ($object_reward->credits) {
				$user->earn($object_reward->credits);

				// Verifica os créditos do jogador.
				$player->achievement_check("credits");
				$player->check_objectives("credits");
			}

			// Prêmios ( EQUIPS )
			if ($object_reward->equipment) {
				if ($object_reward->equipment == 1) {
					$dropped  = Item::generate_equipment($player);
				} elseif ($object_reward->equipment == 2) {
					$dropped  = Item::generate_equipment($player, 0);
				} elseif ($object_reward->equipment == 3) {
					$dropped  = Item::generate_equipment($player, 1);
				} elseif ($object_reward->equipment == 4) {
					$dropped  = Item::generate_equipment($player, 2);
				} elseif ($object_reward->equipment == 5) {
					$dropped  = Item::generate_equipment($player, 3);
				}

				$player->achievement_check('equipment');
				$player->check_objectives('equipment');
			}

			// Prêmios ( PETS )
			if ($object_reward->item_id && $object_reward->pets) {
				if (!$player->has_item($object_reward->item_id)) {
					$npc_pet = Item::find($object_reward->item_id);

					$player_pet				= new PlayerItem();
					$player_pet->item_id	= $npc_pet->id;
					$player_pet->player_id	= $player->id;
					$player_pet->save();

					$player->achievement_check('pets');
					$player->check_objectives('pets');
				}
			}

			// Prêmios ( ITEMS )
			if ($object_reward->item_id && !$object_reward->pets) {
				$player_item_exist			= PlayerItem::find_first("item_id=".$object_reward->item_id." AND player_id=". $player->id);
				if (!$player_item_exist) {
					$player_item			= new PlayerItem();
					$player_item->item_id	= $object_reward->item_id;
					$player_item->quantity	= $object_reward->quantity;
					$player_item->player_id	= $player->id;
					$player_item->save();
				} else {
					$player_item_exist->quantity += $object_reward->quantity;
					$player_item_exist->save();
				}

			}

			// Prêmios ( CHARACTERS )
			if ($object_reward->character_id && !$user->is_character_bought($object_reward->character_id)) {
				$reward_character				= new UserCharacter();
				$reward_character->user_id		= $player->user_id;
				$reward_character->character_id	= $object_reward->character_id;
				$reward_character->was_reward	= 1;
				$reward_character->save();

				$player->achievement_check('character');
				$player->check_objectives('character');
			}

			// Prêmios ( THEME )
			if ($object_reward->character_theme_id && !$user->is_theme_bought($object_reward->character_theme_id)) {
				$reward_theme						= new UserCharacterTheme();
				$reward_theme->user_id				= $player->user_id;
				$reward_theme->character_theme_id	= $object_reward->character_theme_id;
				$reward_theme->was_reward			= 1;
				$reward_theme->save();

				$player->achievement_check('character_theme');
				$player->check_objectives('character_theme');
			}

			// Prêmios ( TITULOS )
			if ($object_reward->headline_id && !$user->is_headline_bought($object_reward->headline_id)) {
				$reward_headline				= new UserHeadline();
				$reward_headline->user_id		= $player->user_id;
				$reward_headline->headline_id	= $object_reward->headline_id;
				$reward_headline->save();
			}

			$player->save();
			$user->save();

			$this->json->reward 	= $object_reward->name;
		} else {
			$this->json->messages	= $errors;
		}
	}
	function dungeon_fight() {
		$this->as_json			= true;
		$this->json->success	= false;

		$player		= Player::get_instance();
		$position	= $player->position();
		$errors		= [];

		$object = GuildMapObject::find_first('guild_map_id=' . $position->guild_map_id . ' AND id=' . $_POST['id']);
		if ($object) {
			if (!isset($_POST['key']) || (isset($_POST['key']) && $_POST['key'] != $_SESSION['guild_dungeon_key'])) {
				$errors[] = 'Chave de autenticação do mapa é inválida, possivelmente você abriu outra aba >:(';
			}

			if (abs($position->xpos - $object->xpos) > 2 || abs($position->ypos - $object->ypos) > 2) {
				$errors[] = 'Este alvo está muito longe';
			}

			if($player->is_pvp_queued) {
				$errors[]	= t('battles.npc.errors.pvp_queue');
			}

			if($player->at_low_stat()) {
				$errors[]	= t('battles.errors.low_stat');
			}

			if ($object->kind == 'npc') {
				if ($player->for_stamina() < NPC_EASY_COST) {
					$errors[]	= t('battles.errors.no_stamina');
				}
			} else if($object->kind == 'sharednpc') {
				if ($player->for_stamina() < NPC_EXTREME_COST) {
					$errors[]	= t('battles.errors.no_stamina');
				}
			}
		} else {
			$errors[] = 'Objeto inválido';
		}

		if (!sizeof($errors)) {
			$this->json->success = true;

			$battle = new BattleNpc();
			$npc = new NpcInstance(
				$player,
				$object->anime_id,
				null,
				$object->ability_id,
				$object->speciality_id,
				$object->pet_id,
				false,
				CharacterTheme::find_first($object->character_theme_id)->character_id,
				$object->character_theme_id,
				$object->id
			);

			if ($object->kind == 'sharednpc') {
				$session = GuildMapObjectSession::find_first('player_id=0 AND guild_accepted_event_id=' . $player->guild_accepted_event_id . ' AND guild_id=' . $player->guild_id . ' AND guild_map_object_id=' . $object->id);

				if (!$session) {
					$session = new GuildMapObjectSession();
					$session->guild_id = $player->guild_id;
					$session->guild_map_object_id = $object->id;
					$session->guild_accepted_event_id = $player->guild_accepted_event_id;
					$session->save();
				}
			}

			if (!has_chance($player->get_parsed_effects()['no_consume_stamina'])) {
				$player->less_stamina	+= ($object->kind == 'npc' ? NPC_EASY_COST : NPC_EXTREME_COST);
			}

			// Cleanups -->
				SharedStore::S('LAST_BATTLE_ITEM_OF_' . $player->id, 0);
				SharedStore::S('LAST_BATTLE_NPC_ITEM_OF_' . $player->id, 0);

				$player->clear_ability_lock();
				$player->clear_speciality_lock();
				$player->clear_technique_locks();
				$player->clear_effects();
			// <--

			$battle->player_id			= $player->id;
			$battle->battle_type_id		= $object->kind == 'npc' ? 7 : 8;
			$battle->save();

			$player->battle_npc_id		= $battle->id;
			$player->save();

			$npc->battle_npc_id			= $battle->id;
			$npc->guild_map_object_id	= $object->id;
			$npc->name					= $object->description()->name;
			$player->save_npc($npc);
		} else {
			$this->json->messages = $errors;
		}
	}
	function dungeon_invite() {
		$this->as_json			= true;
		$this->json->success	= false;

		$player			= Player::get_instance();
		$guild	= $player->guild();
		$errors			= [];

		$redis = new Redis();
		$redis->pconnect(REDIS_SERVER);
		$redis->auth(REDIS_PASS);
		$redis->select(0);

		if (isset($_POST['dungeon_id']) && is_numeric($_POST['dungeon_id'])) {
			$event = GuildEvent::find($_POST['dungeon_id']);

			if (!($unlocked = $event->unlocked($player->guild_id, $event->id, $player->id))) {
				$errors[]	= t('guilds.errors.dungeon.dungeon_not_unlocked');
			}

			$queue_id	= md5("aasg" . $unlocked->id);
		} else {
			$errors[]	= t('guilds.errors.dungeon.invalid_dungeon');
		}

		if (isset($_POST['list']) && $_POST['list']) {
			if (!sizeof($errors)) {
				$player_list = [];
				$accepts = $redis->lRange("od_accepts_" . $queue_id, 0, -1);
				$refuses = $redis->lRange("od_refuses_" . $queue_id, 0, -1);
				$invites = $redis->lRange("od_targets_" . $queue_id, 0, -1);

				foreach ($guild->players() as $guild_player) {
					if ($guild_player->player_id != $player->id) {
						$player_list[] = [
							'id'		=> $guild_player->player_id,
							'name'		=> $guild_player->player()->name,
							'accepted'	=> in_array($guild_player->player_id, $accepts),
							'refused'	=> in_array($guild_player->player_id, $refuses),
							'invited'	=> in_array($guild_player->player_id, $invites)
						];
					}
				}

				$this->json->success	= true;
				$this->json->players	= $player_list;
				$this->json->started	= $redis->get("od_id_{$queue_id}") || false;
			} else {
				$this->json->messages = $errors;
			}
		} else {
			if (!isset($_POST['players']) || (isset($_POST['players']) && !is_array($_POST['players']))) {
				$errors[]	= t('guilds.errors.dungeon.invalid_players_list');
			}

			if (!sizeof($errors)) {
				$redis->del("od_accepts_"		. $queue_id);
				$redis->del("od_refuses_"		. $queue_id);
				$redis->del("od_targets_"		. $queue_id);

				$redis->rPush("aasg_od_invites", $queue_id);

				$redis->set("od_name_"			. $queue_id, $event->description()->name);
				$redis->set("od_id_"			. $queue_id, $event->id);
				$redis->set("od_event_"			. $queue_id, $unlocked->id);
				$redis->set("od_guild_"			. $queue_id, $player->guild_id);
				$redis->set("od_needed_"		. $queue_id, $event->players_required);

				// put myself on the accept list
				$redis->rPush("od_accepts_"		. $queue_id, $player->id);

				foreach ($_POST['players'] as $target) {
					$redis->rPush("od_targets_"	. $queue_id, $target);
				}

				$this->json->success = true;
			} else {
				$this->json->messages = $errors;
			}
		}
	}
	function dungeon_accept() {
		$this->as_json			= true;
		$this->json->success	= false;
		$errors					= [];

		if (!isset($_POST['queue_id']) || (isset($_POST['queue_id']) && !$_POST['queue_id'])) {
			$errors[] = t('guilds.errors.dungeon.invalid_queue');;
		}

		if (!sizeof($errors)) {
			$player			= Player::get_instance();
			$queue_id		= $_POST['queue_id'];

			$redis = new Redis();
			$redis->pconnect(REDIS_SERVER);
			$redis->auth(REDIS_PASS);
			$redis->select(0);

			$redis->rPush("od_accepts_" . $queue_id, $player->id);

			$accepts	= $redis->lrange("od_accepts_"	. $queue_id, 0, -1);
			$needed		= $redis->get("od_needed_"		. $queue_id);
			$event_id	= $redis->get("od_event_"		. $queue_id);

			$accepted	= GuildAcceptedEvent::find($event_id);
			$event		= $accepted->guild_event();

			if (sizeof($accepts) == $needed) {
				foreach ($accepts as $player_id) {
					// Adiciona o player no evento
					$p = Player::find($player_id);
					$p->guild_accepted_event_id	= $event_id;
					$p->save();

					// Posiciona o player no mapa
					$position = $p->position();
					$position->guild_map_id		= $event->initial_map()->id;
					$position->xpos						= rand(0, 19);
					$position->ypos						= rand(0, 19);
					$position->save();
				}
				// $redis->lRem("aasg_od_invites", $queue_id, 0);

				$accepted->accepted		= 1;
				$accepted->finishes_at	= date('Y-m-d H:i:s', now() + $event->require_time);
				$accepted->save();

				$this->json->redirect = true;
			}

			$this->json->success	= true;
		} else {
			$this->json->messages	= $errors;
		}
	}
	function dungeon_refuse() {
		$this->as_json			= true;
		$this->json->success	= false;

		if (!isset($_POST['queue_id']) || (isset($_POST['queue_id']) && !$_POST['queue_id'])) {
			$errors[] = t('guilds.errors.dungeon.invalid_queue');;
		}

		if (!sizeof($errors)) {
			$player		= Player::get_instance();
			$queue_id	= $_POST['queue_id'];

			$redis = new Redis();
			$redis->pconnect(REDIS_SERVER);
			$redis->auth(REDIS_PASS);
			$redis->select(0);

			$redis->rPush("od_refuses_"	. $queue_id, $player->id);

			$this->json->success	= true;
		} else {
			$this->json->messages	= $errors;
		}
	}
	function dungeon_cancel() {
		$this->as_json			= true;
		$this->json->success	= false;

		$player	= Player::get_instance();
		$guild	= $player->guild();
		$errors	= [];

		$redis = new Redis();
		$redis->pconnect(REDIS_SERVER);
		$redis->auth(REDIS_PASS);
		$redis->select(0);

		if (isset($_POST['dungeon_id']) && is_numeric($_POST['dungeon_id'])) {
			$event = GuildEvent::find($_POST['dungeon_id']);

			if (!($unlocked = $event->unlocked($player->guild_id, $event->id, $player->id))) {
				$errors[]	= t('guilds.errors.dungeon.dungeon_not_unlocked');
			}
		} else {
			$errors[]	= t('guilds.errors.dungeon.invalid_dungeon');
		}

		if (!sizeof($errors)) {
			$queue_id	= md5("aasg" . $unlocked->id);

			// clear up memory
			$redis->del("od_accepts_"		. $queue_id);
			$redis->del("od_refuses_"		. $queue_id);
			$redis->del("od_targets_"		. $queue_id);
			$redis->del("od_name_"			. $queue_id);
			$redis->del("od_id_"			. $queue_id);
			$redis->del("od_event_"			. $queue_id);
			$redis->del("od_guild_"	. $queue_id);
			$redis->del("od_needed_"		. $queue_id);

			// remove our queue from the active queues
			$redis->lRem("aasg_od_invites", $queue_id, 0);

			$this->json->success	= true;
		} else {
			$this->json->messages	= $errors;
		}
	}
	function dungeon_start() {
		$this->as_json			= true;
		$this->json->success	= false;

		$player		= Player::get_instance();

		$redis = new Redis();
		$redis->pconnect(REDIS_SERVER);
		$redis->auth(REDIS_PASS);
	}

	function search() {
		$this->assign('player',			Player::get_instance());
		$this->assign('credits_price',	$this->credits_price);
		$this->assign('currency_price',	$this->currency_price);
		$this->assign('min_level',		$this->min_level);
	}
	function remove_all() {
		$this->as_json			= true;
		$this->json->success	= false;
		$player					= Player::get_instance();
		$errors					= [];

		$guild_requests = GuildRequest::find("guild_id=".$player->guild_id);
		if (!$guild_requests){
			$errors[]	= t('guilds.remove_error');
		}

		if (!$player->guild_id) {
			$errors[]	= t('guilds.remove_error2');
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;

			// Deleta os pedidos de amizade
			foreach($guild_requests as $guild_request){
				$guild_request->destroy();
				$guild_request->save();
			}
		} else {
			$this->json->messages	= $errors;
		}
	}
	function create() {
		$this->as_json			= true;
		$this->json->success	= false;
		$errors					= [];
		$player					= Player::get_instance();
		$user					= User::get_instance();
		$method					= isset($_POST['creation_mode']) && is_numeric($_POST['creation_mode']) ? $_POST['creation_mode'] : 0;
		$name					= isset($_POST['name']) ? $_POST['name'] : '';

		if (!$method) {
			$errors[]	= t('guilds.create.errors.invalid_method');
		} else {
			if (!between(strlen($name), 6, 20) || !preg_match(REGEX_GUILD, $name)) {
				$errors[]	= t('guilds.create.errors.invalid_name');
			}

			if ($method == 1) {
				if ($player->level < $this->min_level) {
					$errors[]	= t('guilds.create.errors.not_enough_level');
				}

				if ($player->currency < $this->currency_price) {
					$errors[]	= t('guilds.create.errors.not_enough_currency');
				}
			} else {
				if ($user->credits < $this->credits_price) {
					$errors[]	= t('guilds.create.errors.not_enough_credits');
				}
			}

			$existent	= Guild::find_first('name="' . addslashes($name) . '"');
			if ($existent) {
				$errors[]	= t('guilds.create.errors.existent');
			}
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;

			if ($method == 1) {
				$player->spend($this->currency_price);
			} else {
				$user->spend($this->credits_price);
			}

			// Cria a organização
			$guild					= new Guild();
			$guild->player_id		= $player->id;
			$guild->creation_type	= $method == 1 ? 1 : 2;
			$guild->name			= htmlspecialchars($name);
			$guild->faction_id		= $player->faction_id;
			$guild->save();

			// Adiciona o player na organização
			$player->guild_id		= $guild->id;
			$player->save();
			$player->_update_sum_attributes();

			// Atualiza a tabela position do player
			$position = $player->position();
			$position->guild_id	= $guild->id;
			$position->save();

			// Adiciona o contador de quests da organização
			$guild_quest_counters					= new GuildQuestCounter();
			$guild_quest_counters->guild_id	= $guild->id;
			$guild_quest_counters->save();
		} else {
			$this->json->messages	= $errors;
		}
	}

	function make_list() {
		$this->layout	= false;
		$page			= isset($_POST['page']) && is_numeric($_POST['page']) ? $_POST['page'] : 0;
		$limit			= 100;
		$player			= Player::get_instance();
		$filter			= ' AND faction_id=' . $player->faction_id;

		if (isset($_POST['name']) && $_POST['name']) {
			$filter	.= ' AND name LIKE "%' . addslashes($_POST['name']) . '%"';
		}

		$guilds	= (new Guild)->filter($filter, $page, $limit);

		$this->assign('guilds', $guilds['guilds']);
		$this->assign('pages', $guilds['pages']);
		$this->assign('player', $player);
	}

	function enter($id = null) {
		$this->as_json			= true;
		$this->json->success	= false;
		$errors					= [];
		$player					= Player::get_instance();

		if (is_numeric($id)) {
			$guild	= Guild::find($id);
			if ($guild->member_count >= $this->max_players) {
				$errors[]	= t('guilds.create.errors.full');
			}

			// Não deixa entrar 2 jogadores da mesma conta em uma organização
			$players_guilds = Player::find("guild_id=". $id ." AND user_id=". $player->user_id);
			if ($players_guilds) {
				$errors[]	= t('guilds.create.errors.users');
			}

			if ($guild && $guild->faction_id == $player->faction_id) {
				$already	= GuildRequest::find_first('player_id=' . $player->id . ' AND guild_id=' . $id);
				if ($already) {
					$errors[]	= t('guilds.enter.errors.already');
				}
			} else {
				$errors[]	= t('guilds.enter.errors.invalid');
			}
		} else {
			$errors[]	= t('guilds.enter.errors.invalid');
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;

			$request				= new GuildRequest();
			$request->guild_id		= $id;
			$request->player_id		= $player->id;
			$request->save();
		} else {
			$this->json->messages	= $errors;
		}
	}

	function enter_accept() {
		$this->_enter_or_refuse();
	}

	function enter_refuse() {
		$this->_enter_or_refuse(true);
	}

	private function _enter_or_refuse($is_refuse = false) {
		$this->as_json			= true;
		$this->json->success	= false;

		$player					= Player::get_instance();
		$guild			= $player->guild();
		$errors					= [];

		if (isset($_POST['id']) && is_numeric($_POST['id'])) {
			$accept = $guild->can_accept_player($player->id, $_POST['id']);
		}

		if (!$is_refuse) {
			// Não deixa entrar 2 jogadores da mesma conta em uma organização
			$guild_request		   = GuildRequest::find_first($_POST['id']);
			$player_pedido		   		   = Player::find_first($guild_request->player_id);

			$players_guilds = Player::find("guild_id=". $guild_request->guild_id ." AND user_id=". $player_pedido->user_id);
			if ($players_guilds) {
				$errors[]	= t('guilds.create.errors.users');
			}
		}

		if (!$accept->allowed) {
			$errors	= array_merge($errors, $accept->messages);
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;
			$request				= $guild->request($_POST['id']);
			$target					= $request->player();

			$pm	= new PrivateMessage();
			$pm->to_id	= $target->id;
			$pm->subject	= t('guilds.show.request_message_title');

			if ($is_refuse) {
				$pm->content	= t('guilds.show.refuse_message', ['name' => $guild->name]) . "<hr />" . htmlspecialchars($_POST['reason']);
			} else {
				$pm->content	= t('guilds.show.accept_message', ['name' => $guild->name]);

				$guild_player				= new GuildPlayer();
				$guild_player->guild_id		= $guild->id;
				$guild_player->player_id	= $target->id;
				$guild_player->save();

				// Adiciona o player na organização
				$target->guild_id			= $guild->id;
				$target->save();
				$target->_update_sum_attributes();

				// Atualiza a position do player
				$position = $target->position();
				$position->guild_id			= $guild->id;
				$position->save();
			}

			$pm->save();
			$request->destroy();
			$guild->fix_member_count();
		} else {
			$this->json->messages	= $errors;
		}
	}

	function leave() {
		$player					= Player::get_instance();
		$guild			= $player->guild();
		$this->as_json			= true;
		$this->json->success	= false;
		$errors					= [];

		$can_kick	= $guild->can_kick_player($guild->player_id, $player->id);

		/*if ($guild->player_id != $player->id) {
			$errors[]	= t('guilds.errors.not_leader');
		}*/
		if (!$can_kick->allowed) {
			$errors	= array_merge($errors, $can_kick->messages);
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;

			$pm				= new PrivateMessage();
			$pm->to_id		= $guild->player_id;
			$pm->subject	= t('guilds.kick_leave.leave_message_title');
			$pm->content	= t('guilds.kick_leave.leave_message', ['name' => $player->name]);
			$pm->save();

			$guild->player($player->id)->destroy();

			$player->guild_id	= 0;
			$player->save();
			$player->_update_sum_attributes();

			// Atualiza a position do player
			$position = $player->position();
			$position->guild_id	= 0;
			$position->save();

			$guild->fix_member_count();
		} else {
			$this->json->messages	= $errors;
		}
	}

	function destroy() {
		$player					= Player::get_instance();
		$guild					= $player->guild();
		$this->as_json			= true;
		$this->json->success	= false;
		$errors					= [];

		if ($guild->player_id != $player->id) {
			$errors[]	= t('guilds.errors.not_leader');
		}

		if ($guild->member_count > 1) {
			$errors[]	= t('guilds.errors.still_have_members');
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;

			foreach ($guild->requests() as $request) {
				$request->destroy();
			}

			$guild->destroy();

			$player->guild_id	= 0;
			$player->save();
			$player->_update_sum_attributes();

			// Atualiza a position do player
			$position = $player->position();
			$position->guild_id	= 0;
			$position->save();
		} else {
			$this->json->messages	= $errors;
		}
	}

	function kick() {
		$player					= Player::get_instance();
		$guild			= $player->guild();
		$this->as_json			= true;
		$this->json->success	= false;
		$errors					= [];

		if (isset($_POST['id']) && is_numeric($_POST['id'])) {

			$target					= $guild->player($_POST['id']);
			$target_player			= $target->player();

			if($target_player->battle_pvp_id){
				$errors[]	= t('guilds.kick_leave.errors.battle');
			}

			$can_kick	= $guild->can_kick_player($player->id, $_POST['id']);

			if (!$can_kick->allowed) {
				$errors	= array_merge($errors, $can_kick->messages);
			}


		} else {
			$errors[]	= t('guilds.kick_leave.errors.invalid');
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;


			$pm				= new PrivateMessage();
			$pm->to_id		= $target_player->id;
			$pm->subject	= t('guilds.kick_leave.kick_message_title');
			$pm->content	= t('guilds.kick_leave.kick_message', ['name' => $guild->name]) . "<hr />" . htmlspecialchars($_POST['reason']);
			$pm->save();

			$target->destroy();

			$target_player->guild_id	= 0;
			$target_player->save();
			$target_player->_update_sum_attributes();

			// Atualiza a position do player
			$position = $target_player->position();
			$position->guild_id		= 0;
			$position->save();

			$guild->fix_member_count();
		} else {
			$this->json->messages	= $errors;
		}
	}

	function update_acl() {
		$player					= Player::get_instance();
		$guild			= $player->guild();
		$this->as_json			= true;
		$this->json->success	= false;
		$errors					= [];

		if ($player->id != $guild->player_id) {
			$errors[]	= t('guilds.errors.no_privilege');
		} else {
			if (isset($_POST['id']) && is_numeric($_POST['id'])) {
				$target	= $guild->player($_POST['id']);

				if (isset($_POST['accept']) && is_numeric($_POST['accept'])) {
					$target->can_accept_players	= $_POST['accept'];
				}

				if (isset($_POST['kick']) && is_numeric($_POST['kick'])) {
					$target->can_kick_players	= $_POST['kick'];
				}

				$target->save();
			}
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;
		} else {
			$this->json->messages	= $errors;
		}
	}
	function treasure(){
		$player				= Player::get_instance();
		$total_treasure		= Guild::find_first("id=". $player->guild_id);
		$can_accept			= $total_treasure->can_accept_player($player->id)->allowed;

		$this->assign('total_treasure',$total_treasure);
		$this->assign('player',$player);
		$this->assign('can_accept',$can_accept);
		$this->assign('treasure_list', Recordset::query('
			SELECT
				a.*,
				COUNT(b.id) AS total

			FROM
				treasure_rewards a LEFT JOIN player_treasure_logs b ON b.treasure_reward_id=a.id AND b.player_id=' . $player->id . '


			GROUP BY a.id
		'));
	}
	function treasures_change(){
		$player					= Player::get_instance();
		$guild			= Guild::find_first("id=". $player->guild_id);
		$players_orgs			= Player::find("guild_id=". $player->guild_id);
		$this->as_json			= true;
		$this->json->success	= false;
		$errors					= [];


		if (!isset($_POST['mode']) || (isset($_POST['mode']) && !is_numeric($_POST['mode']))) {
			$errors[]	= t('treasure.error1');
		} else {
			$treasure = TreasureReward::find_first("id =". $_POST['mode']);

			if($guild->treasure_atual < $treasure->treasure_total){
				$errors[]	= t('treasure.error2');
			}

			if(!sizeof($errors)) {
				$guild->treasure_atual -= $treasure->treasure_total;
				$guild->save();

				foreach ($players_orgs as $players_org):
					$p = Player::find_first("id=". $players_org->id);
					$user = User::find_first("id=". $players_org->user_id);

					// Prêmios ( EXP )
					if ($treasure->exp) {
						$p->exp	+= $treasure->exp;
					}

					// Enchant Points
					if ($treasure->enchant_points) {
						$p->enchant_points_total	+= $treasure->quantity;
					}

					// Prêmios ( GOLD )
					if ($treasure->currency) {
						$p->earn($treasure->currency);
					}

					// Prêmios ( CRÉDITOS )
					if($treasure->credits) {
						$user->earn($treasure->credits);

						// Verifica os créditos do jogador.
						$p->achievement_check("credits");
						$p->check_objectives("credits");
					}

					// Prêmios ( EQUIPS )
					if ($treasure->equipment) {
						if ($treasure->equipment == 1){
							$dropped  = Item::generate_equipment($p);
						} elseif ($treasure->equipment == 2) {
							$dropped  = Item::generate_equipment($p, 0);
						} elseif ($treasure->equipment == 3) {
							$dropped  = Item::generate_equipment($p, 1);
						} elseif ($treasure->equipment == 4) {
							$dropped  = Item::generate_equipment($p, 2);
						} elseif ($treasure->equipment == 5) {
							$dropped  = Item::generate_equipment($p, 3);
						}
					}

					// Prêmios ( PETS )
					if ($treasure->item_id && $treasure->pets) {
						$npc_pet = Item::find($treasure->item_id);

						$player_pet				= new PlayerItem();
						$player_pet->item_id	= $npc_pet->id;
						$player_pet->player_id	= $p->id;
						$player_pet->save();
					}

					// Prêmios ( ITEMS )
					if ($treasure->item_id && !$treasure->pets) {
						$player_item_exist			= PlayerItem::find_first("item_id=".$treasure->item_id." AND player_id=". $p->id);
						if(!$player_item_exist){
							$player_item			= new PlayerItem();
							$player_item->item_id	= $treasure->item_id;
							$player_item->quantity	= $treasure->quantity;
							$player_item->player_id	= $p->id;
							$player_item->save();
						}else{
							$player_item_exist->quantity += $treasure->quantity;
							$player_item_exist->save();
						}

						/*if ($reward_item_instance->item_type_id == 1) {
							$player_item->removed	= 1;
						}*/

					}

					// Prêmios ( CHARACTERS )
					if ($treasure->character_id) {
						$reward_character				= new UserCharacter();
						$reward_character->user_id		= $p->user_id;
						$reward_character->character_id	= $treasure->character_id;
						$reward_character->was_reward	= 1;
						$reward_character->save();
					}

					// Prêmios ( THEME )
					if ($treasure->character_theme_id) {
						$reward_theme						= new UserCharacterTheme();
						$reward_theme->user_id				= $p->user_id;
						$reward_theme->character_theme_id	= $treasure->character_theme_id;
						$reward_theme->was_reward			= 1;
						$reward_theme->save();
					}

					// Prêmios ( TITULOS )
					if ($treasure->headline_id) {
						$reward_headline				= new UserHeadline();
						$reward_headline->user_id		= $p->user_id;
						$reward_headline->headline_id	= $treasure->headline_id;
						$reward_headline->save();
					}

					// Adiciona no Log
					$log						= new PlayerTreasureLog();
					$log->player_id				= $p->id;
					$log->treasure_reward_id	= $treasure->id;
					$log->guild_id		= $player->guild_id;
					$log->save();

					// Manda Mensagem para os integrantes
					$pm				= new PrivateMessage();
					$pm->from_id	= $guild->player_id;
					$pm->to_id		= $p->id;
					$pm->subject	= $treasure->name;
					$pm->content	= $treasure->name;
					if ($treasure->enchant_points) {
						$pm->content	= t('treasure.show.desc') ." ". $treasure->quantity ." ". t('treasure.show.enchant');
					}
					if ($treasure->exp) {
						$pm->content	= t('treasure.show.desc') ." ". $treasure->exp ." ". t('treasure.show.exp');
					}
					if ($treasure->currency) {
						$pm->content	= t('treasure.show.desc') ." ". $treasure->currency ." ". t('currencies.' . $player->character()->anime_id);
					}
					if ($treasure->credits) {
						$pm->content	= t('treasure.show.desc') ." ". $treasure->credits ." ". t('treasure.show.credits');
					}
					if ($treasure->equipment && $treasure->equipment == 1) {
						$pm->content	= t('treasure.show.desc').": ". t('treasure.show.equipment1');
					}
					if ($treasure->equipment && $treasure->equipment == 2) {
						$pm->content	= t('treasure.show.desc').": ". t('treasure.show.equipment2');
					}
					if ($treasure->equipment && $treasure->equipment == 3) {
						$pm->content	= t('treasure.show.desc').": ". t('treasure.show.equipment3');
					}
					if ($treasure->equipment && $treasure->equipment == 4) {
						$pm->content	= t('treasure.show.desc').": ". t('treasure.show.equipment4');
					}
					if ($treasure->pets  && $treasure->item_id) {
						$pm->content	= t('treasure.show.desc').": ". t('treasure.show.pet')." ". Item::find($treasure->item_id)->description()->name;
					}
					if ($treasure->character_theme_id) {
						$pm->content = t('treasure.show.desc').": ". t('treasure.show.theme')." ". CharacterTheme::find($treasure->character_theme_id)->description()->name;
					}
					if ($treasure->character_id) {
						$pm->content = t('treasure.show.desc').": ". t('treasure.show.character')." ". Character::find($treasure->character_id)->description()->name;
					}
					if ($treasure->headline_id) {
						$pm->content = t('treasure.show.desc').": ". t('treasure.show.headline')." ". Headline::find($treasure->headline_id)->description()->name;
					}
					if (!$treasure->pets && $treasure->item_id) {
						$reward	= Item::find($treasure->item_id);
						$reward->set_anime($p->character()->anime_id);
						$pm->content = t('treasure.show.desc').": ". $treasure->quantity ." ". $reward->description()->name;
					}
					$pm->save();

					$p->save();
					$user->save();
				endforeach;

				$this->json->success	= true;
			} else {
				$this->json->messages	= $errors;
			}
		}
	}

	function show($id = null) {
		if (isset($_POST['popup'])) {
			$this->layout	= false;
		}

		$player			= Player::get_instance();

		// Verifica se você tem organização - Conquista
		$player->achievement_check("guild");
		$player->check_objectives("guild");

		$errors			= [];
		$upload_error	= false;
		$got_upload		= false;

		if (!$id && ($_POST || $_FILES)) {
			$guild	= $player->guild();
			if ($guild->player_id == $player->id) {
				if (isset($_POST['name']) && preg_match(REGEX_GUILD, $_POST['name'])) {
					$other	= Guild::find_first('id != ' . $guild->id . ' AND name="' . addslashes($_POST['name']) . '"');
					if ($other) {
						$errors[]	= t('guilds.show.errors.existent');
					}
				} else {
					$errors[]	= t('guilds.show.errors.invalid');
				}

				if (!$_FILES['cover']['error']) {
					$got_upload	= true;
					$file		= $_FILES['cover'];
					$mime 		= [
						"image/jpeg",
						"image/png",
						"image/gif"
					];

					if (!in_array(image_type_to_mime_type(exif_imagetype($file['tmp_name'])), $mime)) {
						$upload_error = true;
					}

					if (!in_array( strtolower(substr($file['name'], -3, 3)), ['jpg', 'png', 'gif'])) {
						$upload_error = true;
					}

					if (!$upload_error) {
						$sz = getimagesize($file['tmp_name']);
						if ($sz['0'] > 663 || $sz['1'] > 166) {
							$upload_error = true;
						}
					}
				}

				if ($got_upload && $upload_error) {
					$errors[]	= t('guilds.show.errors.invalid_image');
				}

				if (!sizeof($errors)) {
					$guild->name		= htmlspecialchars($_POST['name']);
					$guild->description	= htmlspecialchars($_POST['description']);

					if ($got_upload) {
						$path	= ROOT . '/uploads/guilds/';
						$name	= md5($guild->id . $file['tmp_name']) . '.' . strtolower(substr($file['name'], -3, 3));

						if ($guild->cover_file) {
							@unlink($path . '/' . $guild->cover_file);
						}

						$guild->cover_file	= $name;

						move_uploaded_file($file['tmp_name'], $path . '/' . $name);
					}

					$guild->save();
				}
			}
		}

		$guild	= Guild::find(is_numeric($id) ? $id : $player->guild_id);
		$rank_org		= RankingGuild::find_first('guild_id='.$guild->id);
		$daily_org		= GuildQuestCounter::find_first('guild_id='.$guild->id);

		if ($guild) {
			$can_kick	= $guild->can_kick_player($player->id)->allowed;
			$can_accept	= $guild->can_accept_player($player->id)->allowed;

			$this->assign('guild', $guild);
			$this->assign('rank_org', $rank_org);
			$this->assign('daily_org', $daily_org);
			$this->assign('leader', $guild->leader());
			$this->assign('is_leader', $guild->player_id == $player->id);
			$this->assign('players', $guild->players());
			$this->assign('requests', $guild->requests());
			$this->assign('can_kick', $can_kick);
			$this->assign('can_accept', $can_accept);
			$this->assign('player', $player);
			$this->assign('errors', $errors);
		} else {
			$this->render	= 'show_error';
		}
	}
}
