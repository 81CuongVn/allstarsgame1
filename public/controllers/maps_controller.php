<?php
	class MapsController extends Controller {
		//use BattleSharedMethods;
		//private $npc_limit	= 20;

		function index() {
			$player		= Player::get_instance();
			$map_animes	= MapAnime::find($_SESSION['universal'] ? '1=1' : 'active=1', ['cache' => true]);		
			$this->assign('player', $player);
			$this->assign('map_animes', $map_animes);
		}
		function unlock() {
			$this->as_json			= true;
			$this->json->success	= false;
			$errors					= [];
	
			if (!isset($_POST['id']) || (isset($_POST['id']) && !is_numeric($_POST['id']))) {
				$errors[]	= t('map.errors.4');
			} else {
				$player		= Player::get_instance();
				$map_anime	= MapAnime::find($_POST['id']);
				$maps		= Map::find_first("anime_id = ".$map_anime->anime_id." ORDER BY RAND () LIMIT 1");
				$map_anime->set_player($player);
				
				if(!$_SESSION['universal']){
					if (!$map_anime->active) {
						$map_anime	= false;
					}
				}
				if(sizeof($map_anime->limit_by_day($map_anime->anime_id)) >= 1){
					$errors[]	= t('friends.f26');
				}
				if ($player->level < 2) {
					$errors[]	= 'Impossível ir para a Exploração no Nível 1.';
				}
				if($player->is_pvp_queued) {
					$errors[]	= t('map.errors.8');
				}
				if (!$map_anime) {
					$errors[]	= t('map.errors.1');
				} else {
					if ($_POST['mode'] == 2 && $player->currency < $map_anime->currency_cost) {
						$errors[]	= t('map.errors.2');
					} elseif ($_POST['mode'] == 3 && $player->user()->credits < $map_anime->credits_cost) {
						$errors[]	= t('map.errors.3');
					}
				}
			}

			if (!sizeof($errors)) {
				$this->json->success	= true;

				if ($_POST['mode'] == 2) {
					$player->spend($map_anime->currency_cost);
					$steps = 10;
				} elseif($_POST['mode'] == 3) {
					$player->user()->spend($map_anime->credits_cost);
					$steps = 15;
				}else{
					$steps = 5;
				}
				//Salva o Map e os Passos na Tabela Player
				$player->map_id 						= $maps->id;
				$player->steps 							= $steps;
				$player->save();
				
				//Adiciona no Log Diario de Exploração
				$player_map_anime						    = new PlayerMapAnime();
				$player_map_anime->player_id				= $player->id;
				$player_map_anime->map_anime_id				= $map_anime->anime_id;
				$player_map_anime->save();
				
				//Adiciona no Log de Exploração
				$player_exploration = PlayerMapLog::find_first("player_id=".$player->id." and map_id=".$maps->id);
				if($player_exploration){
					$player_exploration->quantity++;
				}else{
					$player_exploration = new PlayerMapLog();
					$player_exploration->player_id 		= $player->id;
					$player_exploration->anime_id	 	= $maps->anime_id;
					$player_exploration->map_id 		= $maps->id;
					$player_exploration->quantity 		= 1;
				}
				
				$player_exploration->save();
				//Adiciona no Log de Exploração

			} else {
				$this->json->messages	= $errors;
			}
		}
		function preview(){
			$player		  = Player::get_instance();
			$map 		  = Map::find_first("id=".$player->map_id);
			
			switch($map->anime_id){
				case 1:
					$item_id = 1721;
				break;
				
				case 2:
					$item_id = 1722;
				break;
				
				case 9:
					$item_id = 1851;
				break;
			}
			
			$player_stats = PlayerStat::find_first("player_id=".$player->id);
			
			if($player_stats->rewards){
				$rewards = MapReward::find_first("map_id =".$player->map_id);
				$this->assign('rewards', $rewards);	
			}
			if($player_stats->map_reward){
				$this->assign('map_reward', true);	
			}
			if($player_stats->npc){
				$npc	= new NpcInstance($player, $map->npc_anime_id, [], NULL, NULL, NULL, NULL, $map->npc_character_id, $map->npc_character_theme_id);
				
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
					
				$this->assign('npc', $npc);
			}
			$this->assign('player', $player);
			$this->assign('map', $map);
			$this->assign('player_item', PlayerItem::find_first("player_id=".$player->id." AND item_id=".$item_id));
			$this->assign('player_stats', $player_stats);
			$this->assign('map_total', Map::find("anime_id=".$map->anime_id));
			$this->assign('map_player_total', PlayerMapLog::find("player_id=".$player->id." and anime_id=".$map->anime_id));
		}
		function navegation(){
			$this->as_json			= true;
			$this->json->success	= false;
			$errors					= [];
			$player					= Player::get_instance();
			
			// Remove a flag que vai dizer que o jogador tem uma premiação no mapa
			$player_stats = PlayerStat::find_first("player_id=".$player->id);
			$player_stats->rewards 		= 0;
			$player_stats->npc 			= 0;
			$player_stats->map_reward	= 0;
			$player_stats->save();
			
			if (!isset($_POST['map']) || (isset($_POST['map']) && !is_numeric($_POST['direction'])) && !isset($_POST['direction']) || (isset($_POST['direction']) && !is_numeric($_POST['direction']))) {
				$errors[]	= t('map.errors.4');
			} else {
				$map		= Map::find_first("id = ".$player->map_id);
		
				if (!$map) {
					$errors[]	= t('map.errors.4');
				} else {
					if($_POST['direction'] > 4){
						$errors[]	= t('map.errors.5');
					}
					if(!$player->steps){
						$errors[]	= t('map.errors.6');
					}
					// Verifica se o próximo mapa que o jogador está indo esta certo.
					if($_POST['direction']==1){
						if($_POST['map']!=$map->north){
							$errors[]	= t('map.errors.1');
						}
					}
					if($_POST['direction']==2){
						if($_POST['map']!=$map->east){
							$errors[]	= t('map.errors.1');
						}
					}
					if($_POST['direction']==3){
						if($_POST['map']!=$map->south){
							$errors[]	= t('map.errors.1');
						}
					}
					if($_POST['direction']==4){
						if($_POST['map']!=$map->west){
							$errors[]	= t('map.errors.1');
						}
					}
				}
			}

			if (!sizeof($errors)) {
				$this->json->success	= true;
				$player		= Player::get_instance();
				$user		= User::get_instance();
				$next_map 	= Map::find_first("id=".$_POST['map']);
				
				//Acerta os tickets do mapa
				switch($next_map->anime_id){
					case 1:
						$item_id_map = 1721;
					break;
					case 9:
						$item_id_map = 1851;
					break;
				}
				
				// Verifica se o jogador ganhou o ticket especial do anime
				$rand_ticket 	= rand(1,100);
				if($rand_ticket <= 10){
					
					$item_1721 = PlayerItem::find_first("player_id =". $player->id. " AND item_id=".$item_id_map);
					if($item_1721){
						$player_ticket			= $player->get_item($item_id_map);	
						$player_ticket->quantity += 1;
						$player_ticket->save();
					}else{
						$player_ticket	= new PlayerItem();						
						$player_ticket->item_id	= $item_id_map;
						$player_ticket->player_id	= $player->id;
						$player_ticket->quantity = 1;
						$player_ticket->save();
					}
					// Adiciona a flag que vai dizer que o jogador tem uma premiação no mapa
					$player_stats = PlayerStat::find_first("player_id=".$player->id);
					$player_stats->map_reward = 1;
					$player_stats->save();
				}
				
				// Verificando a existência de npc para ser localizado no mapa
				if($next_map->chance){
					$rand_npc 	= rand(1,100);
					
					if($rand_npc <= $next_map->chance){
						// Adiciona a flag que vai dizer que o jogador tem uma premiação no mapa
						$player_stats = PlayerStat::find_first("player_id=".$player->id);
						$player_stats->npc = 1;
						$player_stats->save();
					}
				}
				
				// Verificando a possibilidade de prêmio do mapa.
				$rewards = MapReward::find_first("map_id =".$_POST['map']." AND is_npc = 0");
				$rand 		= rand(1,100);
				if($rewards){			
					if($rand <= $rewards->chance){
						//Adiciona o Dinheiro do jogador
						if ($rewards->currency) {
							$player->earn($rewards->currency);
						}
						//Adiciona a Experiência do jogador
						if ($rewards->exp) {
							$player->exp	+= $rewards->exp;
							$player->save();
						}
						//Adiciona créditos para o jogador
						if($rewards->credits) {
							$user->earn($rewards->credits);
							$user->save();
						}
						//Prêmios ( EQUIPS )
						if ($rewards->equipment) {
							if($rewards->equipment == 1){
								$dropped  = Item::generate_equipment($player);
							}elseif($rewards->equipment==2){
								$dropped  = Item::generate_equipment($player,0); 
							}elseif($rewards->equipment==3){
								$dropped  = Item::generate_equipment($player,1); 
							}elseif($rewards->equipment==4){
								$dropped  = Item::generate_equipment($player,2); 
							}
						}
						//Prêmios ( PETS )
						if ($rewards->item_id && $rewards->pets) {
							if (!$player->has_item($rewards->item_id)) {
								$npc_pet = Item::find($rewards->item_id);
								
								$player_pet				= new PlayerItem();
								$player_pet->item_id	= $npc_pet->id;
								$player_pet->player_id	= $player->id;
								$player_pet->save();
							}
						}
						//Prêmios ( CHARACTERS )
						if ($rewards->character_id) {
							$reward_character				= new UserCharacter();
							$reward_character->user_id		= $player->user_id;
							$reward_character->character_id	= $rewards->character_id;
							$reward_character->was_reward	= 1;
							$reward_character->save();
						}
						//Prêmios ( THEME )
						if ($rewards->character_theme_id) {
							$reward_theme						= new UserCharacterTheme();
							$reward_theme->user_id				= $player->user_id;
							$reward_theme->character_theme_id	= $rewards->character_theme_id;
							$reward_theme->was_reward			= 1;
							$reward_theme->save();
						}
						//Prêmios ( TITULOS )
						if ($rewards->headline_id) {
							$reward_headline				= new UserHeadline();
							$reward_headline->user_id		= $player->user_id;
							$reward_headline->headline_id	= $rewards->headline_id;
							$reward_headline->save();
						}
						//Prêmios ( ITEMS )
						if ($rewards->item_id && !$rewards->pets) {
							
							$player_item_exist			= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $player->id);
							
							if(!$player_item_exist){
								$player_item			= new PlayerItem();
								$player_item->item_id	= $rewards->item_id;
								$player_item->quantity	= $rewards->quantity;
								$player_item->player_id	= $player->id;
								$player_item->save();
							}else{
								$player_item_exist->quantity += $rewards->quantity;
								$player_item_exist->save();
							}
						}
						
						// Adiciona a flag que vai dizer que o jogador tem uma premiação no mapa
						$player_stats = PlayerStat::find_first("player_id=".$player->id);
						$player_stats->rewards = 1;
						$player_stats->total_rewards++;
						$player_stats->save();
					}
				}
				// Gasta a quantidade de passos do jogador e muda o mapa do jogador na player
				$player->map_id = $_POST['map'];
				$player->steps--;
				$player->save();
				
				//Adiciona no Log de Exploração
				$player_exploration = PlayerMapLog::find_first("player_id=".$player->id." and map_id=".$_POST['map']);
				if($player_exploration){
					$player_exploration->quantity++;
				}else{
					$player_exploration = new PlayerMapLog();
					$player_exploration->player_id 		= $player->id;
					$player_exploration->anime_id	 	= $map->anime_id;
					$player_exploration->map_id 		= $_POST['map'];
					$player_exploration->quantity 		= 1;
				}
				
				$player_exploration->save();
				//Adiciona no Log de Exploração

			} else {
				$this->json->messages	= $errors;
			}
		}
		function leave(){
			$this->as_json			= true;
			$this->json->success	= false;
			$errors					= [];
			
			$player		= Player::get_instance();
			
			if(!$player->map_id){
				$errors[]	= t('map.errors.1');
			}
			
			if (!sizeof($errors)) {
				$this->json->success	= true;
				
				
				// Remove a flag que vai dizer que o jogador tem uma premiação no mapa
				$player_stats = PlayerStat::find_first("player_id=".$player->id);
				$player_stats->rewards 		= 0;
				$player_stats->npc 			= 0;
				$player_stats->map_reward	= 0;
				$player_stats->save();
				
				$player->map_id		= 0;
				$player->steps		= 0;
				$player->save();
				
				//Verifica a conquista do Mapa - Conquista
				$player->achievement_check("map");
				// Objetivo de Round
				$player->check_objectives("map");
				
			} else {
				$this->json->messages	= $errors;
			}	
		}
		function store_change(){
			$player					= Player::get_instance();
			$this->as_json			= true;
			$this->json->success	= false;
			$errors					= [];
			
			
			if (!isset($_POST['mode']) || (isset($_POST['mode']) && !is_numeric($_POST['mode']))) {
				$errors[]	= t('map.errors.1');
			} else {
				$store 		  = MapStore::find_first("id = ". $_POST['mode']);
				$map 		  = Map::find_first("id=".$player->map_id);
			
				switch($map->anime_id){
					case 1:
						$item_id = 1721;
					break;
					
					case 2:
						$item_id = 1722;
					break;
					
					case 9:
						$item_id = 1851;
					break;
				}
				$player_item = PlayerItem::find_first("player_id=".$player->id." AND item_id=".$item_id);
				
				if($player_item->quantity < $store->map_item_total){
					$errors[]	= t('map.errors.7');
				}
				
				if(!sizeof($errors)) {
					
					$player_item->quantity -= $store->map_item_total;
					$player_item->save(); 
			
					//Prêmios ( PETS )
					if ($store->item_id && $store->pets) {
						$npc_pet = Item::find($store->item_id);
						
						$player_pet				= new PlayerItem();
						$player_pet->item_id	= $npc_pet->id;
						$player_pet->player_id	= $player->id;
						$player_pet->save();
					}
					//Prêmios ( ITEMS )
					if ($store->item_id && !$store->pets) {
						$player_item_exist			= PlayerItem::find_first("item_id=".$store->item_id." AND player_id=". $player->id);
						
						if(!$player_item_exist){
							$player_item			= new PlayerItem();
							if($store->is_technique){
								$player_item->removed	= 1;
							}
							$player_item->item_id	= $store->item_id;
							$player_item->quantity	= $store->quantity;
							$player_item->player_id	= $player->id;
							$player_item->save();
						}else{
							$player_item_exist->quantity += $store->quantity;
							$player_item_exist->save();
						}
					
					}
					//Prêmios ( CHARACTERS )
					if ($store->character_id) {
						$reward_character				= new UserCharacter();
						$reward_character->user_id		= $player->user_id;
						$reward_character->character_id	= $store->character_id;
						$reward_character->was_reward	= 1;
						$reward_character->save();
					}
					//Prêmios ( THEME )
					if ($store->character_theme_id) {
						$reward_theme						= new UserCharacterTheme();
						$reward_theme->user_id				= $player->user_id;
						$reward_theme->character_theme_id	= $store->character_theme_id;
						$reward_theme->was_reward			= 1;
						$reward_theme->save();
					}
					//Adiciona no Log
					$log						= new PlayerStoreLog();
					$log->player_id				= $player->id;
					$log->store_id				= $store->id;
					$log->map_id				= $player->map_id;
					$log->save();
			
					$this->json->success	= true;
				}else{
					$this->json->messages	= $errors;
				}
			}
		}
	}