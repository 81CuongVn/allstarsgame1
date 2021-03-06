<?php
class OrganizationsController extends Controller {
	public	$credits_price	= 3;
	public	$currency_price	= 5000;
	public	$min_level		= 5;
	public	$max_players	= 8;
	public	$name_rx		= '/^[áéíóúçãõ\w\s]*$/siU';

	function __construct() {
		Organization::$player_limit = $this->max_players;

		parent::__construct();
	}

	function search() {
		$this->assign('player',			Player::get_instance());
		$this->assign('credits_price',	$this->credits_price);
		$this->assign('currency_price',	$this->currency_price);
		$this->assign('min_level',		$this->min_level);
	}
	function remove_all(){
		$this->as_json			= true;
		$this->json->success	= false;
		$player					= Player::get_instance();
		$errors					= [];
		
		$organization_requests = OrganizationRequest::find("organization_id=".$player->organization_id);
		
		if(!$organization_requests){
			$errors[]	= t('organizations.remove_error');
		}
		if(!$player->organization_id){
			$errors[]	= t('organizations.remove_error2');
		}
		
		if (!sizeof($errors)) {
			$this->json->success	= true;
			
			//Deleta os pedidos de amizade
			foreach($organization_requests as $organization_request){
				$organization_request->destroy();
				$organization_request->save();
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
			$errors[]	= t('organizations.create.errors.invalid_method');
		} else {
			if (!between(strlen($name), 6, 20) || !preg_match($this->name_rx, $name)) {
				$errors[]	= t('organizations.create.errors.invalid_name');
			}

			if ($method == 1) {
				if ($player->level < $this->min_level) {
					$errors[]	= t('organizations.create.errors.not_enough_level');
				}

				if ($player->currency < $this->currency_price) {
					$errors[]	= t('organizations.create.errors.not_enough_currency');
				}
			} else {
				if ($user->credits < $this->credits_price) {
					$errors[]	= t('organizations.create.errors.not_enough_credits');
				}
			}

			$existent	= Organization::find_first('name="' . addslashes($name) . '"');

			if ($existent) {
				$errors[]	= t('organizations.create.errors.existent');
			}
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;

			if ($method == 1) {
				$player->spend($this->currency_price);
			} else {
				$user->spend($this->credits_price);
			}
			
			$organization					= new Organization();
			$organization->player_id		= $player->id;
			$organization->creation_type	= $method == 1 ? 1 : 2;
			$organization->name				= htmlspecialchars($name);
			$organization->faction_id		= $player->faction_id;
			$organization->save();

			$player->organization_id		= $organization->id;
			$player->save();

			$organization_quest_counters					= new OrganizationQuestCounter();
			$organization_quest_counters->organization_id	= $organization->id;
			$organization_quest_counters->save();
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

		$organizations	= (new Organization)->filter($filter, $page, $limit);

		$this->assign('organizations', $organizations['organizations']);
		$this->assign('pages', $organizations['pages']);
		$this->assign('player', $player);
	}

	function enter($id = null) {
		$this->as_json			= true;
		$this->json->success	= false;
		$errors					= [];
		$player					= Player::get_instance();

		if (is_numeric($id)) {
			$organization	= Organization::find($id);
			
			if($organization->member_count >= $this->max_players){
				$errors[]	= t('organizations.create.errors.full');
			}
			// Não deixa entrar 2 jogadores da mesma conta em uma organização
			$players_organizations = Player::find("organization_id=". $id ." AND user_id=". $player->user_id);
			
			if($players_organizations){
				$errors[]	= t('organizations.create.errors.users');
			}

			if ($organization && $organization->faction_id == $player->faction_id) {
				$already	= OrganizationRequest::find_first('player_id=' . $player->id . ' AND organization_id=' . $id);

				if ($already) {
					$errors[]	= t('organizations.enter.errors.already');
				}
			} else {
				$errors[]	= t('organizations.enter.errors.invalid');					
			}
		} else {
			$errors[]	= t('organizations.enter.errors.invalid');
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;

			$request					= new OrganizationRequest();
			$request->organization_id	= $id;
			$request->player_id			= $player->id;
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
		$organization			= $player->organization();
		$errors					= [];

		if (isset($_POST['id']) && is_numeric($_POST['id'])) {
			$accept = $organization->can_accept_player($player->id, $_POST['id']);
		}
		
		if (!$is_refuse) {
			// Não deixa entrar 2 jogadores da mesma conta em uma organização
			$organization_request		   = OrganizationRequest::find_first($_POST['id']);
			$player_pedido		   		   = Player::find_first($organization_request->player_id);

			$players_organizations = Player::find("organization_id=". $organization_request->organization_id ." AND user_id=". $player_pedido->user_id);

			if($players_organizations){
				$errors[]	= t('organizations.create.errors.users');
			}
		}

		if (!$accept->allowed) {
			$errors	= array_merge($errors, $accept->messages);
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;
			$request				= $organization->request($_POST['id']);
			$target					= $request->player();

			$pm	= new PrivateMessage();
			$pm->to_id	= $target->id;
			$pm->subject	= t('organizations.show.request_message_title');

			if ($is_refuse) {
				$pm->content	= t('organizations.show.refuse_message', ['name' => $organization->name]) . "<hr />" . htmlspecialchars($_POST['reason']);
			} else {
				$pm->content	= t('organizations.show.accept_message', ['name' => $organization->name]);

				$organization_player					= new OrganizationPlayer();
				$organization_player->organization_id	= $organization->id;
				$organization_player->player_id			= $target->id;
				$organization_player->save();

				$target->organization_id				= $organization->id;
				$target->save();
			}

			$pm->save();
			$request->destroy();
			$organization->fix_member_count();
		} else {
			$this->json->messages	= $errors;
		}
	}

	function leave() {
		$player					= Player::get_instance();
		$organization			= $player->organization();
		$this->as_json			= true;
		$this->json->success	= false;
		$errors					= [];

		$can_kick	= $organization->can_kick_player($organization->player_id, $player->id);
		
		/*if ($organization->player_id != $player->id) {
			$errors[]	= t('organizations.errors.not_leader');
		}*/
		if (!$can_kick->allowed) {
			$errors	= array_merge($errors, $can_kick->messages);
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;

			$pm				= new PrivateMessage();
			$pm->to_id		= $organization->player_id;
			$pm->subject	= t('organizations.kick_leave.leave_message_title');
			$pm->content	= t('organizations.kick_leave.leave_message', ['name' => $player->name]);
			$pm->save();

			$organization->player($player->id)->destroy();
			
			$player->organization_id	= 0;
			$player->save();

			$organization->fix_member_count();
		} else {
			$this->json->messages	= $errors;
		}
	}

	function destroy() {
		$player					= Player::get_instance();
		$organization			= $player->organization();
		$this->as_json			= true;
		$this->json->success	= false;
		$errors					= [];

		if ($organization->player_id != $player->id) {
			$errors[]	= t('organizations.errors.not_leader');
		}

		if ($organization->member_count > 1) {
			$errors[]	= t('organizations.errors.still_have_members');
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;

			foreach ($organization->requests() as $request) {
				$request->destroy();
			}

			$organization->destroy();

			$player->organization_id	= 0;
			$player->save();
		} else {
			$this->json->messages	= $errors;
		}
	}

	function kick() {
		$player					= Player::get_instance();
		$organization			= $player->organization();
		$this->as_json			= true;
		$this->json->success	= false;
		$errors					= [];

		if (isset($_POST['id']) && is_numeric($_POST['id'])) {
			
			$target					= $organization->player($_POST['id']);
			$target_player			= $target->player();
			
			if($target_player->battle_pvp_id){
				$errors[]	= t('organizations.kick_leave.errors.battle');
			}
			
			$can_kick	= $organization->can_kick_player($player->id, $_POST['id']);

			if (!$can_kick->allowed) {
				$errors	= array_merge($errors, $can_kick->messages);
			}
			
			
		} else {
			$errors[]	= t('organizations.kick_leave.errors.invalid');
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;
			

			$pm				= new PrivateMessage();
			$pm->to_id		= $target_player->id;
			$pm->subject	= t('organizations.kick_leave.kick_message_title');
			$pm->content	= t('organizations.kick_leave.kick_message', ['name' => $organization->name]) . "<hr />" . htmlspecialchars($_POST['reason']);
			$pm->save();

			$target->destroy();

			$target_player->organization_id	= 0;
			$target_player->save();
			$organization->fix_member_count();
		} else {
			$this->json->messages	= $errors;
		}
	}

	function update_acl() {
		$player					= Player::get_instance();
		$organization			= $player->organization();
		$this->as_json			= true;
		$this->json->success	= false;
		$errors					= [];

		if ($player->id != $organization->player_id) {
			$errors[]	= t('organizations.errors.no_privilege');
		} else {
			if (isset($_POST['id']) && is_numeric($_POST['id'])) {
				$target	= $organization->player($_POST['id']);

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
		$total_treasure		= Organization::find_first("id=". $player->organization_id);
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
		$organization			= Organization::find_first("id=". $player->organization_id);
		$players_orgs			= Player::find("organization_id=". $player->organization_id);
		$this->as_json			= true;
		$this->json->success	= false;
		$errors					= [];
		
		
		if (!isset($_POST['mode']) || (isset($_POST['mode']) && !is_numeric($_POST['mode']))) {
			$errors[]	= t('treasure.error1');
		} else {
			$treasure = TreasureReward::find_first("id =". $_POST['mode']);

			if($organization->treasure_atual < $treasure->treasure_total){
				$errors[]	= t('treasure.error2');
			}
			
			if(!sizeof($errors)) {
				
				$organization->treasure_atual -= $treasure->treasure_total;
				$organization->save(); 
				
				foreach ($players_orgs as $players_org):
					$p = Player::find_first("id=". $players_org->id);
					$user = User::find_first("id=". $players_org->user_id);

					//Prêmios ( EXP )
					if ($treasure->exp) {
						$p->exp	+= $treasure->exp;
					}
					//Enchant Points
					if ($treasure->enchant_points) {
						$p->enchant_points_total	+= $treasure->quantity;
					}
					//Prêmios ( GOLD )
					if ($treasure->currency) {
						$p->earn($treasure->currency);
					}
					//Prêmios ( CRÉDITOS )
					if($treasure->credits) {
						$user->earn($treasure->credits);
						
						// Verifica os créditos do jogador.
						$p->achievement_check("credits");
						// Objetivo de Round
						$p->check_objectives("credits");
					}
					//Prêmios ( EQUIPS )
					if ($treasure->equipment) {
						if($treasure->equipment == 1){
							$dropped  = Item::generate_equipment($p);
						}elseif($treasure->equipment==2){
							$dropped  = Item::generate_equipment($p,0); 
						}elseif($treasure->equipment==3){
							$dropped  = Item::generate_equipment($p,1); 
						}elseif($treasure->equipment==4){
							$dropped  = Item::generate_equipment($p,2); 
						}
					}
					//Prêmios ( PETS )
					if ($treasure->item_id && $treasure->pets) {
						$npc_pet = Item::find($treasure->item_id);
						
						$player_pet				= new PlayerItem();
						$player_pet->item_id	= $npc_pet->id;
						$player_pet->player_id	= $p->id;
						$player_pet->save();
					}
					//Prêmios ( ITEMS )
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
					//Prêmios ( CHARACTERS )
					if ($treasure->character_id) {
						$reward_character				= new UserCharacter();
						$reward_character->user_id		= $p->user_id;
						$reward_character->character_id	= $treasure->character_id;
						$reward_character->was_reward	= 1;
						$reward_character->save();
					}
					//Prêmios ( THEME )
					if ($treasure->character_theme_id) {
						$reward_theme						= new UserCharacterTheme();
						$reward_theme->user_id				= $p->user_id;
						$reward_theme->character_theme_id	= $treasure->character_theme_id;
						$reward_theme->was_reward			= 1;
						$reward_theme->save();
					}
					//Prêmios ( TITULOS )
					if ($treasure->headline_id) {
						$reward_headline				= new UserHeadline();
						$reward_headline->user_id		= $p->user_id;
						$reward_headline->headline_id	= $treasure->headline_id;
						$reward_headline->save();
					}
					
					//Adiciona no Log
					$log						= new PlayerTreasureLog();
					$log->player_id				= $p->id;
					$log->treasure_reward_id	= $treasure->id;
					$log->organization_id		= $player->organization_id;
					$log->save();
					
					//Manda Mensagem para os integrantes
					$pm				= new PrivateMessage();
					$pm->from_id	= $organization->player_id;
					$pm->to_id		= $p->id;
					$pm->subject	= $treasure->name;
					$pm->content	= $treasure->name;
					
					
						if($treasure->enchant_points){
						$pm->content	= t('treasure.show.desc') ." ". $treasure->quantity ." ". t('treasure.show.enchant');
						}
						if($treasure->exp){
						$pm->content	= t('treasure.show.desc') ." ". $treasure->exp ." ". t('treasure.show.exp');
						}
						if($treasure->currency){
							$pm->content	= t('treasure.show.desc') ." ". $treasure->currency ." ". t('currencies.' . $player->character()->anime_id);
						}
						if($treasure->credits){
						$pm->content	= t('treasure.show.desc') ." ". $treasure->credits ." ". t('treasure.show.credits');
						}
						if($treasure->equipment && $treasure->equipment == 1){
							$pm->content	= t('treasure.show.desc').": ". t('treasure.show.equipment1');
						}
						if($treasure->equipment && $treasure->equipment == 2){
						$pm->content	= t('treasure.show.desc').": ". t('treasure.show.equipment2');
						}
						if($treasure->equipment && $treasure->equipment == 3){
						$pm->content	= t('treasure.show.desc').": ". t('treasure.show.equipment3');
						}
						if($treasure->equipment && $treasure->equipment == 4){
						$pm->content	= t('treasure.show.desc').": ". t('treasure.show.equipment4');
						}
						if($treasure->pets  && $treasure->item_id){
						$pm->content	= t('treasure.show.desc').": ". t('treasure.show.pet')." ". Item::find($treasure->item_id)->description()->name;
						}
						if($treasure->character_theme_id){
						$pm->content = t('treasure.show.desc').": ". t('treasure.show.theme')." ". CharacterTheme::find($treasure->character_theme_id)->description()->name;
						}
						if($treasure->character_id){
						$pm->content = t('treasure.show.desc').": ". t('treasure.show.character')." ". Character::find($treasure->character_id)->description()->name;
						}
						if($treasure->headline_id){
						$pm->content = t('treasure.show.desc').": ". t('treasure.show.headline')." ". Headline::find($treasure->headline_id)->description()->name;
						}
						if(!$treasure->pets && $treasure->item_id){
						$reward	= Item::find($treasure->item_id);
						$reward->set_anime($p->character()->anime_id);
						$pm->content = t('treasure.show.desc').": ". $treasure->quantity ." ". $reward->description()->name;
						}
					$pm->save();
					$p->save();
					$user->save();
				endforeach;
										
				$this->json->success	= true;
			}else{
				$this->json->messages	= $errors;
			}
			
			
		}
	}
	function show($id = null) {

		if (isset($_POST['popup'])) {
			$this->layout	= false;
		}
	
		$player			= Player::get_instance();
		
		//Verifica se você tem organização - Conquista
		$player->achievement_check("organization");
		// Objetivo de Round
		$player->check_objectives("organization");
		
		$errors			= [];
		$upload_error	= false;
		$got_upload		= false;

		if (!$id && ($_POST || $_FILES)) {

			$organization	= $player->organization();

			if ($organization->player_id == $player->id) {
				if (isset($_POST['name']) && preg_match($this->name_rx, $_POST['name'])) {
					$other	= Organization::find_first('id != ' . $organization->id . ' AND name="' . addslashes($_POST['name']) . '"');

					if ($other) {
						$errors[]	= t('organizations.show.errors.existent');
					}
				} else {
					$errors[]	= t('organizations.show.errors.invalid');
				}

				if (!$_FILES['cover']['error']) {

					$got_upload	= true;
					$file		= $_FILES['cover'];
					$mime 		= [
						"image/jpeg",
						"image/png",
						"image/gif"
					];
					
					if(!in_array(image_type_to_mime_type(exif_imagetype($file['tmp_name'])), $mime)) {
						$upload_error = true;
					}

					if(!in_array( strtolower(substr($file['name'], -3, 3)), ['jpg', 'png', 'gif'])) {
						$upload_error = true;
					}

					if (!$upload_error) {
						$sz = getimagesize($file['tmp_name']);
						
						if($sz['0'] > 663 || $sz['1'] > 166) {
							$upload_error = true;
						}
					}
				}

				if ($got_upload && $upload_error) {
					$errors[]	= t('organizations.show.errors.invalid_image');
				}

				if (!sizeof($errors)) {
					$organization->name			= htmlspecialchars($_POST['name']);
					//$organization->description	= htmlspecialchars($_POST['description']);

					if ($got_upload) {
						$path	= ROOT . '/uploads/organizations/';
						$name	= md5($organization->id . $file['tmp_name']) . '.' . strtolower(substr($file['name'], -3, 3));

						if ($organization->cover_file) {
							@unlink($path . '/' . $organization->cover_file);
						}

						$organization->cover_file	= $name;

						move_uploaded_file($file['tmp_name'], $path . '/' . $name);
					}

					$organization->save();
				}
			}
		}

		$organization	= Organization::find(is_numeric($id) ? $id : $player->organization_id);
		$rank_org		= RankingOrganization::find_first('organization_id='.$organization->id);
		$daily_org		= OrganizationQuestCounter::find_first('organization_id='.$organization->id);


		if ($organization) {
			$can_kick	= $organization->can_kick_player($player->id)->allowed;
			$can_accept	= $organization->can_accept_player($player->id)->allowed;

			$this->assign('organization', $organization);
			$this->assign('rank_org', $rank_org);
			$this->assign('daily_org', $daily_org);
			$this->assign('leader', $organization->leader());
			$this->assign('is_leader', $organization->player_id == $player->id);
			$this->assign('players', $organization->players());
			$this->assign('requests', $organization->requests());
			$this->assign('can_kick', $can_kick);
			$this->assign('can_accept', $can_accept);
			$this->assign('player', $player);
			$this->assign('errors', $errors);
			
		} else {
			$this->render	= 'show_error';
		}
	}
}