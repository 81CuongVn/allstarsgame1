<?php
	class ChallengesController extends Controller {
		//use BattleSharedMethods;
		//private $npc_limit	= 20;

		function index() {
			$player	= Player::get_instance();
			$challenges	= Challenge::find($_SESSION['universal'] ? '1=1' : 'active=1', ['cache' => true]);
			$this->assign('player', $player);
			$this->assign('challenges', $challenges);
		}
		
		function unlock() {
			$this->as_json			= true;
			$this->json->success	= false;
			$errors					= [];
	
			if (!isset($_POST['challenge']) || (isset($_POST['challenge']) && !is_numeric($_POST['challenge']))) {
				$errors[]	= t('history_mode.unlock.errors.invald');
			} else {
				$player	= Player::get_instance();
				$challenge	= Challenge::find($_POST['challenge']);
				$challenge->set_player($player);
				
				if(sizeof($challenge->limit_by_day()) > 1){
					$errors[]	= t('friends.f26');	
				}
				if($player->challenge_id){
					$errors[]	= "Você não pode comprar outra Arena do Céu, porque está no meio de um desafio.";	
				}
				if (!$challenge->active) {
					$challenge	= false;
				}

				if (!$challenge) {
					$errors[]	= t('history_mode.unlock.errors.invalid');
				} else {
					if($_POST['mode'] == 1 && !$challenge->currency_cost){
						$errors[]	= "Modo inválido";
					}
					if($_POST['mode'] == 2 && !$challenge->credits_cost){
						$errors[]	= "Modo inválido";
					}
					if ($_POST['mode'] == 1 && $player->currency < $challenge->currency_cost) {
						$errors[]	= t('history_mode.unlock.errors.not_enough_currency');
					} elseif ($_POST['mode'] != 1 && $player->user()->credits < $challenge->credits_cost) {
						$errors[]	= t('history_mode.unlock.errors.not_enough_credits');
					}
				}
			}

			if (!sizeof($errors)) {
				$this->json->success	= true;

				if ($_POST['mode'] == 1) {
					$player->spend($challenge->currency_cost);
				} else {
					$player->user()->spend($challenge->credits_cost);
				}
				//Salva o ID challenge na Tabela Player
				$player->challenge_id 						= $challenge->id;
				$player->save();
				
				$player_challenge							= new PlayerChallenge();
				$player_challenge->player_id				= $player->id;
				$player_challenge->challenge_id				= $challenge->id;
				$player_challenge->save();
			} else {
				$this->json->messages	= $errors;
			}
		}
		function show($id = null){
			if (!$id || ($id && !is_numeric($id))) {
				$this->render	= 'show_invalid';
			} else {
				$character_id			= false;
				$character_theme_id		= false;	
				$player					= Player::get_instance();
				$challenge				= Challenge::find($id);
				$challenge_active 		= PlayerChallenge::find_first('player_id='.$player->id.' and challenge_id='.$id.' and complete=0');
				$challenge_best 		= PlayerChallenge::find_first('player_id='.$player->id.' and challenge_id='.$id.' ORDER BY quantity DESC');
				$challenge_best_all 	= PlayerChallenge::find_first('challenge_id='.$id.' ORDER BY quantity DESC');
				$player_best_all		= Player::find($challenge_best_all->player_id);
				
				//Nova regra de npc
				$player_stats = PlayerStat::find_first('player_id='.$player->id);
				
				if($player_stats->npc_challenge_character_id){
					$npc	= new NpcInstance($player,$player_stats->npc_challenge_anime_id,[],NULL,NULL,NULL,$id,$player_stats->npc_challenge_character_id,$player_stats->npc_challenge_character_theme_id);
				}else{
					if($challenge->characters_id && !$challenge->characters_theme_id){
						$character_id 	= explode(",",$challenge->characters_id);
						$random			= rand(0,sizeof($character_id)-1);
						$anime_id  		= Character::find_first('id='. $character_id[$random]);
						$npc			= new NpcInstance($player,$anime_id->anime_id,[],NULL,NULL,NULL,$id,$character_id[$random],NULL);
						
					}elseif($challenge->characters_id && $challenge->characters_theme_id){
						$character_id 			= explode(",",$challenge->characters_id);
						$random					= rand(0,sizeof($character_id)-1);
						$anime_id  				= Character::find_first('id='. $character_id[$random]);
						$character_theme_id 	= explode(",",$challenge->characters_theme_id);
						$npc					= new NpcInstance($player,$anime_id->anime_id,[],NULL,NULL,NULL,$id,$character_id[$random],$character_theme_id[$random]);
						
					}else{
						$npc			= new NpcInstance($player,$challenge->anime_id,[],NULL,NULL,NULL,$id,NULL,NULL);
					}	
					
					//Salva o NPC atual no player
					$anime = Character::find_first("id=".$npc->character_id);
					
					$player_stats->npc_challenge_anime_id 			= $anime->anime_id;;
					$player_stats->npc_challenge_character_id 		= $npc->character_id;
					if($character_theme_id){
						$player_stats->npc_challenge_character_theme_id = $character_theme_id[$random];
					}
					$player_stats->save();
				}
								
				//$npc	= new NpcInstance($player,$challenge->anime_id,[],NULL,NULL,NULL,$id,NULL,NULL);
				
				$rewards = array ( 
								   array(
									'quantity' 	 => 5,
									'exp'   	 => $challenge->reward_exp * $challenge_active->quantity,
									'money' 	 => '',
									'equipments' => '',
									'pets'  	 => '',
									'title'  	 => '',
									'star'  	 => ''
								  ),
								   array( 
									'quantity' 	 => 10,
									'exp'   	 => $challenge->reward_exp * $challenge_active->quantity,
									'money' 	 => $challenge->reward_gold * $challenge_active->quantity,
									'equipments' => '',
									'pets'  	 => '',
									'title'  	 => '',
									'star'  	 => ''
								  ),
								  array( 
									'quantity' 	 => 20,
									'exp'   	 => $challenge->reward_exp * $challenge_active->quantity,
									'money' 	 => $challenge->reward_gold * $challenge_active->quantity,
									'equipments' => 'Equipamento Comum',
									'pets'  	 => '',
									'title'  	 => '',
									'star'  	 => ''
								  ),
								  array( 
									'quantity' 	 => 25,
									'exp'   	 => $challenge->reward_exp * $challenge_active->quantity,
									'money' 	 => $challenge->reward_gold * $challenge_active->quantity,
									'equipments' => 'Equipamento Raro',
									'pets'  	 => '',
									'title'  	 => '',
									'star'  	 => ''
								  ),
								  array( 
									'quantity' 	 => 35,
									'exp'   	 => $challenge->reward_exp * $challenge_active->quantity,
									'money' 	 => $challenge->reward_gold * $challenge_active->quantity,
									'equipments' => 'Equipamento Raro',
									'pets'  	 => '',
									'title'  	 => '',
									'star'  	 => ''
								  ),
								  array( 
									'quantity' 	 => 45,
									'exp'   	 => $challenge->reward_exp * $challenge_active->quantity,
									'money' 	 => $challenge->reward_gold * $challenge_active->quantity,
									'equipments' => 'Equipamento Raro',
									'pets'  	 => Item::find($challenge->reward_pet_1)->description()->name,
									'title'  	 =>  Headline::find($challenge->reward_title_1)->description()->name,
									'star'  	 => ''
								  ),
								  array( 
									'quantity' 	 => 65,
									'exp'   	 => $challenge->reward_exp * $challenge_active->quantity,
									'money' 	 => $challenge->reward_gold * $challenge_active->quantity,
									'equipments' => 'Equipamento Lendário',
									'pets'  	 => Item::find($challenge->reward_pet_1)->description()->name,
									'title'  	 => Headline::find($challenge->reward_title_1)->description()->name,
									'star'  	 => ''
								  ),
								   array( 
									'quantity' 	 => 80,
									'exp'   	 => $challenge->reward_exp * $challenge_active->quantity,
									'money' 	 => $challenge->reward_gold * $challenge_active->quantity,
									'equipments' => 'Equipamento Lendário',
									'pets'  	 => Item::find($challenge->reward_pet_2)->description()->name,
									'title'  	 => Headline::find($challenge->reward_title_2)->description()->name,
									'star'  	 => ''
								  ),
								   array( 
									'quantity' 	 => 100,
									'exp'   	 => $challenge->reward_exp * $challenge_active->quantity,
									'money' 	 => $challenge->reward_gold * $challenge_active->quantity,
									'equipments' => 'Equipamento Lendário',
									'pets'  	 => Item::find($challenge->reward_pet_2)->description()->name,
									'title'  	 => Headline::find($challenge->reward_title_2)->description()->name,
									'star'  	 => '3 Estrelas'
								  )
						  );

				$challenge->set_player($player);

				if (!$challenge->unlocked()) {
					$this->render	= 'show_denied';
				} else {
					// Cleanups -->
					SharedStore::S('last_battle_item_of_' . $player->id, 0);
					SharedStore::S('last_battle_npc_item_of_' . $player->id, 0);
	
					$player->clear_ability_lock();
					$player->clear_speciality_lock();
					$player->clear_technique_locks();
					$player->clear_effects();
					$player->save_npc_challenge($npc);
					// <--
		
					$player->refresh_talents();
					
					$this->assign('challenge', $challenge);
					$this->assign('challenge_active', $challenge_active);
					$this->assign('challenge_best', $challenge_best);
					$this->assign('challenge_best_all', $challenge_best_all);
					$this->assign('npc', $npc);
					$this->assign('rewards', $rewards);
					$this->assign('player', $player);
					$this->assign('player_best_all', $player_best_all);
				}
			}
		}
	}