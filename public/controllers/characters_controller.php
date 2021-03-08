<?php
class CharactersController extends Controller {
	function create() {
		$user	= User::get_instance();
		$total	= Player::find("user_id=".$user->id);
		
		if($_POST) {
			$this->layout			= false;
			$this->as_json			= true;
			$this->render			= false;
			$this->json->success	= false;
			$errors					= array();

			if(!isset($_POST['name']) || (isset($_POST['name']) && !preg_match(REGEX_NAME, $_POST['name']))) {
				$errors[]	= t('characters.create.errors.invalid_name');
			} else {
				if(strlen($_POST['name']) > 14) {
					$errors[]	= t('characters.create.errors.name_length_max');
				}

				if(strlen($_POST['name']) < 6) {
					$errors[]	= t('characters.create.errors.name_length_min');
				}

				if(Player::find('name="' . addslashes($_POST['name']) . '"')) {
					$errors[]	= t('characters.create.errors.existent');
				}
				if(sizeof($total) >= $user->character_slots){
					$errors[]	= t('characters.title_chars');
				}
			}

			if(!isset($_POST['character_id']) || (isset($_POST['character_id']) && !Character::includes($_POST['character_id']))) {
				$errors[]	= t('characters.create.errors.invalid_character');
			} else {
				$character	= Character::find($_POST['character_id'], ['cache' => true]);

				if (!$character->unlocked($user)) {
					$errors[]	= t('characters.create.errors.locked');
				}
			}

			if (!isset($_POST['faction_id']) || (isset($_POST['faction_id']) && !is_numeric($_POST['faction_id'])) || !in_array($_POST['faction_id'], [1, 2])) {
				$errors[]	= t('characters.create.errors.invalid_faction');
			}

			if(!sizeof($errors)) {
				$this->json->success	= true;
				$theme					= Character::find($_POST['character_id'])->default_theme();

				$player								= new Player();
				$player->user_id					= $_SESSION['user_id'];
				$player->name						= $_POST['name'];
				$player->faction_id					= $_POST['faction_id'];
				$player->character_id				= $_POST['character_id'];
				$player->character_theme_id			= $theme->id;
				$player->character_theme_image_id	= $theme->images()[0]->id;
				$player->last_login					= now(true);
				$player->save();
				
				//Adiciona as Habilidades do jogador
				$character_abilities = CharacterAbility::find("character_id=" . $player->character_id);	
				foreach ($character_abilities as $character_ability){
					$player_character_ability = new PlayerCharacterAbility();
					$player_character_ability->player_id = $player->id;
					$player_character_ability->character_ability_id = $character_ability->id;
					$player_character_ability->character_id = $player->character_id;
					$player_character_ability->item_effect_ids = $character_ability->item_effect_ids;
					$player_character_ability->effect_chances = $character_ability->effect_chances;
					$player_character_ability->effect_duration = $character_ability->effect_duration;
					$player_character_ability->consume_mana = $character_ability->consume_mana;
					$player_character_ability->cooldown = $character_ability->cooldown;
					$player_character_ability->is_initial = $character_ability->is_initial;
					$player_character_ability->save();
					
				} 
				//Adiciona as Especialidades do jogador
				$character_specialities = CharacterSpeciality::find("character_id=" . $player->character_id);	
				foreach ($character_specialities as $character_speciality){
					$player_character_speciality = new PlayerCharacterSpeciality();
					$player_character_speciality->player_id = $player->id;
					$player_character_speciality->character_speciality_id = $character_speciality->id;
					$player_character_speciality->character_id = $player->character_id;
					$player_character_speciality->item_effect_ids = $character_speciality->item_effect_ids;
					$player_character_speciality->effect_chances = $character_speciality->effect_chances;
					$player_character_speciality->effect_duration = $character_speciality->effect_duration;
					$player_character_speciality->consume_mana = $character_speciality->consume_mana;
					$player_character_speciality->cooldown = $character_speciality->cooldown;
					$player_character_speciality->is_initial = $character_speciality->is_initial;
					$player_character_speciality->save();
					
				}
			} else
				$this->json->errors	= $errors;
		} else {
			$animes	= Anime::find($_SESSION['universal'] ? '1=1 AND playable=1' : 'active=1 AND playable=1', ['cache' => true, 'reorder' => 'id ASC']);

			$this->assign('user', $user);
			$this->assign('total', $total);
			$this->assign('animes', $animes);
			$this->assign('formulas', [
				'for_atk'	=> t('formula.for_atk'),
				'for_def'	=> t('formula.for_def'),
				'for_crit'	=> t('formula.for_crit'),
				'for_abs'	=> t('formula.for_abs'),
				'for_prec'	=> t('formula.for_prec'),
				'for_init'	=> t('formula.for_init'),
			]);
		}
	}
	function select() {
		if ($_POST) {
			$this->layout			= false;
			$this->as_json			= true;
			$this->render			= false;
			$this->json->success	= false;
			$errors					= array();
			$current_player			= $_SESSION['player_id'] ? Player::get_instance() : false;
			
			if (!isset($_POST['id']) || (isset($_POST['id']) && !is_numeric($_POST['id'])))
				$errors[]	= t('characters.select.errors.invalid');
			else {
				$player					= Player::find($_POST['id']);
				$player->last_login		= now(true);
				$player->save();
				
				if ($player->user_id != $_SESSION['user_id']) {
					$errors[]	= t('characters.select.errors.user_match');
				}
				if ($player->banned) {
					$errors[]	= t('characters.select.errors.banned');
				}
				if ($current_player && $current_player->is_pvp_queued) {
					$errors[]	= t('characters.select.errors.pvp_queue');
				}
			}

			if (!sizeof($errors)) {
				$this->json->success	= true;
				$_SESSION['player_id']	= $player->id;

				if ($current_player) {
					$user_items = UserPlayerItem::find("user_id=".$player->user_id);
					if ($user_items) {
						foreach ($user_items as $user_item) {
							$player_item_exists		= PlayerItem::find_first("item_id=". $user_item->item_id." AND player_id=".$player->id);
							if (!$player_item_exists) {
								$player_item			= new PlayerItem();
								$player_item->item_id	= $user_item->item_id;
								$player_item->player_id	= $player->id;
								$player_item->removed	= 1;
								$player_item->save();
							}
						}	
					}
				}

				/* Primeiro acesso após reset */
				if (!$player->first_actions) {
					# Add os Golpes
					$slotId	= 0;
					$techniques		= Item::find('is_initial = 1 and item_type_id = 1', [
						'cache'		=> TRUE,
						'reorder'	=> 'id asc',
						'limit'		=> '10'
					]);
					foreach ($techniques as $technique) {
						$playerTechnique				= new PlayerItem();
						$playerTechnique->player_id		= $player->id;
						$playerTechnique->item_id		= $technique->id;
						$playerTechnique->equipped		= 1;
						$playerTechnique->slot_id		= $slotId;
						$playerTechnique->save();

						++$slotId;
					}

					# Atualiza personagem
					$player->currency			= INITIAL_MONEY;
					$player->first_actions		= 1;
					$player->save();

					$player->welcome();
				}
				/* /Primeiro acesso após reset */
			} else
				$this->json->errors	= $errors;
		} else
			$this->assign('players', Player::find('user_id=' . $_SESSION['user_id'], [
				'reorder'	=> 'level DESC'
			]));
	}
	function remove($id	= null, $key = null) {
		if (is_numeric($id) && $key) {
			$player	= Player::find_first('id = ' . $id . ' AND remove_key="' . addslashes($key) . '"');
			$user	= User::get_instance();
			$player_removed = Recordset::query('select * from players WHERE user_id='.$user->id.' AND removed=1')->result_array();

			$errors	= [];

			if (!$player)
				$errors[]	= t('characters.remove.not_found');
			else {
				if (sizeof($player_removed) >= $user->character_slots){
					$errors[]	= t('characters.create.errors.removed');
				}
				if ($player->user_id != $_SESSION['user_id']) {
					$errors[]	= t('characters.remove.same_user');
				}
				if ($player->id == $_SESSION['player_id']) {
					$errors[]	= t('characters.remove.same_player');
				}
				if ($player->organization_id) {
					$errors[]	= t('characters.remove.organization');
				}
			}

			if (!sizeof($errors)) {
				// Exclui suas amizades da lista de pendencia
				$friend_players_requests = PlayerFriendRequest::find("friend_id=". $player->id);
				$player_friends_requests = PlayerFriendRequest::find("player_id=". $player->id);
				
				if ($friend_players_requests) {
					foreach ($friend_players_requests as $friend_players_request) {
						$friend_players_request->destroy();
					}
				}
				if ($player_friends_requests) {
					foreach ($player_friends_requests as $player_friends_request) {
						$player_friends_request->destroy();
					}
				}
				
				// Exclui suas amizades
				$friend_players = PlayerFriendList::find("friend_id=". $player->id);
				$player_friends = PlayerFriendList::find("player_id=". $player->id);
				
				if($friend_players){
					foreach($friend_players as $friend_player){
						$friend_player->destroy();
					}
				}
				if($player_friends){
					foreach($player_friends as $player_friend){
						$player_friend->destroy();
					}
				}
				
				$player->destroy();
				
				redirect_to('characters#select?deleted_ok');
			} else {
				$messages	= [];

				foreach ($errors as $error) {
					$messages[]	= '<li>' . $error . '</li>';
				}

				$this->assign('messages', '<ul>' . implode('', $messages) . '</ul>');
				$this->render	= 'remove_error';
			}
		} else {
			$this->layout			= false;
			$this->as_json			= true;
			$this->render			= false;
			$this->json->success	= false;
			$errors					= array();

			if(isset($_POST['id']) && is_numeric($_POST['id'])) {
				$player	= Player::find($_POST['id']);
				$user	= User::get_instance();
				$player_removed = Recordset::query('select * from players WHERE user_id='.$user->id.' AND removed=1')->result_array();
									
				if(!$player) {
					$errors[]	= t('characters.remove.not_found');
				} else {
					if(sizeof($player_removed) >= $user->character_slots){
						$errors[]	= t('characters.create.errors.removed');
					}
					if($player->user_id != $_SESSION['user_id']) {
						$errors[]	= t('characters.remove.same_user');
					}
					if($player->id == $_SESSION['player_id']) {
						$errors[]	= t('characters.remove.same_player');
					}
					if($player->organization_id) {
					$errors[]	= t('characters.remove.organization');
					}
				}
			} else {
				$errors[]	= t('characters.remove.invalid');				
			}

			if(!sizeof($errors)) {
				$this->json->success	= true;
				$player->remove_key		= uniqid();
				$player->save();

				CharacterMailer::dispatch('character_deleted', [User::get_instance(), $player]);
			} else {
				$this->json->errors	= $errors;
			}
		}
	}

