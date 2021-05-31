<?php
	class TechniquesController extends Controller {
		function index() {
			$player			= Player::get_instance();
			$player_stats	= PlayerStat::find_first("player_id=".$player->id);
			if (!$player_stats->view_golpes) {
				$player_stats->view_golpes = 1;
				$player_stats->save();
			}

			$this->assign('player',				$player);
			$this->assign('items',				$player->character_theme()->attacks());
			$this->assign('player_tutorial',	$player->player_tutorial());
		}
		function list_golpes() {
			$this->layout	= FALSE;
			$player			= Player::get_instance();

			if ($_POST) {
				$this->as_json			= TRUE;
				$this->render			= FALSE;
				$this->json->success	= FALSE;

				$errors					= [];

				if (is_numeric($_POST['item_id']) && $_POST['item_id']) {
					$player_item	= PlayerItem::find_first('item_id = ' . $_POST['item_id'] . ' and player_id =  '. $player->id);
					if (!$player_item) {
						$errors[]	= t('enchant.errors.semogolpe');
					}
				} else {
					$errors[]	= t('enchant.errors.invalid');
				}

				if (!sizeof($errors)) {
					$this->json->success  = TRUE;

					// Remove os itens que estão work
					$player_item_works = PlayerItem::find("player_id = " . $player->id . " and working = 1");
					foreach ($player_item_works as $player_item_work) {
						$item_work = Item::find_first("id = " . $player_item_work->item_id . " and item_type_id = 1");
						if ($item_work) {
							$player_item_work->working = 0;
							$player_item_work->save();
						}
					}

					// Adiciona o Item para Trabalhar
					$player_item->working = 1;
					$player_item->save();

					// Adiciona o golpe na Player item Gem
					$player_item_gem = PlayerItemGem::find_first("player_id = " . $player->id . " and item_id = " . $player_item->item_id);
					if (!$player_item_gem) {
						$player_item_gem = new PlayerItemGem();
						$player_item_gem->player_id	= $player->id;
						$player_item_gem->item_id 	= $player_item->item_id;
						$player_item_gem->save();
					}
				} else {
					$this->json->errors	= $errors;
				}
			} else {
				$this->assign('player',	$player);
				$this->assign('items',	$player->character_theme()->attacks());
			}
		}
		function enchant() {
			$player			  = Player::get_instance();
			$gems 			  = Item::find("item_type_id = 15");
			$item_equipped	  = FALSE;

			$player_item_work = Recordset::query("select * from player_items where working = 1 and player_id=" . $player->id . " and item_id in (select id from items where item_type_id = 1)")->result_array();
			if ($player_item_work) {
				$item_equipped = Item::find_first("id = " . $player_item_work[0]['item_id']);
				$item_equipped->set_anime($player->character()->anime_id);

				if (!$item_equipped->is_generic) {
					$item_equipped->set_character_theme($player->character_theme_id);
				}

				// Retorna as combinações do item equipado
				$item_combinations = ItemGem::find_first("parent_id = " . $player_item_work[0]['item_id']);
				$this->assign('item_combinations',	$item_combinations);

				// Busca os Golpes aprimorados do golpe Equipado
				$item_enchanteds = Item::find("item_type_id = 1 AND parent_id = "  .$player_item_work[0]['item_id']);
				$this->assign('item_enchanteds',	$item_enchanteds);

				// Se tem item trabalhando, vamos puxas suas joias
				$player_item_gem = PlayerItemGem::find_first("player_id = " . $player->id . " and item_id = " . $player_item_work[0]['item_id']);
				$this->assign('player_item_gem',	$player_item_gem);
			}

			$this->assign('player',				$player);
			$this->assign('gems',				$gems);
			$this->assign('items',				$player->character_theme()->attacks());
			$this->assign('item_equipped',		$item_equipped);
			$this->assign('player_tutorial',	$player->player_tutorial());
		}
		function remove_gem() {
			$this->layout			= false;
			$this->as_json			= true;
			$this->json->success	= false;

			$player					= Player::get_instance();
			$errors					= [];

			if(isset($_POST['item_id']) && is_numeric($_POST['item_id']) && isset($_POST['counter']) && is_numeric($_POST['counter'])) {
				$player_item_gem		= PlayerItemGem::find_first("player_id=".$player->id." AND item_id=".$_POST['item_id']);
				$gem_slot 				= "gem_" . $_POST['counter'];

				if (!$player_item_gem) {
					$errors[]	= t('techniques.learn.learned');
				}
			} else {
				$errors[]	= t('techniques.learn.invalid');
			}

			if (!sizeof($errors)) {
				$this->json->success		= true;

				// Remove o Encantamento enquanto o jogador estiver mudando as gemas
				$player_item_encantado = PlayerItem::find_first("player_id=".$player->id." AND item_id=".$player_item_gem->item_id);
				$player_item_encantado->parent_id = 0;
				$player_item_encantado->save();

				// Adiciona o contador na pedra que ta sendo removida
				$player_item_old =  PlayerItem::find_first("player_id=".$player->id." AND item_id=".$player_item_gem->{$gem_slot});
				$player_item_old->quantity += 1;
				$player_item_old->save();

				// Remove a pedra do gems
				$player_item_gem->{$gem_slot} = 0;
				$player_item_gem->enchanted = 0;
				$player_item_gem->save();
			} else {
				$this->json->errors		= $errors;
			}
		}
		function equip_gem() {
			$this->layout			= false;
			$this->as_json			= true;
			$this->json->success	= false;

			$player					= Player::get_instance();
			$errors					= array();

			if (isset($_POST['item']) && is_numeric($_POST['item']) && is_numeric($_POST['slot'])) {
				$player_item_gem_show		= PlayerItem::find_first("player_id=".$player->id." AND item_id=".$_POST['item']);
				$gem_slot 					= "gem_".$_POST['slot'];

				if ($player_item_gem_show->quantity < 1) {
					$errors[]	= t('techniques.learn.learned');
				}
			} else {
				$errors[]	= t('techniques.learn.invalid');
			}

			if (!sizeof($errors)) {
				$this->json->success		= true;

				// Procura o item que esta trabalhando
				$player_item_work = Recordset::query("Select * from player_items WHERE working = 1 and player_id=".$player->id." and item_id in ( SELECT id FROM items WHERE item_type_id=1)")->result_array();

				// adicionando a gema no slot
				$player_item_gem = PlayerItemGem::find_first("player_id=".$player->id." AND item_id=".$player_item_work[0]['item_id']);
				if ($player_item_gem) {
					// Remove o Encantamento enquanto o jogador estiver mudando as gemas
					$player_item_encantado = PlayerItem::find_first("player_id=".$player->id." AND item_id=".$player_item_work[0]['item_id']);
					$player_item_encantado->parent_id = 0;
					$player_item_encantado->save();

					// Se tiver pedra no lugar que ele ta pondo, devolve para o player.
					if ($player_item_gem->{$gem_slot}) {
						$player_item_old = PlayerItem::find_first("player_id=".$player->id." AND item_id=".$player_item_gem->{$gem_slot});
						$player_item_old->quantity += 1;
						$player_item_old->save();
					}

					// Remove o item da quantidade da player_item
					$player_item_new = PlayerItem::find_first("player_id=".$player->id." AND item_id=".$_POST['item']);
					$player_item_new->quantity--;
					$player_item_new->save();

					$player_item_gem->{$gem_slot} = $_POST['item'];
					$player_item_gem->enchanted = 0;
					$player_item_gem->save();
				}
			} else {
				$this->json->errors		= $errors;
			}
		}
		function enchant_golpe() {
			$player					= Player::get_instance();
			$this->as_json			= true;
			$this->json->success	= false;
			$errors					= [];

			if (isset($_POST['item_id']) && is_numeric($_POST['item_id']) && isset($_POST['enchanted_id']) && is_numeric($_POST['enchanted_id']) && isset($_POST['combination']) && is_numeric($_POST['combination'])) {
				$item_gem 			= ItemGem::find_first("parent_id=".$_POST['enchanted_id']);
				$player_item_gems 	= PlayerItemGem::find_first("player_id=". $player->id." AND item_id=". $_POST['enchanted_id']);

				//Combinação original do item
				$combinations_gems	= explode(",", $item_gem->combination);

				switch ($_POST['combination']) {
					case 1:	$item_id = $item_gem->item_id_1;	break;
					case 2:	$item_id = $item_gem->item_id_2;	break;
					case 3:	$item_id = $item_gem->item_id_3;	break;
				}

				if ($item_id  != $_POST['item_id']) {
					$errors[]	= t('enchant.errors.stamina_invalida');
				}

				if ($_POST['combination'] == 1) {
					$player_combination_atual = $player_item_gems->gem_1 . '-' . $player_item_gems->gem_2;
					if ($player_item_gems->gem_1 == 0  ||  $player_item_gems->gem_2 == 0) {
						$errors[]	= "Você precisa equipar as Gemas para encantar seu golpe.";
					}
					if ($combinations_gems[0] != $player_combination_atual) {
						$errors[]	= "A combinação de Gemas é inválida para esse item";
					}
				} elseif ($_POST['combination'] == 2) {
					$player_combination_atual = $player_item_gems->gem_1 . '-' . $player_item_gems->gem_2 . '-' . $player_item_gems->gem_3;
					if ($player_item_gems->gem_1 == 0  ||  $player_item_gems->gem_2 == 0  ||  $player_item_gems->gem_3 == 0) {
						$errors[]	= "Você precisa equipar as Gemas para encantar seu golpe.";
					}
					if ($combinations_gems[1] != $player_combination_atual) {
						$errors[]	= "A combinação de Gemas é inválida para esse item";
					}
				} else {
					$player_combination_atual = $player_item_gems->gem_1.'-'.$player_item_gems->gem_2.'-'.$player_item_gems->gem_3.'-'.$player_item_gems->gem_4;
					if ($player_item_gems->gem_1 == 0  ||  $player_item_gems->gem_2 == 0  ||  $player_item_gems->gem_3 == 0 ||  $player_item_gems->gem_4 == 0) {
						$errors[]	= "Você precisa equipar as Gemas para encantar seu golpe.";
					}
					if ($combinations_gems[2] != $player_combination_atual) {
						$errors[]	= "A combinação de Gemas é inválida para esse item";
					}
				}
			} else {
				$errors[]	= t('enchant.errors.stamina_invalida');
			}

			if (!sizeof($errors)) {
				$this->json->success	= true;

				// Adiciona o Enchant na Player Item do Jogador
				$player_item = PlayerItem::find_first("player_id=".$player->id." AND item_id=".$item_gem->parent_id);
				$player_item->parent_id = $item_id;
				$player_item->save();

				// Marca o golpe como encantado
				$player_item_gem = PlayerItemGem::find_first("player_id=".$player->id." AND item_id=".$_POST['enchanted_id']);
				$player_item_gem->enchanted = 1;
				$player_item_gem->save();

				if ($player_item_gem->enchanted = 1 && $player_item_gem->gem_1 = 0) {
					$player_item_gem->enchanted = 0;
					$player_item = PlayerItem::find_first("player_id=".$player->id." AND item_id=0");
					$player_item_gem->save();
				}
			} else {
				$this->json->messages	= $errors;
			}
		}
		function create_gem() {
			$player					= Player::get_instance();
			$this->as_json			= true;
			$this->json->success	= false;
			$errors					= [];

			if (isset($_POST['create']) && is_numeric($_POST['create'])) {
				if($player->enchant_points_total < 2000){
					$errors[]	= t('enchant.frase3');
				}
			} else {
				$errors[]	= t('enchant.errors.stamina_invalida');
			}

			if (!sizeof($errors)) {
				$this->json->success	= true;

				// Sorteia a joia
				$gems = Item::find("item_type_id = 15 ORDER BY RAND()");
				foreach ($gems as $gem) {
					$rand 	= rand(1, 100);
					if ($rand <= $gem->drop_chance) {
						$player_item = PlayerItem::find_first("item_id=".$gem->id." AND player_id=".$player->id);
						if ($player_item) {
							$player_item->quantity += 1;
							$player_item->save();
						} else {
							$player_item = new PlayerItem();
							$player_item->player_id = $player->id;
							$player_item->item_id	= $gem->id;
							$player_item->rarity	= $gem->rarity;
							$player_item->quantity += 1;
							$player_item->save();
						}
						break;
					}
				}

				// Remove os pontos da Player
				$player->enchant_points_total -= 2000;
				$player->save();

				// Manda o id do premio para o json
				$this->json->premio	= $gem->id;
			} else {
				$this->json->messages	= $errors;
			}
		}
		function enchant_trainner() {
			$this->as_json			= true;
			$this->json->success	= false;

			$player					= Player::get_instance();
			$errors					= [];

			if (isset($_POST['stamina']) && is_numeric($_POST['stamina']) && $_POST['stamina'] > 0) {
				if ($player->enchant_points >= 3000 && !$_SESSION['universal']) {
					$errors[]	= t('enchant.errors.nao_pode_treinar');
				}

				if ($player->enchant_points_total >= 50000 && !$_SESSION['universal']) {
					$errors[]	= t('enchant.errors.nao_pode_treinar2');
				}

				//$stamina = ( 10 + $player->level ) - $player->less_stamina;
				if ($player->for_stamina() < $_POST['stamina'] && !$_SESSION['universal']) {
					$errors[]	= t('enchant.errors.sem_stamina');
				}
			} else {
				$errors[]	= t('enchant.errors.stamina_invalida');
			}

			if (!sizeof($errors)) {
				$this->json->success	= true;
				$pontos_ganhos = $_POST['stamina'] * (15 * 60 / $player->level);

				// Adiciona na player os pontos e remove a stamina
				if ($pontos_ganhos + $player->enchant_points > 3000) {
					$pontos_ganhos = 3000 - $player->enchant_points;
					$player->enchant_points = 3000;
				} else {
					$player->enchant_points += $pontos_ganhos;
				}

				if ($pontos_ganhos + $player->enchant_points_total > 50000) {
					$player->enchant_points_total += 50000 - $player->enchant_points_total;
				} else {
					$player->enchant_points_total += $pontos_ganhos;
				}

				if (!$_SESSION['universal']) {
					$player->less_stamina += $_POST['stamina'];
				}
				$player->save();
			} else {
				$this->json->messages	= $errors;
			}
		}
		function grimoire() {
			$player		= Player::get_instance();
			$anime_id	= $player->character()->anime_id;

			$items		= Recordset::query('
				SELECT
					*
				FROM
					item_descriptions a JOIN
					items b ON b.id=a.item_id
				WHERE
					a.anime_id=' . $anime_id . ' AND b.item_type_id = 1 AND b.locked = 1 AND parent_id = 0 AND b.id not in (36,447,1714,1722,1723,1858,2104) AND
					a.language_id=' . $_SESSION['language_id'] . '
				ORDER BY b.mana_cost ASC
			', TRUE);

			foreach ($items->result_array() as $item) {
				$instance	= Item::find($item['item_id'], array('cache' => true));
				$instance->set_anime($anime_id);
				$result[]	= $instance;
			}

			$this->assign('player',				$player);
			$this->assign('items',				$result);
			$this->assign('player_tutorial',	$player->player_tutorial());
		}
		function learn_grimoire(){
			$player					= Player::get_instance();
			$this->as_json			= true;
			$this->json->success	= false;
			$errors					= [];

			if(isset($_POST['id']) && is_numeric($_POST['id'])) {
				$item			= Item::find_first($_POST['id']);


				if(!$item) {
					$errors[]				= t('grimoire.errors.invalid');
				} else {
					$anime_id		= $player->character()->anime_id;
					$item->set_anime($anime_id);

					$parent_items			= Item::find("parent_id = ". $item->id ." AND item_type_id = 11");
					if(!$parent_items){
						$errors[]			= t('grimoire.errors.invalid');
					}else{
						$items_counter  = sizeof($parent_items);
						$player_item_counter = 0;

						foreach($parent_items as $parent_item){
							$player_items	= PlayerItem::find("player_id= ".$player->id." AND item_id= ". $parent_item->id);

							if($player_items){
								$player_item_counter++;
							}
						}
						if($items_counter!=$player_item_counter){
							$errors[]	= t('grimoire.errors.impossivel');
						}
					}
				}
			}

			if(!sizeof($errors)) {
				$this->json->success	= true;

				$player_item_grimoire				= new PlayerItem();
				$player_item_grimoire->item_id		= $item->id;
				$player_item_grimoire->player_id	= $player->id;
				$player_item_grimoire->removed		= 1;
				$player_item_grimoire->save();

				//Verifica a conquista de grimoire - Conquista
				$player->achievement_check("grimoire");
				$player->check_objectives("grimoire");

				$pm	= new PrivateMessage();
				$pm->to_id		= $player->id;
				$pm->subject	= "Novo Golpe Liberado";
				$pm->content 	= "O golpe ". $item->description()->name ." foi liberado";
				$pm->save();

			} else {
				$this->json->messages	= $errors;
			}
		}
		function learn() {
			$this->layout			= false;
			$this->as_json			= true;
			$this->json->success	= false;
			$player					= Player::get_instance();
			$errors					= array();

			if(isset($_POST['item']) && is_numeric($_POST['item']) && is_numeric($_POST['slot']) && $_POST['slot'] >= 0 && $_POST['slot'] < MAX_EQUIPPED_ATTACKS) {
				$item	= Item::find($_POST['item']);

				if($player->has_technique($item)) {
					$errors[]	= t('techniques.learn.learned');
				}

				if ($item->locked && !$player->has_unlocked_item($item->id)) {
					$errors[]	= t('techniques.learn.locked');
				}
			} else {
				$errors[]	= t('techniques.learn.invalid');
			}

			if(!sizeof($errors)) {
				$this->json->success		= true;

				$this->json->exp			= $player->exp;
				$this->json->max_exp		= $player->level_exp();
				$this->json->level			= $player->level;

				$this->json->mana			= $player->for_mana();
				$this->json->max_mana		= $player->for_mana(true);
				$this->json->stamina		= $player->for_stamina();
				$this->json->max_stamina	= $player->for_stamina(true);

				$player->add_technique($item, $_POST['slot']);
			} else {
				$this->json->errors		= $errors;
			}
		}
		function change_ability() {
			$this->layout	= false;
			$player			= Player::get_instance();

			if($_POST) {
				$this->as_json			= true;
				$this->render			= false;
				$this->json->success	= false;
				$errors					= array();

				if(is_numeric($_POST['id']) && is_numeric($_POST['id2'])) {
					$character_ability_old_id 	= $_POST['id'];
					$character_ability_new_id 	= $_POST['id2'];

					$character_ability_old 	= CharacterAbility::find_first("id=".$character_ability_old_id);
					$character_ability_new 	= CharacterAbility::find_first("id=".$character_ability_new_id);

					if(!$character_ability_old) {
						$errors[]	= t('upgrade.errors.1');
					}
					if(!$character_ability_new) {
						$errors[]	= t('upgrade.errors.1');
					}
					if(!sizeof($errors)) {
						//Update para marcar a nova habilidade
						$player_character_ability = PlayerCharacterAbility::find_first("character_ability_id=".$character_ability_old_id." and player_id=".$player->id);
						$player_character_ability->character_ability_new_id	= $character_ability_new->id;
						$player_character_ability->item_effect_ids 			= $character_ability_new->item_effect_ids;
						$player_character_ability->effect_chances			= $character_ability_new->effect_chances;
						$player_character_ability->effect_duration 			= $character_ability_new->effect_duration;
						$player_character_ability->consume_mana 			= $character_ability_new->consume_mana;
						$player_character_ability->cooldown 				= $character_ability_new->cooldown;
						$player_character_ability->save();

						$this->json->success  = true;
					} else {
						$this->json->errors	= $errors;
					}
				}

			}else{
				$player_ability = PlayerCharacterAbility::find_first("character_ability_id=".$_GET['id']." and player_id=".$player->id);
				$abilities = CharacterAbility::find("item_effect_ids !=370 GROUP BY item_effect_ids");

				$this->assign('player_ability', $player_ability);
				$this->assign('abilities', $abilities);
			}
		}
		function change_speciality() {
			$this->layout	= false;
			$player			= Player::get_instance();

			if($_POST) {
				$this->as_json			= true;
				$this->render			= false;
				$this->json->success	= false;
				$errors					= array();

				if(is_numeric($_POST['id']) && is_numeric($_POST['id2'])) {
					$character_speciality_old_id 	= $_POST['id'];
					$character_speciality_new_id 	= $_POST['id2'];

					$character_speciality_old 	= CharacterSpeciality::find_first("id=".$character_speciality_old_id);
					$character_speciality_new 	= CharacterSpeciality::find_first("id=".$character_speciality_new_id);


					if(!$character_speciality_old) {
						$errors[]	= t('upgrade.errors.1');
					}
					if(!$character_speciality_new) {
						$errors[]	= t('upgrade.errors.1');
					}
					if(!sizeof($errors)) {
						//Update para marcar a nova habilidade
						$player_character_speciality = PlayerCharacterSpeciality::find_first("character_speciality_id=".$character_speciality_old_id." and player_id=".$player->id);
						$player_character_speciality->character_speciality_new_id	= $character_speciality_new->id;
						$player_character_speciality->item_effect_ids 				= $character_speciality_new->item_effect_ids;
						$player_character_speciality->effect_chances				= $character_speciality_new->effect_chances;
						$player_character_speciality->effect_duration 				= $character_speciality_new->effect_duration;
						$player_character_speciality->consume_mana 					= $character_speciality_new->consume_mana;
						$player_character_speciality->cooldown 						= $character_speciality_new->cooldown;
						$player_character_speciality->save();

						$this->json->success  = true;
					} else {
						$this->json->errors	= $errors;
					}
				}

			}else{
				$player_speciality = PlayerCharacterSpeciality::find_first("character_speciality_id=".$_GET['id']." and player_id=".$player->id);
				$specialities = CharacterSpeciality::find("item_effect_ids !=370 GROUP BY item_effect_ids");

				$this->assign('player_speciality', $player_speciality);
				$this->assign('specialities', $specialities);
			}
		}
		function abilities_and_specialities() {
			$player			= Player::get_instance();
			$player_stats = PlayerStat::find_first("player_id=".$player->id);

			if(!$player_stats->view_habilidades){
				$player_stats->view_habilidades = 1;
				$player_stats->save();
			}
			$this->assign('abilities', $player->character()->abilities($player->id));
			$this->assign('specialities', $player->character()->specialities($player->id));
			$this->assign('player', $player);
			$this->assign('counter', 0);
			$this->assign('player_tutorial', $player->player_tutorial());
		}

		function learn_ability() {
			$this->_learn_ability_or_speciality(true);
		}

		function learn_speciality() {
			$this->_learn_ability_or_speciality();
		}

		private function _learn_ability_or_speciality($ability = false) {
			$this->layout			= false;
			$this->as_json			= true;
			$this->json->success	= false;
			$this->render			= 'learn_ability_or_speciality';
			$player					= Player::get_instance();
			$errors					= array();
			$checker_method			= $ability ? 'has_ability' : 'has_speciality';
			$translate_key			= $ability ? 'abilities.learn.' :  'specialities.learn.';
			$type_check				= $ability ? 3 : 4;
			$class					= $ability ? 'CharacterAbility' : 'CharacterSpeciality';

			if(isset($_POST['id']) && is_numeric($_POST['id'])) {
				$item	= $class::find($_POST['id']);

				if($item) {
					extract($item->has_requirement($player));

					if($has_requirement) {
						if($player->$checker_method($item)) {
							$errors[]	= t($translate_key . 'learned2');
						}
					} else {
						$errors[]	= t($translate_key . 'requirements');
					}
				} else {
					$errors[]	= t($translate_key . 'invalid');
				}
			} else {
				$errors[]	= t($translate_key . 'invalid');
			}

			if(!sizeof($errors)) {
				$this->json->success	= true;

				if($ability) {
					$player->character_ability_id		= $_POST['id'];
				} else {
					$player->character_speciality_id	= $_POST['id'];
				}

				$player->save();
			} else {
				$this->json->errors		= $errors;
			}
		}
	}
