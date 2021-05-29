<?php
class QuestsController extends Controller {
	private	$quests	= [];

	function daily(){
		$player	= Player::get_instance();
		$buy_mode_change 	= PlayerChange::find_first("player_id=" . $player->id);

		$this->assign('player', $player);
		$this->assign('quests', $player->daily_quests());
		$this->assign('buy_mode_change', $buy_mode_change);
		$this->_generate_daily_quest_list($player);
		$this->assign('player_tutorial', $player->player_tutorial());
	}
	function battle(){
		$player	= Player::get_instance();

		//Verifica a conquista da missão de combate
		$player->achievement_check("battle_quests");

		$this->assign('player',			$player);
		$this->assign('quests',			PlayerCombatQuest::find("finished=0 ORDER BY id ASC"));
		$this->assign('finish_quests',	PlayerCombatQuest::find("finished=1 GROUP BY period"));
	}
	function account(){
		$user				= User::get_instance();
		$player				= Player::get_instance();
		$buy_mode_change 	= UserChange::find_first("user_id=" . $user->id);

		$this->assign('user', $user);
		$this->assign('player', $player);
		$this->assign('quests', $user->account_quests());
		$this->assign('buy_mode_change', $buy_mode_change);
		$this->_generate_account_quest_list($user);
		$this->assign('player_tutorial', $player->player_tutorial());
	}
	function pet_remove(){
		$player			= Player::get_instance();

		$this->as_json			= true;
		$this->render			= false;
		$this->json->success	= false;
		$errors					= array();

		if(is_numeric($_POST['quest_id']) && is_numeric($_POST['counter'])) {
			$pet_id 		= 'pet_id_'.$_POST['counter'];

			$pet_quest		= PlayerPetQuest::find_first('completed = 0 AND pet_quest_id='.$_POST['quest_id'].' AND player_id= '. $player->id);
			$player_item	= PlayerItem::find_first('item_id='.$pet_quest->{$pet_id}.' AND player_id= '. $player->id);

			if(!$player_item) {
				$errors[]	= t('character.status.change_image.errors.invalid');
			}
		} else {
			$errors[]	= t('character.status.change_image.errors.invalid');
		}
		if(!sizeof($errors)) {
			$this->json->success  = true;

			//Retira o PET do trabalho na player item
			$player_item->working = 0;
			$player_item->save();

			// Remove o pet da missão na player quest pet
			$pet_quest->{$pet_id} = 0;
			$pet_quest->save();

			//Recalcula o percentual da missão
			$player->quest_pet_calc_success($_POST['quest_id']);

		}else{
			$this->json->errors	= $errors;
		}

	}
	function list_pets() {
		$this->layout	= false;
		$player			= Player::get_instance();

		if($_POST) {
			$this->as_json			= true;
			$this->render			= false;
			$this->json->success	= false;
			$errors					= array();

			if(is_numeric($_POST['id']) && is_numeric($_POST['quest_id']) && is_numeric($_POST['counter'])) {
				$player_item		= PlayerItem::find_first('item_id='.$_POST['id'].' AND player_id= '. $player->id);
				$pet_quest			= PlayerPetQuest::find_first('completed = 0 AND pet_quest_id='.$_POST['quest_id'].' AND player_id= '. $player->id);
				$player_pet_quest	= PlayerPetQuest::find("(pet_id_1=".$_POST['id']." || pet_id_2=".$_POST['id']." || pet_id_3=".$_POST['id'].") and player_id=".$player->id." AND completed=0");

				if($player_pet_quest){
					$errors[]	= "Esse Mascote já esta em outra missão!";
				}
				if($pet_quest->finish_at){
					$errors[]	= "Você não pode trocar o mascote dessa missão!";
				}
				if(!$player_item) {
					$errors[]	= t('character.status.change_image.errors.invalid');
				}
			} else {
				$errors[]	= t('character.status.change_image.errors.invalid');
			}

			if(!sizeof($errors)) {
				$this->json->success  = true;

				// Adiciona o Pet no Trabalho
				if($_POST['counter']==1){
					$pet_quest->pet_id_1 = $_POST['id'];
				}else if($_POST['counter']==2){
					$pet_quest->pet_id_2 = $_POST['id'];
				}else if($_POST['counter']==3){
					$pet_quest->pet_id_3 = $_POST['id'];
				}
				$pet_quest->save();

				//Pets Trabalhando de Verdade
				$pets_working = Recordset::query('select GROUP_CONCAT(pet_id_1,",",pet_id_2,",",pet_id_3) as ids from player_pet_quests WHERE  player_id='. $player->id.' AND completed=0')->result_array();
				$pets_items_no_working = PlayerItem::find('item_id not in ('.$pets_working[0]['ids'].') AND working = 1 AND player_id='. $player->id);

				foreach($pets_items_no_working as $pet_item_no_working){
					$pet_item_no_working->working = 0;
					$pet_item_no_working->save();
				}

				// Adiciona o Pet para Trabalhar
				$player_item->working = 1;
				$player_item->save();



			} else {
				$this->json->errors	= $errors;
			}
		} else {
			if(is_numeric($_GET['quest_id'])) {
				$this->assign('quest_id', $_GET['quest_id']);
				$this->assign('counter', $_GET['counter']);
			}else{
				$errors[]	= t('character.status.change_image.errors.invalid');
			}
			$this->assign('pets', $player->your_pets());
			$this->assign('player', $player);
		}
	}
	function organization_daily(){
		$player				= Player::get_instance();
		$total_treasure		= Organization::find_first("id=". $player->organization_id);
		$can_accept			= $total_treasure->can_accept_player($player->id)->allowed;
		$buy_mode_change 	= PlayerChange::find_first("player_id=" . $player->id);

		$this->assign('buy_mode_change', $buy_mode_change);
		$this->assign('can_accept',$can_accept);
		$this->assign('player', $player);
		$this->assign('total_treasure', $total_treasure);
		$this->assign('quests', $player->organization_daily_quests());
		$this->_generate_organization_daily_quest_list($player);


	}
	function time() {
		$player	= Player::get_instance();

		$this->assign('player',				$player);
		$this->assign('extras',				$player->attributes());
		$this->assign('effects',			$player->get_parsed_effects());
		$this->assign('graduations',		$player->character()->anime()->graduations());
		$this->assign('quests',				$player->character()->anime()->time_quests());
		$this->assign('player_tutorial',	$player->player_tutorial());

		$this->_generate_time_quest_list($player);
	}
	function pet() {
		$player	= Player::get_instance();

		$this->assign('player', $player);
		$this->assign('quests', $player->pet_quests());
		$this->_generate_pet_quest_list($player);
		$this->assign('player_tutorial', $player->player_tutorial());
	}