	function status() {
		$player			= Player::get_instance();
		$user			= User::get_instance();
		
		$player_stat	= PlayerStat::find_first("player_id=". $player->id);
		
		// Começando o novo modulo de missão de conta
		$user_quest_counter = UserQuestCounter::find_first("user_id=". $player->user_id);
		if(!$user_quest_counter){
			$user_quest_counter = new UserQuestCounter();
			$user_quest_counter->user_id = $player->user_id;
			$user_quest_counter->save();
		}
		
		// Começando o novo modulo de recompensa diária
		$player_fidelity = PlayerFidelity::find_first("player_id=". $player->id);
		if(!$player_fidelity){
			$player_fidelity = new PlayerFidelity();
			$player_fidelity->player_id = $player->id;
			$player_fidelity->day = 1;	
			$player_fidelity->save();
		}
		
		$formulas	= [
			'for_atk'		=> t('formula.for_atk'),
			'for_def'		=> t('formula.for_def'),
			'for_crit'		=> t('formula.for_crit'),
			'for_crit_inc'	=> t('formula.for_inc_crit'),
			'for_abs'		=> t('formula.for_abs'),
			'for_abs_inc'	=> t('formula.for_inc_abs'),
			'for_prec'		=> t('formula.for_prec'),
			'for_init'		=> t('formula.for_init'),
		];

		$max	= 0;
		foreach ($formulas as $_ => $formula) {
			$value	= $player->{$_}();
			
			if($value > $max) {
				$max	= $value;
			}
		}

		$this->assign('player',					$player);
		$this->assign('stat',					$player_stat);
		$this->assign('formulas',				$formulas);
		$this->assign('quest_counters',			$player->quest_counters());
		$this->assign('user_quest_counters',	$user->quest_counters());
		$this->assign('player_tutorial',		$player->player_tutorial());
		$this->assign('max',					$max);
		$this->assign('player',					$player);
	}
	function list_images_only() {
		$this->layout	= false;
		$player			= Player::get_instance();
		
		$this->assign('images', CharacterTheme::find($_GET['theme_id'], array('cache' => true))->images());
	}
	function list_images() {
		$this->layout	= false;
		$player			= Player::get_instance();
		$user			= User::get_instance();

		if($_POST) {
			$this->as_json			= true;
			$this->render			= false;
			$this->json->success	= false;
			$errors					= array();
			$user_image 			= UserCharacterThemeImage::find_first("user_id=".$user->id." AND character_theme_image_id=".$_POST['id']);
			if(is_numeric($_POST['id'])) {
				$image	= CharacterThemeImage::find($_POST['id']);

				if(!$image) {
					$errors[]	= t('character.status.change_image.errors.invalid');
				} else {
					if($image->character_theme_id != $player->character_theme_id) {
						$errors[]	= t('character.status.change_image.errors.theme');
					}

					if($image->character_theme()->character_id != $player->character_id) {
						$errors[]	= t('character.status.change_image.errors.belongs');
					}
					if(!$user_image){
						if($image->is_buyable  && ($user->credits < $image->price_credits)) {
							$errors[]	= "Você não tem créditos para comprar essa imagem";
						}
					}
				}
			} else {
				$errors[]	= t('character.status.change_image.errors.invalid');
			}

			if(!sizeof($errors)) {
				$this->json->success				= true;
				
				if($image->is_buyable){
					if(!$user_image){
						$user_character_theme_image								= new UserCharacterThemeImage();
						$user_character_theme_image->user_id					= $user->id;
						$user_character_theme_image->character_theme_image_id	= $_POST['id'];
						$user_character_theme_image->price_credits				= $image->price_credits;
						$user_character_theme_image->save();
						
						$user->credits -= 5;
						$user->save();
					}
						
				}
				
				$player->character_theme_image_id	= $_POST['id'];
				$player->save();
			} else {
				$this->json->errors	= $errors;
			}
		} else {
			$this->assign('user', $user);
			$this->assign('images', $player->character_theme()->images());
		}
	}
	function list_themes() {
		$this->layout	= false;
		$player			= Player::get_instance();
		$user			= User::get_instance();

		if($_POST) {
			$this->as_json			= true;
			$this->render			= false;
			$this->json->success	= false;
			$errors					= array();

			if(isset($_POST['theme']) && is_numeric($_POST['theme'])) {
				$theme	= CharacterTheme::find($_POST['theme']);
				if(!$theme) {
					$errors[]	= t('characters.themes.errors.invalid');
				} else {
					if($_POST['type']){	
						if($theme->character()->id != $player->character()->id) {
							$errors[]	= t('characters.themes.errors.character');
						}
					}
					if(isset($_POST['buy']) && isset($_POST['mode']) && is_numeric($_POST['mode'])) {
						if(!$theme->is_buyable){
							$errors[]	= t('characters.themes.errors.invalid');
						}
						if($_POST['mode'] == 2 && !$theme->price_currency){
							$errors[]	= t('characters.themes.errors.invalid');
						}
						if($_POST['mode'] == 1 && !$theme->price_credits){
							$errors[]	= t('characters.themes.errors.invalid');
						}
						if($theme->price_credits || $theme->price_currency) {
							if($theme->price_credits && $theme->price_credits > $user->credits && $_POST['mode']==1) {
								$errors[]	= t('characters.themes.errors.enough_credits');
							}

							if($theme->price_currency && $theme->price_currency > $player->currency && $_POST['mode']==2) {
								$errors[]	= t('characters.themes.errors.enough_currency', array('currency' => t('currencies.' . $player->character()->anime_id)));
							}
						}
					} elseif($_POST['use']) {
						if(!$theme->is_default && !$user->is_theme_bought($_POST['theme'])) {
							$errors[]	= t('characters.themes.errors.not_bought');
						}
					} else {
						$errors[]	= t('characters.themes.errors.operation');
					}
				}
			} else {
				$errors[]	= t('characters.themes.errors.invalid');
			}					

			if(!sizeof($errors)) {
				$this->json->success	= true;

				if(isset($_POST['buy'])) {
					$user_theme						= new UserCharacterTheme();
					$user_theme->user_id			= $user->id;
					$user_theme->character_theme_id	= $_POST['theme'];
					$user_theme->price_credits		= $theme->price_credits;
					$user_theme->price_currency		= $theme->price_currency;						
					$user_theme->save();
					
					if($_POST['type']){
						$image								= $theme->first_image();
						$player->character_theme_id			= $theme->id;
						$player->character_theme_image_id	= $image->id;
						$player->save();
					}
					
					
					if($_POST['mode'] == 1){
						if($theme->price_credits) {
							$user->spend($theme->price_credits);
						}
					}else{
						if($theme->price_currency) {
							$player->spend($theme->price_currency);
						}
					}
					
					// Verifica se o jogador comprou o tema - Conquista
					$player->achievement_check("character_theme");
					// Objetivo de Round
					$player->check_objectives("character_theme");
					
				} elseif($_POST['use']) {
					$image								= $theme->first_image();
					$player->character_theme_id			= $theme->id;
					$player->character_theme_image_id	= $image->id;
					$player->save();
				}
			} else {
				$this->json->errors	= $errors;
			}
		} else {
			$filter = "";
			if(!$_SESSION['universal']){
				$filter = " AND active=1";
			}
			$this->assign('user', $user);

			if(isset($_GET['show_only'])) {
				if(isset($_GET['character']) && is_numeric($_GET['character'])) {
					$this->assign('player', false);
					$this->assign('themes', CharacterTheme::find('character_id=' . $_GET['character'] . $filter));
					$this->assign('character', Character::find($_GET['character']));
				} else {
					$this->denied	= true;
				}
			} else {
				$this->assign('player', $player);
				$this->assign('character', $player->character());
				$this->assign('themes', CharacterTheme::find('character_id=' . $player->character_id . $filter));
			}
		}			
	}

