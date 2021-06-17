<?php
	class HistoryModeController extends Controller {
		function index() {
			$player	= Player::get_instance();
			$groups	= HistoryModeGroup::find($_SESSION['universal'] ? '1=1' : 'active=1', ['cache' => true]);

			$this->assign('player', $player);
			$this->assign('groups', $groups);
		}

		function show($id = null) {
			if (!$id || ($id && !is_numeric($id))) {
				$this->render	= 'show_invalid';
			} else {
				$player	= Player::get_instance();
				$group	= HistoryModeGroup::find($id);
				$group->set_player($player);

				if (!$group->unlocked()) {
					$this->render	= 'show_denied';
				} else {
					$this->assign('group', $group);
					$this->assign('subgroups', $group->subgroups());
					$this->assign('player', $player);
				}
			}
		}

		function unlock() {
			$this->as_json			= true;
			$this->json->success	= false;
			$errors					= [];

			if (!isset($_POST['group']) || (isset($_POST['group']) && !is_numeric($_POST['group']))) {
				$errors[]	= t('history_mode.unlock.errors.invald');
			} else {
				$player	= Player::get_instance();
				$group	= HistoryModeGroup::find($_POST['group']);

				if (!$_SESSION['universal']) {
					if (!$group->active) {
						$group	= false;
					}
				}

				if (!$group) {
					$errors[]	= t('history_mode.unlock.errors.invald');
				} else {
					if ($_POST['mode'] == 1 && $player->currency < $group->currency_cost) {
						$errors[]	= t('history_mode.unlock.errors.not_enough_currency');
					} elseif ($_POST['mode'] != 1 && $player->user()->credits < $group->credits_cost) {
						$errors[]	= t('history_mode.unlock.errors.not_enough_credits');
					}
				}
			}

			if (!sizeof($errors)) {
				$this->json->success	= true;

				if ($_POST['mode'] == 1) {
					$player->spend($group->currency_cost);
				} else {
					$player->user()->spend($group->credits_cost);
				}

				$user_group							= new UserHistoryModeGroup();
				$user_group->user_id				= $player->user_id;
				$user_group->history_mode_group_id	= $group->id;
				$user_group->save();
			} else {
				$this->json->messages	= $errors;
			}
		}

		function accept() {
			$this->as_json			= true;
			$this->json->success	= false;
			$errors					= [];

			if (!isset($_POST['npc']) || (isset($_POST['npc']) && !is_numeric($_POST['npc']))) {
				$errors[]	= t('history_mode.unlock.accept.invald');
			} else {
				$player	= Player::get_instance();
				$npc	= HistoryModeNpc::find($_POST['npc']);
				$npc->set_player($player);

				if ($npc) {
					$group	= $npc->subgroup()->group();
					$group->set_player($player);

					if (!$group->unlocked()) {
						$errors[]	= t('history_mode.accept.errors.locked');
					} else {
						if (!$npc->can_battle()) {
							$errors[]	= t('history_mode.accept.errors.requirements');
						}

						if ($player->for_stamina() < $npc->stamina_cost) {
							$errors[]	= t('history_mode.accept.errors.not_enough_stamina');
						}
					}
				} else {
					$errors[]	= t('history_mode.unlock.accept.invald');
				}
			}

			if ($player->is_pvp_queued) {
				$errors[]	= t('history_mode.accept.errors.pvp_queue');
			}

			if (!sizeof($errors)) {
				$this->json->success	= true;

				// Cleanups -->
					SharedStore::S('last_battle_item_of_' . $player->id, 0);
					SharedStore::S('last_battle_npc_item_of_' . $player->id, 0);

					$player->clear_ability_lock();
					$player->clear_speciality_lock();
					$player->clear_technique_locks();
					$player->clear_effects();
				// <--

				$_SESSION['history_mode']	= $group->id;

				$battle					= new BattleNpc();
				$battle->player_id		= $player->id;
				$battle->battle_type_id	= 9;
				$battle->save();

				$player->battle_npc_id	= $battle->id;
				$player->less_stamina	+= $npc->stamina_cost;
				$player->save();

				$theme_ids							= $npc->character_theme_ids ? explode(',', $npc->character_theme_ids) : null;

				$instance							= new NpcInstance($player, $npc->anime_id, $theme_ids, $npc->ability_id, $npc->speciality_id, $npc->pet_id);
				$instance->specific_id				= $npc->id;
				$instance->name						= $npc->description()->name;
				$instance->specific_image			= true;
				$instance->battle_npc_id			= $battle->id;
				$player->save_npc($instance);
			} else {
				$this->json->messages	= $errors;
			}
		}
	}