	function time_accept() {
		$this->as_json			= TRUE;
		$this->json->success	= FALSE;
		$this->json->messages	= [];

		$player					= Player::get_instance();
		$errors					= [];

		$this->_generate_time_quest_list($player);

		if (isset($_POST['quest']) && is_numeric($_POST['quest']) && isset($_POST['duration']) && is_numeric($_POST['duration']) && $_POST['duration'] > 0) {
			$quest	= TimeQuest::find($_POST['quest']);

			if (!$quest || ($quest->anime_id && $quest->anime_id != $player->character()->anime_id))
				$errors[]	= t('quests.time.errors.invalid');
			else {
				$duration	= $quest->duration($_POST['duration']);

				if ($_POST['duration'] > $quest->durations)
					$errors[]	= t('quests.time.errors.durations');
				if (in_array($quest->id, $this->quests))
					$errors[]	= t('quests.time.errors.finished');
				if ($quest->req_level > $player->level)
					$errors[]	= t('quests.time.errors.level');
				// if ($quest->req_graduation_sorting > $player->graduation()->sorting)
				// 	$errors[]	= t('quests.time.errors.graduation');
			}
		} else {
			$errors[]	= t('quests.time.errors.invalid');
		}

		if ($player->is_pvp_queued) {
			$errors[]	= t('quests.time.errors.pvp_queue');
		}

		if (!sizeof($errors)) {
			$this->json->success		= TRUE;
			$player->time_quest_id		= $quest->id;
			$player->save();

			$player_quest					= new PlayerTimeQuest();
			$player_quest->time_quest_id	= $quest->id;
			$player_quest->player_id		= $player->id;
			$player_quest->duration			= $_POST['duration'];
			$player_quest->finish_at		= date('Y-m-d H:i:s', now() + $duration->total_time);

			$this->_add_reward($quest, $player, $player_quest);

			$player_quest->save();
		} else
			$this->json->messages	= $errors;
	}
	function pet_accept() {
		$this->as_json			= TRUE;
		$this->json->success	= FALSE;
		$this->json->messages	= [];

		$player					= Player::get_instance();
		$errors					= [];

		$this->_generate_pet_quest_list($player);

		if(isset($_POST['quest']) && is_numeric($_POST['quest'])) {
			$quest	= PetQuest::find($_POST['quest']);

			if(!$quest) {
				$errors[]	= t('quests.time.errors.invalid');
			} else {
				$duration	= $quest->duration($quest->durations);

				if(!in_array($quest->id, $this->quests)) {
					$errors[]	= t('quests.time.errors.finished');
				}
			}
		} else {
			$errors[]	= t('quests.time.errors.invalid');
		}

		if (!sizeof($errors)) {
			$this->json->success				= TRUE;

			$player_pet_quest					= PlayerPetQuest::find_first('completed = 0  AND pet_quest_id='.$quest->id.' AND player_id='.$player->id);
			$player_pet_quest->duration			= $quest->durations;
			$player_pet_quest->finish_at		= date('Y-m-d H:i:s', now() + $duration->total_time);
			$player_pet_quest->save();

		} else
			$this->json->messages	= $errors;
	}
	function time_wait() {
		$player			= Player::get_instance();
		$quest			= $player->character()->anime()->time_quest($player->time_quest_id);
		if (!$quest) {
			$player->time_quest_id = 0;
			$player->save();

			redirect_to('characters#status');
		}

		$player_quest	= $player->player_time_quest($player->time_quest_id);
		$can_finish		= now() >= strtotime($player_quest->finish_at);
		$diff			= get_time_difference(now(), strtotime($player_quest->finish_at));
		$duration		= $quest->duration($player_quest->duration);

		$this->assign('player',			$player);
		$this->assign('quest',			$player->character()->anime()->time_quest($player->time_quest_id));
		$this->assign('player_quest',	$player_quest);
		$this->assign('can_finish',		$can_finish);
		$this->assign('diff',			$diff);
		$this->assign('duration',		$duration);
		$this->assign('extras',			$player->attributes());
		$this->assign('effects',		$player->get_parsed_effects());
	}
	function time_cancel() {
		$this->layout   = FALSE;
		$this->render   = FALSE;

		$player			= Player::get_instance();
		$quest			= $player->character()->anime()->time_quest($player->time_quest_id);
		$player_quest	= $player->player_time_quest($player->time_quest_id);

		$player->time_quest_id	= 0;
		$player->save();

		$player_quest->destroy();
	}
	function organization_daily_change(){
		$player					= Player::get_instance();
		$user					= User::get_instance();
		$this->as_json			= true;
		$this->json->success	= false;
		$this->json->messages	= [];
		$errors					= [];
		$buy_change				= 0;
		$valor_change			= 0;
		$organizations			= 0;
		$personagens			= 0;

		if(isset($_POST['id']) && is_numeric($_POST['id']) && isset($_POST['quest']) && is_numeric($_POST['quest'])) {
			$daily				= DailyQuest::find($_POST['quest']);

			if(!$daily) {
				$errors[]	= t('quests.time.errors.invalid');
			} else {
				$player_daily 		= OrganizationDailyQuest::find($_POST['id']);
				$buy_mode_change 	= PlayerChange::find_first("player_id=" . $player->id);

				if($player_daily->complete==1){
					$errors[]	= t('quests.time.errors.invalid');
				}
				if($buy_mode_change){
					if($buy_mode_change->weekly == 0){
						$buy_change = 0;
					}elseif($buy_mode_change->weekly > 0 && $buy_mode_change->weekly < 5){

						$valor_change = $buy_mode_change->weekly * 500;

						if ($player->currency < $valor_change) {
							$errors[]	= t("quests.time.errors.not_enough_currency");
						}else{
							$buy_change = 1;
						}

					}elseif($buy_mode_change->weekly > 4){

						if($buy_mode_change->weekly > 4   && $buy_mode_change->weekly < 10){
							$valor_change = 1;
						}elseif($buy_mode_change->weekly > 9  && $buy_mode_change->weekly < 15){
							$valor_change = 2;
						}elseif($buy_mode_change->weekly > 14  && $buy_mode_change->weekly < 20){
							$valor_change = 3;
						}elseif($buy_mode_change->weekly > 20){
							$valor_change = 5;
						}

						if ($user->credits < $valor_change) {
							$errors[]	= t("quests.time.errors.not_enough_credits");
						}else{
							$buy_change = 2;
						}

					}
				}else{
					$player_change				 = new PlayerChange();
					$player_change->player_id 	 = $player->id;
					$player_change->save();
				}
			}
		} else {
			$errors[]	= t('quests.time.errors.invalid');
		}

		if(!sizeof($errors)) {
			$this->json->success		= true;

			// Desconta o valor do player
			if ($buy_change == 1) {
				$player->spend($valor_change);
			} elseif($buy_change == 2) {
				$user->spend($valor_change);
			}

			// Atualiza o contador de troca das missões diarias
			if(!$buy_mode_change){
				$buy_mode_change 	= PlayerChange::find_first("player_id=" . $player->id);
			}
			$buy_mode_change->weekly++;
			$buy_mode_change->save();

			//Deleta a missão do player
			$player_daily_del = Recordset::query("DELETE FROM organization_daily_quests WHERE id=". $_POST['id']." AND organization_id=".$player->organization_id);

			//Adiciona uma nova missão para o player
			$daily_quests			= Recordset::query('SELECT * FROM daily_quests WHERE of="organization" ORDER BY RAND() LIMIT 1')->row_array();

			if($daily_quests['anime'] && !$daily_quests['personagem']){
				$organizations			= Recordset::query('SELECT id FROM organizations WHERE removed = 0 AND id not in ('.$player->organization_id.') ORDER BY RAND() LIMIT 1')->row_array();

			}else if($daily_quests['anime'] && $daily_quests['personagem']){

				$organizations			= Recordset::query('SELECT id FROM organizations WHERE removed = 0 AND id not in ('.$player->organization_id.') ORDER BY RAND() LIMIT 1')->row_array();
				$organizations['id'] = $organizations['id'] ? $organizations['id'] : 0;
				$personagens			= Recordset::query('SELECT id FROM players WHERE removed = 0 AND organization_id ='. $organizations['id'] .' ORDER BY RAND() LIMIT 1')->row_array();

			}

			Recordset::insert('organization_daily_quests', [
				'organization_id'		=> $player->organization_id,
				'daily_quest_id'		=> $daily_quests['id'],
				'type'					=> $daily_quests['type'],
				'guild_enemy_id'		=> ($organizations['id']) ? $organizations['id'] : 0 ,
				'enemy_id'				=> ($personagens['id']) ? $personagens['id'] : 0
			]);

		} else {
			$this->json->messages	= $errors;
		}
	}
	function daily_change(){
		$player					= Player::get_instance();
		$user					= User::get_instance();

		$this->as_json			= true;
		$this->json->success	= false;
		$this->json->messages	= [];

		$errors					= [];
		$buy_change				= 0;
		$valor_change			= 0;
		$animes					= 0;
		$personagens			= 0;

		if (isset($_POST['id']) && is_numeric($_POST['id']) && isset($_POST['quest']) && is_numeric($_POST['quest'])) {
			$daily				= DailyQuest::find($_POST['quest']);

			if (!$daily) {
				$errors[]	= t('quests.time.errors.invalid');
			} else {
				$player_daily 		= PlayerDailyQuest::find($_POST['id']);
				$buy_mode_change 	= PlayerChange::find_first("player_id=" . $player->id);

				if ($player_daily->complete == 1) {
					$errors[]	= t('quests.time.errors.invalid');
				}

				if ($buy_mode_change) {
					if ($buy_mode_change->daily == 0) {
						$buy_change = 0;
					} elseif ($buy_mode_change->daily > 0 && $buy_mode_change->daily < 5) {
						$valor_change = $buy_mode_change->daily * 500;

						if ($player->currency < $valor_change) {
							$errors[]	= t("quests.time.errors.not_enough_currency");
						} else {
							$buy_change = 1;
						}
					} elseif ($buy_mode_change->daily > 4) {
						if ($buy_mode_change->daily > 4   && $buy_mode_change->daily < 10) {
							$valor_change = 1;
						} elseif ($buy_mode_change->daily > 9  && $buy_mode_change->daily < 15) {
							$valor_change = 2;
						} elseif ($buy_mode_change->daily > 14  && $buy_mode_change->daily < 20) {
							$valor_change = 3;
						} elseif ($buy_mode_change->daily > 20) {
							$valor_change = 5;
						}

						if ($user->credits < $valor_change) {
							$errors[]	= t("quests.time.errors.not_enough_credits");
						}else{
							$buy_change = 2;
						}

					}
				} else {
					$player_change				 = new PlayerChange();
					$player_change->player_id 	 = $player->id;
					$player_change->save();
				}
			}
		} else {
			$errors[]	= t('quests.time.errors.invalid');
		}

		if (!sizeof($errors)) {
			$this->json->success		= true;

			// Desconta o valor do player
			if ($buy_change != 1) {
				$user->spend($valor_change);
			} else {
				$player->spend($valor_change);
			}

			// Atualiza o contador de troca das missões diarias
			if (!$buy_mode_change) {
				$buy_mode_change 	= PlayerChange::find_first("player_id=" . $player->id);
			}
			$buy_mode_change->daily++;
			$buy_mode_change->save();

			// Deleta a missão do player
			Recordset::delete('player_daily_quests', [
				'id'		=> [
					'escape'	=> true,
					'value'		=> $_POST['id']
				],
				'player_id'	=> [
					'escape'	=> true,
					'value'		=> $player->id
				]
			]);

			// Adiciona uma nova missão para o player
			$daily_quests			= Recordset::query('SELECT * FROM daily_quests WHERE of="player" ORDER BY RAND() LIMIT 1')->row_array();
			$quest	= DailyQuest::find_first("of = 'player'", [
				'reorder'	=> 'RAND()',
				'limit'		=> 1
			]);
			if ($quest->anime && !$quest->personagem) {
                $anime = Anime::find_first('active = 1', [
                    'reorder'	=> 'RAND()',
                    'limit'		=> 1
                ]);
            } elseif ($quest->anime && $quest->personagem) {
                $anime      = Anime::find_first('active = 1', [
                    'reorder'	=> 'RAND()',
                    'limit'		=> 1
                ]);
                $character  = Character::find_first('active = 1 and anime_id = ' . $anime->id, [
                    'reorder'	=> 'RAND()',
                    'limit'		=> 1
                ]);
            }
			if ($daily_quests['anime'] && !$daily_quests['personagem']) {
				$animes					= Recordset::query('SELECT id FROM animes WHERE active = 1 ORDER BY RAND() LIMIT 1')->row_array();
			} elseif ($daily_quests['anime'] && $daily_quests['personagem']) {
				$animes					= Recordset::query('SELECT id FROM animes WHERE active = 1 ORDER BY RAND() LIMIT 1')->row_array();
				$personagens			= Recordset::query('SELECT id FROM characters WHERE active = 1 AND anime_id ='. $animes['id'] .' ORDER BY RAND() LIMIT 1')->row_array();
			}

			Recordset::insert('player_daily_quests', [
				'player_id'				=> $player->id,
				'daily_quest_id'		=> $daily_quests['id'],
				'type'					=> $daily_quests['type'],
				'anime_id'				=> ($animes['id']) ? $animes['id'] : 0,
				'character_id'			=> ($personagens['id']) ? $personagens['id'] : 0
			]);
		} else {
			$this->json->messages	= $errors;
		}
	}
	function account_change() {
		$player					= Player::get_instance();
		$user					= User::get_instance();
		$this->as_json			= true;
		$this->json->success	= false;
		$this->json->messages	= [];
		$errors					= [];
		$buy_change				= 0;
		$valor_change			= 0;
		$animes					= 0;
		$personagens			= 0;

		if (isset($_POST['id']) && is_numeric($_POST['id']) && isset($_POST['quest']) && is_numeric($_POST['quest'])) {
			$daily				= DailyQuest::find($_POST['quest']);

			if (!$daily) {
				$errors[]	= t('quests.time.errors.invalid');
			} else {
				$user_daily 		= UserDailyQuest::find($_POST['id']);
				$buy_mode_change 	= UserChange::find_first("user_id=" . $user->id);

				if ($user_daily->complete == 1) {
					$errors[]	= t('quests.time.errors.invalid');
				}
				if ($buy_mode_change) {
					if ($buy_mode_change->daily == 0) {
						$buy_change = 0;
					} elseif ($buy_mode_change->daily > 0 && $buy_mode_change->daily < 5) {
						$valor_change = $buy_mode_change->daily * 500;
						if ($player->currency < $valor_change) {
							$errors[]	= t("quests.time.errors.not_enough_currency");
						} else {
							$buy_change = 1;
						}
					} elseif ($buy_mode_change->daily > 4) {
						if ($buy_mode_change->daily > 4   && $buy_mode_change->daily < 10) {
							$valor_change = 1;
						} elseif ($buy_mode_change->daily > 9  && $buy_mode_change->daily < 15) {
							$valor_change = 2;
						} elseif ($buy_mode_change->daily > 14  && $buy_mode_change->daily < 20) {
							$valor_change = 3;
						} elseif ($buy_mode_change->daily > 20){
							$valor_change = 5;
						}

						if ($user->credits < $valor_change) {
							$errors[]	= t("quests.time.errors.not_enough_credits");
						} else {
							$buy_change = 2;
						}
					}
				} else {
					$user_change			= new UserChange();
					$user_change->user_id 	= $user->id;
					$user_change->save();
				}
			}
		} else {
			$errors[]	= t('quests.time.errors.invalid');
		}

		if (!sizeof($errors)) {
			$this->json->success		= true;

			// Desconta o valor do player
			if ($buy_change == 1) {
				$player->spend($valor_change);
			} elseif ($buy_change == 2) {
				$user->spend($valor_change);
			}

			// Atualiza o contador de troca das missões diarias
			if (!$buy_mode_change) {
				$buy_mode_change 	= UserChange::find_first("user_id=" . $user->id);
			}
			$buy_mode_change->daily++;
			$buy_mode_change->save();

			//Deleta a missão do player
			$user_daily_del = Recordset::query("DELETE FROM user_daily_quests WHERE id=". $_POST['id']." AND user_id=".$user->id);

			//Adiciona uma nova missão para o player
			$daily_quests			= Recordset::query('SELECT * FROM daily_quests WHERE of="account" ORDER BY RAND() LIMIT 1')->row_array();

			if($daily_quests['anime'] && !$daily_quests['personagem']){
				$animes				= Recordset::query('SELECT id FROM animes WHERE active = 1 ORDER BY RAND() LIMIT 1')->row_array();

			}
			Recordset::insert('user_daily_quests', [
				'user_id'				=> $user->id,
				'daily_quest_id'		=> $daily_quests['id'],
				'type'					=> $daily_quests['type'],
				'anime_id'				=> ($animes['id']) ? $animes['id'] : 0
			]);

		} else {
			$this->json->messages	= $errors;
		}
	}
	function daily_finish(){
		$player					= Player::get_instance();
		$player_quests		  	= DailyQuest::all(['cache' => true]);
		$player_quests_daily   	= $player->daily_quests();
		$this->as_json			= true;
		$this->json->success	= false;
		$this->json->messages	= [];
		$errors					= [];

		if($player_quests_daily){
			foreach($player_quests as $player_quest){
				foreach($player_quests_daily as $player_quest_daily){
					if($player_quest_daily->daily_quest_id == $player_quest->id){

						if($player_quest_daily->total >= $player_quest->total && !$player_quest_daily->complete){

							// Recompensas e Atualizações de contadores
							$this->json->success				= true;
							$player_quest_daily->completed_at	= now(true);
							$player_quest_daily->complete		= 1;
							$player_quest_daily->save();

							$player->currency					+= $player_quest->currency;
							$player->save();

							$counters	= $player->quest_counters();
							$counters->daily_total++;
							$counters->save();

							//Verifica a conquista de fragmentos - Conquista
							$player->achievement_check("daily_quests");

							// Level da Conta ( Missão Diaria )
							$user = User::get_instance();
							$user->exp	+= percent(1, $player_quest->currency);
							$user->save();

						}else{
							$errors[]	= t('quests.daily.errors.not_ready');
							$this->json->messages	= $errors;
						}

					}
				}
			}

		}else{
			$errors[]	= t('quests.daily.errors.not_mission');
			$this->json->messages	= $errors;
		}
	}
	function account_finish(){
		$player					= Player::get_instance();
		$user					= User::get_instance();
		$player_quests		  	= DailyQuest::find("of='account'", ['cache' => true]);
		$user_quests_daily   	= $player->account_quests();
		$this->as_json			= true;
		$this->json->success	= false;
		$this->json->messages	= [];
		$errors					= [];

		if($user_quests_daily){
			foreach($player_quests as $player_quest){
				foreach($user_quests_daily as $user_quest_daily){
					if($user_quest_daily->daily_quest_id == $player_quest->id){

						if($user_quest_daily->total >= $player_quest->total && !$user_quest_daily->complete){

							// Recompensas e Atualizações de contadores
							$this->json->success				= true;
							$user_quest_daily->completed_at	= now(true);
							$user_quest_daily->complete		= 1;
							$user_quest_daily->save();

							// Adiciona o EXP da conta
							$user->exp($player_quest->currency);


							//Adiciona o contador de missões da conta
							$counters	= $user->quest_counters();
							$counters->daily_total++;
							$counters->save();

							//Verifica a conquista de fragmentos - Conquista
							$player->achievement_check("account_quests");

						}else{
							$errors[]	= t('quests.daily.errors.not_ready');
							$this->json->messages	= $errors;
						}

					}
				}
			}

		}else{
			$errors[]	= t('quests.daily.errors.not_mission');
			$this->json->messages	= $errors;
		}
	}
	function organization_daily_finish(){
		$player							= Player::get_instance();
		$players_orgs					= Player::find("organization_id=". $player->organization_id);
		$player_quests		  			= DailyQuest::all(['cache' => true]);
		$organization_quests_daily   	= $player->organization_daily_quests();
		$this->as_json					= true;
		$this->json->success			= false;
		$this->json->messages			= [];
		$errors							= [];

		if($organization_quests_daily){
			foreach($player_quests as $player_quest){
				foreach($organization_quests_daily as $organization_quest_daily){
					if($organization_quest_daily->daily_quest_id == $player_quest->id){

						if($organization_quest_daily->total >= $player_quest->total && !$organization_quest_daily->complete){

							// Recompensas e Atualizações de contadores
							$this->json->success					= true;
							$organization_quest_daily->completed_at	= now(true);
							$organization_quest_daily->complete		= 1;
							$organization_quest_daily->save();

							foreach($players_orgs as $players_org){
								$p = Player::find_first("id=". $players_org->id);
								$p->currency	+= $player_quest->currency;
								$p->save();
							}

							$counters	= $player->organization_quest_counters();
							$counters->daily_total++;
							$counters->save();

							//Verifica a conquista de fragmentos - Conquista
							$player->achievement_check("weekly_quests");

						}else{
							$errors[]	= t('quests.daily.errors.not_ready');
							$this->json->messages	= $errors;
						}

					}
				}
			}

		}else{
			$errors[]	= t('quests.daily.errors.not_mission');
			$this->json->messages	= $errors;
		}
	}