	function talents() {
		$items	= Item::find("item_type_id=6 ORDER BY mana_cost ASC");
		$player	= Player::get_instance();
		$user	= User::get_instance();
		$list	= [];

		if($_POST) {
			$this->as_json			= true;
			$this->json->success	= false;
			$errors					= [];

			if($player->has_item($_POST['item_id'])) {
				$errors[]	= t('characters.talents.errors.already');
			} else {
				if(!is_numeric($_POST['item_id'])) {
					$errors[]	= t('characters.talents.errors.invalid');
				} else {
					$item	= Item::find($_POST['item_id']);

					if($item->item_type_id != 6) {
						$errors[]	= t('characters.talents.errors.invalid');
					} else {
						$levels_learned	= [];

						foreach ($player->learned_talents() as $talent) {
							$levels_learned[$talent->item()->mana_cost]	= true;
						}

						if(isset($levels_learned[$item->mana_cost])) {
							$errors[]	= t('characters.talents.errors.tree_level');
						}

						$reqs	= $item->has_requirement($player);

						if(!$reqs['has_requirement']) {
							$errors[]	= t('characters.talents.errors.requirements');
						}
					}
				}
			}

			if (!sizeof($errors)) {
				$this->json->success	= true;

				$player->add_talent($item);
			} else {
				$this->json->messages	= $errors;
			}
		} else {
			foreach($items as $item) {
				$lvl	= $item->mana_cost;

				if(!isset($list[$lvl])) {
					$list[$lvl]	= [];
				}

				$item->set_anime($player->character()->anime_id);

				$list[$lvl][]	= $item;
			}

			$this->assign('list', $list);
			$this->assign('player', $player);
			$this->assign('user', $user);
			$this->assign('player_tutorial', $player->player_tutorial());
		}
	}