	function time_finish() {
		$player					= Player::get_instance();
		$quest					= $player->character()->anime()->time_quest($player->time_quest_id);
		$player_quest			= $player->player_time_quest($player->time_quest_id);
		$duration				= $quest->duration($player_quest->duration);
		$can_finish				= now() >= strtotime($player_quest->finish_at);
		$effects				= $player->get_parsed_effects();
		$extras					= $player->attributes();

		$this->as_json			= TRUE;
		$this->json->success	= FALSE;
		$this->json->messages	= [];
		$errors					= [];

		if (!$can_finish) {
			$errors[]	= t('quests.time.errors.not_ready');
		}

		if (!sizeof($errors)) {
			$this->json->success		= TRUE;

			$player_quest->finished_at	= now(TRUE);
			$player_quest->save();

			$expReward		= $duration->exp + percent($effects['bonus_exp_mission_percent'], $duration->exp) + $effects['bonus_exp_mission'] + percent($extras->exp_quest, $duration->exp);
			$coinsReward	= $duration->currency + percent($effects['bonus_gold_mission_percent'], $duration->currency) + $effects['bonus_gold_mission'] + percent($extras->currency_quest, $duration->currency);

			$player->exp			+= $expReward;
			$player->currency		+= $coinsReward;

			$player->time_quest_id	= 0;
			$player->save();

			// Missões Diarias
			$player_quests_daily   = $player->daily_quests();
			if ($player_quests_daily) {
				foreach ($player_quests_daily as $player_quest_daily) {
					switch ($player_quest_daily->type) {
						case "time_mission":
							$player_quest_daily->total++;
							break;
					}
					$player_quest_daily->save();
				}
			}

			// Level da Conta ( Missão de Tempo )
			$user = User::get_instance();
			$user->exp	+= percent(5, $expReward);
			$user->save();

			$counters	= $player->quest_counters();
			$counters->time_total++;
			$counters->save();

			// Verifica a conquista de fragmentos - Conquista
			$player->achievement_check("time_quests");

			$this->_give_item_reward($player, $player_quest);
		} else {
			$this->json->messages	= $errors;
		}
	}
	function pet_finish() {
		$user					= User::get_instance();
		$player					= Player::get_instance();

		$this->as_json			= true;
		$this->json->success	= false;
		$this->json->messages	= [];
		$errors					= [];

		if (isset($_POST['id']) && is_numeric($_POST['id'])) {
			$quest	= PetQuest::find($_POST['id']);
			if (!$quest) {
				$errors[]				= t('quests.time.errors.invalid');
			} else {
				$player_quest_pet		= PlayerPetQuest::find_first("completed = 0 and player_id = {$player->id} and success_at is not null and pet_quest_id = " . $quest->id);
				if (!$player_quest_pet) {
					$errors[]	= t('quests.time.errors.invalid');
				}
			}
		} else {
			$errors[]	= t('quests.time.errors.invalid');
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;

			// Finaliza a missão!
			$player_quest_pet->completed = 1;
			$player_quest_pet->finished_at	= now(true);
			$player_quest_pet->save();

			// Adiciona o contador das quests de pets
			if ($player_quest_pet->success) {
				$counters	= $player->quest_counters();
				$counters->pet_total++;
				$counters->save();
			}

			// Verifica a conquista de fragmentos - Conquista
			$player->achievement_check("pet_quests");

			// Removendo os pets do trabalho e adicionando a exp e felicidades deles
			for ($i = 1; $i <= 3; ++$i) {
				$petID = 'pet_id_' . $i;
				$actualPetID = $player_quest_pet->$petID;
				if ($actualPetID) {
					$actualPet	= PlayerItem::find_first("player_id = {$player->id} AND item_id = {$actualPetID}");
					if ($quest->pet_exp && $player_quest_pet->success) {
						$actualPet->exp	+= $quest->pet_exp;

						// Evolui o Pet
						$player->check_pet_level($actualPet);
					}

					if ($quest->pet_happiness && $player_quest_pet->success) {
						$actualPet->happiness	+= $quest->pet_happiness;
						$actualPet->happiness	=  $actualPet->happiness > 100 ? 100 : $actualPet->happiness;
					}
					$actualPet->working	= 0;
					$actualPet->save();
				}
			}

			// A missão foi bem sucedida
			if ($player_quest_pet->success) {
				// Adiciona o Dinheiro do jogador
				if ($quest->currency)
					$player->earn($quest->currency);

				// Adiciona a Experiência do jogador
				if ($quest->exp) {
					$player->exp	+= $quest->exp;
					$player->save();
				}
				// Adiciona créditos para o jogador
				if ($quest->credits) {
					$user->earn($quest->credits);
				}

				// Prêmios ( EQUIPS )
				if ($quest->equipment) {
					if ($quest->equipment == 1) {
						$dropped  = Item::generate_equipment($player);
					} elseif ($quest->equipment == 2) {
						$dropped  = Item::generate_equipment($player, 0);
					} elseif ($quest->equipment == 3) {
						$dropped  = Item::generate_equipment($player, 1);
					} elseif ($quest->equipment == 4) {
						$dropped  = Item::generate_equipment($player, 2);
					} elseif ($quest->equipment == 5) {
						$dropped  = Item::generate_equipment($player, 3);
					}
				}

				// Prêmios ( PETS )
				if ($quest->item_id && $quest->pets) {
					if (!$player->has_item($quest->item_id)) {
						$npc_pet = Item::find($quest->item_id);

						$player_pet				= new PlayerItem();
						$player_pet->item_id	= $npc_pet->id;
						$player_pet->player_id	= $player->id;
						$player_pet->save();
					}
				}

				// Prêmios ( CHARACTERS )
				if ($quest->character_id && !$user->is_character_bought($quest->character_id)) {
					$reward_character				= new UserCharacter();
					$reward_character->user_id		= $player->user_id;
					$reward_character->character_id	= $quest->character_id;
					$reward_character->was_reward	= 1;
					$reward_character->save();
				}

				// Prêmios ( THEME )
				if ($quest->character_theme_id && !$user->is_theme_bought($quest->character_theme_id)) {
					$reward_theme						= new UserCharacterTheme();
					$reward_theme->user_id				= $player->user_id;
					$reward_theme->character_theme_id	= $quest->character_theme_id;
					$reward_theme->was_reward			= 1;
					$reward_theme->save();
				}

				// Prêmios ( TITULOS )
				if ($quest->headline_id && !$user->is_headline_bought($quest->headline_id)) {
					$reward_headline				= new UserHeadline();
					$reward_headline->user_id		= $player->user_id;
					$reward_headline->headline_id	= $quest->headline_id;
					$reward_headline->save();
				}

				// Prêmios ( ITEMS )
				if ($quest->item_id && !$quest->pets) {
					$player_item_exist			= PlayerItem::find_first("item_id=".$quest->item_id." AND player_id=". $player->id);
					if (!$player_item_exist) {
						$player_item			= new PlayerItem();
						$player_item->item_id	= $quest->item_id;
						$player_item->quantity	= $quest->quantity;
						$player_item->player_id	= $player->id;
						$player_item->save();
					} else {
						$player_item_exist->quantity += $quest->quantity;
						$player_item_exist->save();
					}
				}
			}
		} else {
			$this->json->messages	= $errors;
		}
	}

	function _generate_time_quest_list($player) {
		foreach ($player->time_quests() as $quest) {
			$this->quests[]	= $quest->time_quest_id;
		}

		$this->assign('player_quests', $this->quests);
	}
	function _generate_pet_quest_list($player) {
		foreach ($player->pet_quests() as $quest) {
			$this->quests[]	= $quest->pet_quest_id;
		}

		$this->assign('player_quests', $this->quests);
	}
	function _generate_daily_quest_list($player) {
		foreach ($player->daily_quests() as $quest) {
			$this->quests[]	= $quest->daily_quest_id;
		}
		$this->assign('player_quests', $this->quests);
	}
	function _generate_account_quest_list($user) {
		foreach ($user->account_quests() as $quest) {
			$this->quests[]	= $quest->daily_quest_id;
		}
		$this->assign('user_quests', $this->quests);
	}
	function _generate_organization_daily_quest_list($player) {
		foreach ($player->organization_daily_quests() as $quest) {
			$this->quests[]	= $quest->daily_quest_id;
		}
		$this->assign('player_quests', $this->quests);
	}

	function pvp() {
		$player	= Player::get_instance();

		$this->assign('player', $player);
		$this->assign('effects', $player->get_parsed_effects());
		$this->assign('graduations', $player->character()->anime()->graduations(' AND sorting!=1'));
		$this->assign('quests', $player->character()->anime()->pvp_quests());
		$this->_generate_pvp_quest_list($player);
		$this->assign('player_tutorial', $player->player_tutorial());
	}