	function next_level() {
		$player	=& Player::get_instance();

		if ($_POST) {
			/*$player->exp				-= $player->level_exp();
			$player->level_screen_seen	= 1;
			$player->less_mana	        = 0;
			$player->less_life	        = 0;
			$player->less_stamina	    = 0;
			$player->level++;
			$player->save();*/

			redirect_to('characters#status');
		} else {
			$this->assign('player', $player);
		}
	}

	function inventory() {
		// REMOVED = 7
		$ids			= [5, 10, 12, 13];
		$consumables	= [5];
		$player			=& Player::get_instance();
		$items			= [];
		$errors			= [];
		$results		= Recordset::query('
			SELECT
				a.id

			FROM
				player_items a JOIN items b ON b.id=a.item_id

			WHERE
				b.item_type_id IN(' . implode(',', $ids) . ')
				AND a.player_id=' . $player->id);

		foreach ($results->result_array() as $result) {
			$items[]	= PlayerItem::find($result['id']);
		}

		if($_POST) {
			$this->as_json	= true;

			if(!isset($_POST['item']) || (isset($_POST['item']) && !is_numeric($_POST['item']))) {
				$errors[]	= t('characters.inventory.errors.invalid');
			} else {
				if($player->has_item($_POST['item'])) {
					$player_item	= $player->get_item($_POST['item']);
					$item			= $player_item->item();

					if(!in_array($item->item_type_id, $consumables)) {
						$errors[]	= t('characters.inventory.errors.allowed');
					}
				} else {
					$errors[]	= t('characters.inventory.errors.existent');
				}

				if($item->item_type_id == 5 && $player->less_life <= 0 && $item->for_life > 0) {
					$errors[]	= t('characters.create.errors.life');
				}
				if($item->item_type_id == 5 && $player->less_mana <= 0 && $item->for_mana > 0) {
					$errors[]	= t('characters.create.errors.mana', [
						'mana' => strtolower(t('formula.for_mana.' . $player->character()->anime()->id))
					]);
				}
				if($item->item_type_id == 5 && $player->less_stamina <= 0 && $item->for_stamina > 0) {
					$errors[]	= t('characters.create.errors.stamina');
				}
					
				if($player->hospital) {
					$errors[]	= t('characters.inventory.errors.hospital');
				}

				if($player->battle_pvp_id || $player->battle_npc_id) {
					$errors[]	= t('characters.inventory.errors.battle');
				}
			}

			if(!sizeof($errors)) {
				switch($item->item_type_id) {
					case 5:
						$player->less_life				-= $item->for_life;
						$player->less_mana				-= $item->for_mana;
						$player->less_stamina			-= $item->for_stamina;

						if($player->less_life <= 0) {
							$player->less_life	= 0;
						}

						if($player->less_mana <= 0) {
							$player->less_mana	= 0;
						}

						if($player->less_stamina <= 0) {
							$player->less_stamina	= 0;
						}

						$player->save();

						break;
				}

				$this->json->life			= $player->for_life();
				$this->json->max_life		= $player->for_life(true);
				$this->json->mana			= $player->for_mana();
				$this->json->max_mana		= $player->for_mana(true);
				$this->json->stamina		= $player->for_stamina();
				$this->json->max_stamina	= $player->for_stamina(true);

				if($player_item->quantity - 1 <= 0) {
					$player_item->destroy();

					$this->json->delete	= true;
				} else {
					$player_item->quantity--;
					$player_item->save();

					$this->json->quantity	= $player_item->quantity;
				}

				$this->json->success	= true;
			} else {
				$this->json->messages	= $errors;
			}
		} else {
			$this->layout	= false;

			$this->assign('player', $player);
			$this->assign('player_items', $items);
			$this->assign('consumables', $consumables);
			$this->assign('types', ItemType::all());
		}
	}
	function fragments(){
		$player		= Player::get_instance();
		$total		= PlayerItem::find_first("player_id=". $player->id ." AND item_id=446");
		
		$this->assign('player', $player);
		$this->assign('total', $total);
		$this->assign('player_tutorial', $player->player_tutorial());	
	}
	function fragments_change(){
		$player		= Player::get_instance();
		$this->as_json			= true;
		$this->json->success	= false;
		$errors					= [];
		
		
		if (!isset($_POST['mode']) || (isset($_POST['mode']) && !is_numeric($_POST['mode']))) {
			$errors[]	= t('fragments.error1');
		} else {
			$item_446 = PlayerItem::find_first("player_id =". $player->id. " AND item_id=446");
			$prices	= [
						'100',
						'100'
						];
			if($item_446->quantity < $prices[$_POST['mode']]){
				$errors[]	= t('fragments.error2');
			}
			
			if(!sizeof($errors)) {
				
				$item_446->quantity -= $prices[$_POST['mode']];
				$item_446->save(); 
				
				// Faz a premiação referente ao mode que o jogador escolheu!
				switch($_POST['mode']){
					case 0:
						//Equipamento aleatorio
						Item::generate_equipment($player);
						
						$message = "Você ganhou um Equipamento Aleatório, visite a página de Equipamentos para mais detalhes!";
						$message = base64_encode($message);
					break;
					case 1:
						// Dá um pet random!
						$npc_pet	= Item::find_first('item_type_id=3 AND is_initial=1', ['reorder' => 'RAND()']);
						if (!$player->has_item($npc_pet->id)) {
						
							$player_pet						= new PlayerItem();
							$player_pet->item_id			= $npc_pet->id;
							$player_pet->player_id			= $player->id;
							$player_pet->save();
							
							$message = "Você ganhou o Mascote: ". $npc_pet->description()->name;
							$message = base64_encode($message);
						}else{
							$item_446 = PlayerItem::find_first('item_id=446 AND player_id=' . $player->id);
							
							if($item_446){
								$item_446->quantity += 25;
								$item_446->save();
							}else{
								$item_446				= new PlayerItem();						
								$item_446->item_id		= 446;
								$item_446->player_id	= $player->id;
								$item_446->quantity 	+= 25;
								$item_446->save();
							}
							
							$message = "Você já possuia o Mascote sorteado e por isso ganhou <strong>25 Fragmentos de Alma</strong>.";	
							$message = base64_encode($message);
						}
					break;
				}
				
				// Adiciona o contador de aprimoramentos
				$upgrade_counter = PlayerStat::find_first("player_id=".$player->id);
				$upgrade_counter->fragments++;	
				$upgrade_counter->save();
				// Adiciona o contador de aprimoramentos
				
				//Verifica a conquista de fragmentos - Conquista
				$player->achievement_check("fragments");
				// Objetivo de Round
				$player->check_objectives("fragments");
				
				// Manda o id do premio para o json
				$this->json->message = $message;
				
				$this->json->success	= true;
			}else{
				$this->json->messages	= $errors;
			}
			
			
		}
	}
	function generate_equipment($player, $rarity_fragment = NULL, $slot = NULL) {
		$rarities	= [
			'common',
			'rare',
			'legendary'
		];

		$slots	= [
			'head',
			'shoulder',
			'chest',
			'neck',
			'hand',
			'leggings'
		];

		$attributes_by_slot	= [
			'head'		=> [],
			'shoulder'	=> [],
			'chest'		=> [],
			'neck'		=> [],
			'hand'		=> [],
			'leggings'	=> []
		];

		$ignore_sums	= ['cooldown_reduction', 'for_stamina', 'npc_battle_count'];

		$choosen_slot	= $slots[rand(0, sizeof($slots) - 1)];

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
			'common'	=> [1],
			'rare'		=> [2],
			'legendary'	=> [3]
		];

		$additional_by_graduation	= [
			'common'	=> 1,
			'rare'		=> 2,
			'legendary'	=> 3
		];

		$additional_chance_by_graduation	= [
			[10, 35, 50, 5, 1],
			[15, 30, 45, 10, 1],
			[20, 25, 40, 15, 1],
			[25, 20, 35, 20, 1],
			[30, 15, 30, 25, 1],
			[35, 10, 25, 30, 1]
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
				], 					[
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
					'for_atk'		=> [1, 6, 4, 11],
					'for_def'		=> [1, 6, 4, 11],
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
		$have_extras			= false;
		
		if(is_null($rarity_fragment)){
			while(true) {
				$rarity_choosen_id	= 0;

				foreach ($rarity_base as $rarity => $chance) {
					if(rand(1, 100) <= $chance) {
						$rarity_choosen_name	= $rarity;
						break 2;
					}

					$rarity_choosen_id++;
				}
			}
		}else{
			switch($rarity_fragment){
				case 0:
					$rarity_choosen_name = "common";
					$rarity_choosen_id = 0;
				break;
				case 1:
					$rarity_choosen_name = "rare";
					$rarity_choosen_id = 1;
				break;
				case 2:
					$rarity_choosen_name = "legendary";
					$rarity_choosen_id = 2;
				break;
			}	
		}

		foreach ($bases as $block => $base) {
			$attribute_counter	= $bonuses_by_rarity[$rarity_choosen_name][$block];
			$choosen_attributes	= $base[$current_grad - 1];
			$extras				= $additional_chance_by_graduation[$current_grad - 1];
			$extra_chance		= $extras[$block];
			
	
			if(rand(1, 100) <= 25 && rand(1, 100) <= $extra_chance && !$have_extras) {
				$attribute_counter	+= $extras[4];
				$have_extras		= true;
			}

			if ($attribute_counter) {
				while(true) {
					foreach ($base[$current_grad - 1] as $attribute => $value) {
				
						if(in_array($attribute, $attributes_by_slot[$choosen_slot])) {
							continue;
						}

						if(isset($values[$attribute])) {
							continue;
						}

						if(rand(1, 100) > 10) {
							continue;
						}

						if(!in_array($attribute, $ignore_sums)) {
							if($rarity_choosen_id == 2) {
								$value[0]++;
							}

							if($rarity_choosen_id == 3 || $rarity_choosen_id == 4) {
								$value[0]	+= 2;
							}

							//$values[$attribute]	= rand($value[0], $value[1] + (($rarity_choosen_id) * 2));
							$values[$attribute]	= rand($value[0]*10, $value[1]*10)/10;
							//$values[$attribute]	= rand($value[0], $value[1]);	
						} else {
							$values[$attribute]	= rand($value[0]*10, $value[1]*10)/10;
							//$values[$attribute]	= rand($value[0], $value[1]);	
						}

						if (isset($choosables[$attribute])) {
							$items_query	= Recordset::query('SELECT id FROM items WHERE item_type_id=1 AND id NOT IN(112, 113)', true)->result_array();

							//$values[$attribute]					= 1;
							$values[$choosables[$attribute]]	= $items_query[rand(0, sizeof($items_query) - 1)]['id'];
						}

						$attribute_counter--;

						if(!$attribute_counter) {
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
			$attribute->{$property}	= $value;
		}

		$attribute->save();

		return $player_item;
	}
	function pets() {
		$player		= Player::get_instance();
		$mine_pets	= [];
		$active_pet	= 0;
		$page		= isset($_POST['page']) && is_numeric($_POST['page']) ? $_POST['page'] : 0;
		$limit		= 20;
		$filter		= '';
		
		if(!$_POST) {
			$filter				.= '';
			$name				= '';
			$description		= '';
			$raridade			= 'todos';
			$active				= 1;
		}else{
			if(isset($_POST['name']) && strlen(trim($_POST['name']))) {
				$filter	.= ' AND a.name LIKE "%' . addslashes($_POST['name']) . '%"';
				$name	= $_POST['name'];
			} else {
				$name	= '';
			}
			if(isset($_POST['description']) && strlen(trim($_POST['description']))) {
				$filter	.= ' AND a.description LIKE "%' . addslashes($_POST['description']) . '%"';
				$description	= $_POST['description'];
			} else {
				$description	= '';
			}
			if(isset($_POST['raridade']) && strlen(trim($_POST['raridade'])) && ($_POST['raridade']!="todos")) {
				$filter	.= ' AND b.rarity ="' . addslashes($_POST['raridade']) . '"';
				$raridade	= $_POST['raridade'];
			} else {
				$raridade	= 'todos';
			}
			if(isset($_POST['active']) && is_numeric($_POST['active'])) {
				if($_POST['active'] != 0) {
					
				}
				$active	= $_POST['active'];
			} else {
				$active	= 1;
			}
		}
		
					
		foreach ($player->pets() as $pet) {
			$mine_pets[$pet->item_id]	= $pet->id;

			if ($pet->equipped) {
				$active_pet	= $pet->item_id;
			}
		}

		$result		= (new Character)->filter($filter, $mine_pets, $player->id, $active, $page, $limit);
		$animes		= Anime::find('active=1', ['cache' => true]);
		
		$this->assign('mine_pets', $mine_pets);
		$this->assign('active_pet', $active_pet);
		$this->assign('player', $player);
		$this->assign('animes', $animes);
		//$this->assign('pets', Item::find('item_type_id=3'));
		//$this->assign('pets', $result['pets']->result_array());
		$this->assign('pages', $result['pages']);
		$this->assign('page', $page);
		$this->assign('pets', $result);
		$this->assign('name', $name);
		$this->assign('description', $description);
		$this->assign('raridade', $raridade);
		$this->assign('active', $active);
		$this->assign('player_tutorial', $player->player_tutorial());
	}
	function remove_pet() {
		$this->as_json			= true;
		$this->json->success	= false;
		$errors					= [];
		$player					= Player::get_instance();
		
		if (!isset($_POST['id']) || (isset($_POST['id']) && !is_numeric($_POST['id']))) {
			$errors[]	= t('quests.pets.errors.invalid');
		} else {
			$item	= PlayerItem::find_first('player_id=' . $player->id . ' AND item_id=' . $_POST['id']);
			if($item){
				if ($item->item()->item_type_id != 3 || $item->equipped == 0) {
					$errors[]	= t('quests.pets.errors.invalid');
				}
			}else{
				$errors[]	= t('quests.pets.errors.invalid');
			}
		}

		if (!sizeof($errors)) {
			$item->equipped	= 0;
			$item->save();

			$this->json->success	= true;
		} else {
			$this->json->messages	= $errors;
		}
	}
	function learn_pet() {
		$this->as_json			= true;
		$this->json->success	= false;
		$errors					= [];
		$player					= Player::get_instance();

		if (!isset($_POST['id']) || (isset($_POST['id']) && !is_numeric($_POST['id']))) {
			$errors[]	= t('quests.pets.errors.invalid');
		} else {
			$item	= PlayerItem::find_first('player_id=' . $player->id . ' AND id=' . $_POST['id']);
				
			if ($item->item()->item_type_id != 3 || $item->working == 1) {
				$errors[]	= t('quests.pets.errors.invalid');
			}
		}

		if (!sizeof($errors)) {
			foreach ($player->pets() as $pet) {
				$pet->equipped	= 0;
				$pet->save();
			}

			$item->equipped	= 1;
			$item->save();

			$this->json->success	= true;
		} else {
			$this->json->messages	= $errors;
		}
	}

	function show_lock_info($id) {
		if (!is_numeric($id)) {
			$this->denied	= true;
		} else {
			$this->layout	= false;
			$player			= Player::get_instance();
			$user			= User::get_instance();
			$character		= Character::find($id);

			if (!$character || $character && $character->unlocked($user)) {
				$this->denied	= true;
			} else {
				$this->assign('character', $character);
				$this->assign('user', $user);
				$this->assign('player', $player);
			}
		}
	}

	function unlock_character($id) {
		if (!is_numeric($id)) {
			$this->denied	= true;
		} else {
			$character	= Character::find($id);
			$player		= Player::get_instance();
			$user		= User::get_instance();

			if (!$character || $character && $character->unlocked($user)) {
				$this->denied	= true;
			} else {
				$this->as_json 			= true;
				$this->json->success	= false;
				$errors					= [];

				if ($_POST['method'] == 1) {
					if ($character->currency_lock) {
						if ($player->currency < $character->currency_lock) {
							$errors[]	= t('characters.unlock.errors.not_enough_currency');
						}
					} else {
						$errors[]	= t('characters.unlock.errors.unallowed_method');
					}
				} else {
					if ($character->credits_lock) {
						if ($user->credits < $character->credits_lock) {
							$errors[]	= t('characters.unlock.errors.not_enough_credits');
						}
					} else {
						$errors[]	= t('characters.unlock.errors.unallowed_method');
					}
				}

				if (!sizeof($errors)) {
					$this->json->success	= true;

					if ($_POST['method'] == 1) {
						$player->spend($character->currency_lock);
					} else {
						$user->spend($character->credits_lock);
					}

					$user_character					= new UserCharacter();
					$user_character->user_id		= $user->id;
					$user_character->character_id	= $id;
					$user_character->save();
					
					if($player){
						// verifica se desbloqueou novo personagem - conquista
						$player->achievement_check("character");
						// Objetivo de Round
						$player->check_objectives("character");
					}
					
				} else {
					$this->json->messages	= $errors;
				}
			}
		}
	}

	function change_headline() {
		$this->as_json			= true;
		$this->json->success	= false;
		$errors					= [];
		$player					= Player::get_instance();

		if (!isset($_POST['headline']) || (isset($_POST['headline']) && !is_numeric($_POST['headline']))) {
			$errors[]	= t('characters.change_headline.errors.invalid');
		} else {
			if ($_POST['headline']) {
				$headline	= UserHeadline::find($_POST['headline']);

				if ($headline->user_id != $player->user_id) {
					$errors[]	= t('characters.change_headline.errors.not_belongs');
				}
			}
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;
			
			$player->headline_id	= $_POST['headline'] ? $headline->headline_id : 0;
			$player->save();
		} else {
			$this->json->messages	= $errors;
		}
	}
	
	function tutorial() {
		$this->layout	= false;
		$player			= Player::get_instance();
		
		if($_POST) {
			$this->as_json			= true;
			$this->render			= false;
			$this->json->success	= false;
			$errors					= array();
			
			if(!sizeof($errors)) {
				$this->json->success  = true;
				
				$player_tutorial = PlayerTutorial::find_first("player_id=". $player->id);
				
				switch($_POST['id']){
					case 1:
						$player_tutorial->status = 1;
					break;
					case 2:
						$player_tutorial->habilidades = 1;
					break;
					case 3:
						$player_tutorial->pets = 1;
					break;
					case 4:
						$player_tutorial->equips = 1;
					break;	
					case 5:
						$player_tutorial->escola = 1;
					break;
					case 6:
						$player_tutorial->mercado = 1;
					break;	
					case 7:
						$player_tutorial->ramen = 1;
					break;
					case 8:
						$player_tutorial->treinamento = 1;
					break;
					case 9:
						$player_tutorial->aprimoramentos = 1;
					break;
					case 10:
						$player_tutorial->golpes = 1;
					break;
					case 11:
						$player_tutorial->loja_pvp = 1;
					break;
					case 12:
						$player_tutorial->missoes_tempo = 1;
					break;
					case 13:
						$player_tutorial->missoes_pvp = 1;
					break;
					case 14:
						$player_tutorial->missoes_diarias = 1;
					break;
					case 15:
						$player_tutorial->missoes_seguidores = 1;
					break;
					case 16:
						$player_tutorial->battle_pvp = 1;
					break;
					case 17:
						$player_tutorial->battle_npc = 1;
					break;
					case 18:
						$player_tutorial->fidelity = 1;
					break;
					case 19:
						$player_tutorial->battle_village = 1;
					break;
					case 20:
						$player_tutorial->bijuus = 1;
					break;
					case 21:
						$player_tutorial->talents = 1;
					break;
					case 22:
						$player_tutorial->missoes_conta = 1;
					break;
					case 23:
						$player_tutorial->objectives = 1;
					break;	
						
				}
				$player_tutorial->save();	
			} else {
				$this->json->errors	= $errors;
			}	
		}
	}
}