	function pvp_accept() {
		$player					= Player::get_instance();
		$this->as_json			= true;
		$this->json->success	= false;
		$this->json->messages	= [];
		$errors					= [];

		$this->_generate_pvp_quest_list($player);

		if(isset($_POST['quest']) && is_numeric($_POST['quest'])) {
			$quest	= PvpQuest::find($_POST['quest']);

			if(!$quest || ($quest->anime_id && $quest->anime_id != $player->character()->anime_id)) {
				$errors[]	= t('quests.pvp.errors.invalid');
			} else {
				if(in_array($quest->id, $this->quests)) {
					$errors[]	= t('quests.pvp.errors.already');
				}

				if($quest->req_level > $player->level) {
					$errors[]	= t('quests.pvp.errors.level');
				}

				if($quest->req_graduation_sorting > $player->graduation()->sorting) {
					$errors[]	= t('quests.pvp.errors.graduation');
				}
			}
		} else {
			$errors[]	= t('quests.pvp.errors.invalid');
		}

		if(!sizeof($errors)) {
			$this->json->success		= true;
			$player->pvp_quest_id		= $quest->id;
			$player->save();

			$player_quest				= new PlayerPvpQuest();
			$player_quest->pvp_quest_id	= $quest->id;
			$player_quest->player_id	= $player->id;

			$this->_add_reward($quest, $player, $player_quest);

			$player_quest->save();
		} else {
			$this->json->messages	= $errors;
		}
	}

	function pvp_status() {
		$player			= Player::get_instance();
		$quest			= $player->character()->anime()->pvp_quest($player->pvp_quest_id);
		$player_quest	= $player->player_pvp_quest($player->pvp_quest_id);
		$can_finish		= true;

		if ($quest->req_same_level && $player_quest->req_same_level < $quest->req_same_level) {
			$can_finish	= false;
		}

		if ($quest->req_low_level && $player_quest->req_low_level < $quest->req_low_level) {
			$can_finish	= false;
		}

		if ($quest->req_kill_wo_amplifier && $player_quest->req_kill_wo_amplifier < $quest->req_kill_wo_amplifier) {
			$can_finish	= false;
		}

		if ($quest->req_kill_wo_buff && $player_quest->req_kill_wo_buff < $quest->req_kill_wo_buff) {
			$can_finish	= false;
		}

		if ($quest->req_kill_wo_ability && $player_quest->req_kill_wo_ability < $quest->req_kill_wo_ability) {
			$can_finish	= false;
		}

		if ($quest->req_kill_wo_speciality && $player_quest->req_kill_wo_speciality < $quest->req_kill_wo_speciality) {
			$can_finish	= false;
		}

		$this->assign('player', $player);
		$this->assign('quest', $quest);
		$this->assign('player_quest', $player_quest);
		$this->assign('can_finish', $can_finish);
	}

	function pvp_finish() {
		$this->as_json			= TRUE;
		$this->json->success	= FALSE;
		$this->json->messages	= [];

		$player					= Player::get_instance();
		$quest					= $player->character()->anime()->pvp_quest($player->pvp_quest_id);
		$player_quest			= $player->player_pvp_quest($player->pvp_quest_id);
		$effects				= $player->get_parsed_effects();
		$can_finish				= TRUE;

		if ($quest->req_same_level && $player_quest->req_same_level < $quest->req_same_level) {
			$can_finish	= FALSE;
		}

		if ($quest->req_low_level && $player_quest->req_low_level < $quest->req_low_level) {
			$can_finish	= FALSE;
		}

		if ($quest->req_kill_wo_amplifier && $player_quest->req_kill_wo_amplifier < $quest->req_kill_wo_amplifier) {
			$can_finish	= FALSE;
		}

		if ($quest->req_kill_wo_buff && $player_quest->req_kill_wo_buff < $quest->req_kill_wo_buff) {
			$can_finish	= FALSE;
		}

		if ($quest->req_kill_wo_ability && $player_quest->req_kill_wo_ability < $quest->req_kill_wo_ability) {
			$can_finish	= FALSE;
		}

		if ($quest->req_kill_wo_speciality && $player_quest->req_kill_wo_speciality < $quest->req_kill_wo_speciality) {
			$can_finish	= FALSE;
		}

		if ($can_finish) {
			$this->json->success		= TRUE;

			$player_quest->completed_at	= now(TRUE);
			$player_quest->complete		= 1;

			$player_quest->save();

			$expReward		= $quest->exp() + percent($effects['bonus_exp_mission_percent'], $quest->exp()) + $effects['bonus_exp_mission'];
			$coinsReward	= $quest->currency() + percent($effects['bonus_gold_mission_percent'], $quest->exp()) + $effects['bonus_gold_mission'];

			$player->exp			+= $expReward;
			$player->currency		+= $coinsReward;
			$player->pvp_quest_id	= 0;
			$player->save();

			// Missões Diarias
			$player_quests_daily   = $player->daily_quests();
			if($player_quests_daily){
				foreach ($player_quests_daily as $player_quest_daily):
					switch($player_quest_daily->type){
						case "pvp_mission":
							$player_quest_daily->total++;
							break;
					}
					$player_quest_daily->save();
				endforeach;
			}

			// Level da Conta ( Missão de PVP )
			$user = User::get_instance();
			$user->exp	+= percent(10, $expReward);
			$user->save();

			$counters	= $player->quest_counters();
			$counters->pvp_total++;
			$counters->save();

			//Verifica a conquista de fragmentos - Conquista
			$player->achievement_check("pvp_quests");

			$this->_give_item_reward($player, $player_quest);
		} else {
			$this->json->messages[]	= t('quests.pvp.errors.requirements');
		}
	}

	function pvp_cancel() {
		$this->layout   = FALSE;
		$this->render   = FALSE;

		$player			= Player::get_instance();
		$quest			= $player->character()->anime()->pvp_quest($player->pvp_quest_id);
		$player_quest	= $player->player_pvp_quest($player->pvp_quest_id);

		$player->pvp_quest_id	= 0;
		$player->save();

		$player_quest->destroy();
	}

	function _generate_pvp_quest_list($player) {
		foreach ($player->pvp_quests() as $quest) {
			$this->quests[]	= $quest->pvp_quest_id;
		}

		$this->assign('player_quests', $this->quests);
	}

	private function _add_reward($quest, $player, &$player_quest) {
		if ($quest->random_equipment_chance) {
			$got_ramdom_drop	= has_chance($quest->random_equipment_chance);

			if ($got_ramdom_drop) {
				$player_quest->reward_equipment		= 1;
			}
		} elseif($quest->random_pet_chance && !$quest->reward_item_id) {
			if (has_chance($quest->random_pet_chance)) {
				$pet	= Item::find_first('item_type_id=3 AND is_initial=1', ['reorder' => 'RAND()']);

				if (!$player->has_item($pet->id)) {
					$player_quest->reward_pet_id	= $pet->id;
				}
			}
		} elseif($quest->random_pet_chance && $quest->reward_item_id) {
			if (has_chance($quest->random_pet_chance)) {
				if (!$player->has_item($quest->reward_item_id)) {
					$player_quest->reward_pet_id	= $quest->reward_item_id;
				}
			}
		}
	}

	private function _give_item_reward($player, $player_quest) {
		if ($player_quest->reward_equipment) {
			Item::generate_equipment($player);
		}

		if ($player_quest->reward_pet_id) {
			$player_pet				= new PlayerItem();
			$player_pet->item_id	= $player_quest->reward_pet_id;
			$player_pet->player_id	= $player->id;
			$player_pet->save();
		}
	}
}
