<?php
class EventsController extends Controller {
    function tutorial() {
        $player		 = Player::get_instance();

        // Checa a conquista
        $player->tutorial();

        $this->assign('player',				$player);
        $this->assign('player_stats',		PlayerStat::find_first("player_id=".$player->id));
        $this->assign('player_tutorial',	$player->player_tutorial());
    }
    function fidelity(){
        $player			 = Player::get_instance();
        $player_fidelity = PlayerFidelity::find_first("player_id=".$player->id);
        $user_stats 	 = UserStat::find_first("user_id=".$player->user_id);

        if (!$user_stats) {
            $user_stats 		  = new UserStat();
            $user_stats->user_id  = $player->user_id;
            $user_stats->save(); 
        }
        
        $this->assign('user_stats',         $user_stats);
        $this->assign('player',             $player);
        $this->assign('player_fidelity',    $player_fidelity);
        $this->assign('player_tutorial',    $player->player_tutorial());
    }
    function reward_fidelity() {
        $this->as_json			= true;
        $this->json->success	= false;

        $player		            = Player::get_instance();
        $user		            = User::get_instance();
        $errors					= [];
        
        if (!isset($_POST['day']) || (isset($_POST['day']) && !is_numeric($_POST['day']))) {
            $errors[]	= t('fidelity.errors.day');
        } else {
            $player_fidelity = PlayerFidelity::find_first("player_id=".$player->id);
                
            if ($player_fidelity->day != $_POST['day']){
                $errors[]	= t('fidelity.errors.day');
            }
            if ($player_fidelity->reward){
                $errors[]	= t('fidelity.errors.reward');
            }
            if (!sizeof($errors)) {
                $player_fidelity = PlayerFidelity::find_first("player_id=".$player->id);
                $player_fidelity->reward = 1;
                $player_fidelity->reward_at = now(true);
                $player_fidelity->save();
            
                // Premiando os jogadores pelo dia correspondente
                switch ($player_fidelity->day) {
                    
                    // Recompensa em Dinheiro
                    case 1:
                        $player->earn(100);
                        $player->save();
                    break;	
                    case 5:
                        $player->earn(200);
                        $player->save();
                    break;	
                    case 10:
                        $player->earn(400);
                        $player->save();
                    break;	
                    case 15:
                        $player->earn(800);
                        $player->save();
                    break;	
                    case 20:
                        $player->earn(1600);
                        $player->save();
                    break;
                    case 25:
                        $player->earn(3200);
                        $player->save();
                    break;
                    // Recompensa em Dinheiro
                    
                    // Recompensa em Experiencia
                    case 2:
                        $player->exp	+= 200;
                        $player->save();
                    break;
                    case 6:
                        $player->exp	+= 400;
                        $player->save();
                    break;
                    case 11:
                        $player->exp	+= 800;
                        $player->save();
                    break;
                    case 16:
                        $player->exp	+= 1600;
                        $player->save();
                    break;
                    case 21:
                        $player->exp	+= 3200;
                        $player->save();
                    break;
                    case 26:
                        $player->exp	+= 6400;
                        $player->save();
                    break;
                    // Recompensa em Experiencia
                    
                    // Recompensa em Comida
                    case 3:
                        $item	= Item::find_first(46);
                        $player->add_consumable($item, 2);
                    break;
                    case 7:
                        $item	= Item::find_first(48);
                        $player->add_consumable($item, 2);
                    break;
                    case 12:
                        $item	= Item::find_first(50);
                        $player->add_consumable($item, 2);
                    break;
                    case 17:
                        $item	= Item::find_first(52);
                        $player->add_consumable($item, 2);
                    break;
                    case 22:
                        $item	= Item::find_first(54);
                        $player->add_consumable($item, 2);
                    break;
                    case 27:
                        $item	= Item::find_first(56);
                        $player->add_consumable($item, 2);
                    break;
                    // Recompensa em Comida
                    
                    // Recompensa em joia
                    case 4:
                    case 13:
                    case 23:
                        //Sorteia a joia
                        $gems = Item::find("item_type_id=15 ORDER BY RAND()");
                        foreach ($gems as $gem) {
                            if (rand(1, 100) <= $gem->drop_chance) {
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
                    break;

                    // Recompensa em Mascote
                    case 8:
                    case 18:
                        $pet = Item::find_first('item_type_id=3 AND is_initial=1', ['reorder' => 'RAND()']);
                        if (!$player->has_item($pet->id)) {
                            $player_pet				= new PlayerItem();
                            $player_pet->item_id	= $pet->id;
                            $player_pet->player_id	= $player->id;
                            $player_pet->save();
                            
                            //Verifica se você tem pets - Conquista
                            $player->achievement_check("pets");
                            
                            // Objetivo de Round
                            $player->check_objectives("pets");
                        }
                    break;
                    // Recompensa em Equipamento
                    case 9:
                        $dropped	= Item::generate_equipment($player, 1);
                    break;
                    case 29:
                        $dropped	= Item::generate_equipment($player, 2);
                    break;
                    // Recompensa em Equipamento
                    
                    // Areia Estelar
                    case 19:
                        $item_1719 = PlayerItem::find_first("player_id =". $player->id. " AND item_id=1719");
                        if($item_1719) {
                            $player_areia			= $player->get_item(1719);	
                            $player_areia->quantity += 1;
                            $player_areia->save();
                        } else {
                            $player_areia	= new PlayerItem();						
                            $player_areia->item_id		= 1719;
                            $player_areia->player_id	= $player->id;
                            $player_areia->quantity		= 1;
                            $player_areia->save();
                        }

                        //Verifica a conquista de areia - Conquista
                        $player->achievement_check("sands");

                        // Objetivo de Round
                        $player->check_objectives("sands");
                    break;
                    
                    // Areia Estelar
                    // Sangue de Deus
                    case 24:
                        $item_1720 = PlayerItem::find_first("player_id =". $player->id. " AND item_id=1720");
                        if ($item_1720) {
                            $player_sangue			 = $player->get_item(1720);	
                            $player_sangue->quantity += 1;
                            $player_sangue->save();
                        } else {
                            $player_sangue	= new PlayerItem();						
                            $player_sangue->item_id		= 1720;
                            $player_sangue->player_id	= $player->id;
                            $player_sangue->quantity	= 1;
                            $player_sangue->save();
                        }

                        //Verifica a conquista de areia - Conquista
                        $player->achievement_check("bloods");

                        // Objetivo de Round
                        $player->check_objectives("bloods");
                    break;
                    
                    // Sangue de Deus
                    
                    // Recompensa Chakra Azul
                    case 14:
                        $item_1852 = PlayerItem::find_first("player_id =". $player->id. " AND item_id=1852");
                            if ($item_1852) {
                                $player_chakra				= $player->get_item(1852);	
                                $player_chakra->quantity 	+= 1;
                                $player_chakra->save();
                            } else {
                                $player_chakra				= new PlayerItem();						
                                $player_chakra->item_id		= 1852;
                                $player_chakra->player_id	= $player->id;
                                $player_chakra->quantity 	= 1;
                                $player_chakra->save();
                            }
                        
                    break;
                    // Recompensa Chakra Roxo
                    case 28:
                        $item_1853 = PlayerItem::find_first("player_id =". $player->id. " AND item_id=1853");
                            if ($item_1853) {
                                $player_chakra				= $player->get_item(1853);	
                                $player_chakra->quantity 	+= 1;
                                $player_chakra->save();
                            } else {
                                $player_chakra				= new PlayerItem();						
                                $player_chakra->item_id		= 1853;
                                $player_chakra->player_id	= $player->id;
                                $player_chakra->quantity 	= 1;
                                $player_chakra->save();
                            }
                        
                    break;
                    case 30:
                        //Verifica se é o prêmio de crédito.
                        $user_stats = UserStat::find_first("user_id=".$user->id);
                        if (!$user_stats) {
                            $user->earn(1);
                            $user->save();

                            // Adiciona a data de hoje na conta do cara falando que ele já ganhou os créditos.
                            $user_stats_new 		  = new UserStat();
                            $user_stats_new->credits  = now(true);
                            $user_stats_new->user_id  = $user->id;
                            $user_stats_new->save(); 
                        } else {
                            if (!$user_stats->credits || strtotime(date('Y-m-d H:i:s')) >= strtotime($user_stats->credits . "+29 days")) {
                                $user->earn(1);
                                $user->save();
                                $user_stats->credits = now(true);
                                $user_stats->save();
                            }
                        }
                    break;
                }

                // Missões Diarias
                $player_quests_daily   = $player->daily_quests();
                if ($player_quests_daily) {
                    foreach ($player_quests_daily as $player_quest_daily) {
                        switch ($player_quest_daily->type) {
                            case "fidelity":
                                $player_quest_daily->total++;
                                break;
                        }
                        $player_quest_daily->save();
                    }
                }

                $this->json->success	= true;
            } else {
                $this->json->messages	= $errors;
            }
        }	
    }
    function anime() {
        $player			= Player::get_instance();
        $activeEvent	= EventAnime::find_first("completed = 0");
        $lastWinner		= EventAnime::find_first("completed = 1 and anime_win_id != 0", [
			'reorder'	=> 'id DESC',
			'limit'		=> 1
		]);
        $animes			= Anime::find('playable = 1', [
        	'reorder' => 'score desc, id asc'
		]);

		$this->assign('player',				$player);
		$this->assign('animes',				$animes);
        $this->assign('lastWinner',			$lastWinner);
        $this->assign('activeEvent',		$activeEvent);
        $this->assign('player_tutorial',	$player->player_tutorial());
    }
    function wanted() {
        $player		= Player::get_instance();
        $page		= isset($_POST['page']) && is_numeric($_POST['page']) ? $_POST['page'] : 0;
        $limit		= 4;
        $wanteds	= [];

        $result	= PlayerWanted::filter($page, $limit);
        if ($result['players']) {
            foreach ($result['players'] as $player_wanted) {
                $p = Player::find_first($player_wanted->player_id);
                if (!$p->banned) {
                    $wanteds[] = $p;
                }
            }
        }
        $this->assign('player',				$player);
        $this->assign('wanteds',			$wanteds);
        $this->assign('pages',				$result['pages']);
        $this->assign('page',				$page);
        $this->assign('player_tutorial',	$player->player_tutorial());
    }
    function objectives() {
        $user	= User::get_instance();
        $player	= Player::get_instance();

        $this->assign('player',				$player);
        $this->assign('user',				$user);
        $this->assign('objectives',			UserObjective::find("user_id=". $user->id));
        $this->assign('rewards',			LuckReward::find("type=3"));
        $this->assign('item_type_ids',		Recordset::query('SELECT DISTINCT(`item_type_id`) FROM `luck_rewards` WHERE `type` = 3'));
        $this->assign('player_tutorial',	$player->player_tutorial());
    }
    function objective_reward() {
        $player					= Player::get_instance();
        $user					= User::get_instance();
        $attributes				= $player->attributes();
        $this->as_json			= TRUE;
        $this->json->success	= FALSE;
        $errors					= [];


        if (!isset($_POST['id']) || (isset($_POST['id']) && !is_numeric($_POST['id'])))
            $errors[]	= t('fragments.error1');
        else {
            $reward = LuckReward::find_first("id=".$_POST['id']);
            if ($user->round_points < $reward->chance)
                $errors[]	= t('fragments.error2');
            if ($reward->luck_reward_log($reward->id, $player->id))
                $errors[]	= t('objectives.ja_trocou');

            if (!sizeof($errors)) {
                // Desconta o valor necessário do jogador
                $user->round_points -= $reward->chance;
                $user->save();

				// Aplica a premiação para o jogador atual
				// Prêmios ( PTS ENCANTAMENTO )
                if($reward->enchant_points){
                    $player->enchant_points_total += $reward->quantity;
				}

				// Prêmios ( DINHEIRO )
                if ($reward->currency) {
                    $player->earn($reward->currency);
                }

				// Prêmios ( EXP )
				if ($reward->exp) {
                    $player->earn_exp($reward->exp);
                }

				// Prêmios ( CRÉDITOS )
				if ($reward->credits) {
                    $user->earn($reward->credits);

                    // Verifica os créditos do jogador.
                    $player->achievement_check("credits");
                    // Objetivo de Round
                    $player->check_objectives("credits");
				}

				// Prêmios ( EXP CONTA )
                if ($reward->exp_account) {
                    $user->exp($reward->exp_account);
                }

				// Prêmios ( EQUIPAMENTO )
                if ($reward->equipment) {
                    $i = 1;
                    if ($reward->equipment == 1) {
                        $dropped  = Item::generate_equipment($player);
                    } elseif ($reward->equipment == 2) {
                        while ($i <= $reward->quantity) {
                            $dropped  = Item::generate_equipment($player, 0);
                            $i++;
                        }
                    } elseif ($reward->equipment == 3) {
                        while ($i <= $reward->quantity) {
                            $dropped  = Item::generate_equipment($player, 1);
                            $i++;
                        }
                    } elseif ($reward->equipment == 4) {
                        while ($i <= $reward->quantity) {
                            $dropped  = Item::generate_equipment($player, 2);
                            $i++;
                        }
                    }
                }

				// Prêmios ( PERSONAGEM )
                if ($reward->character_id) {
                    $reward_character				= new UserCharacter();
                    $reward_character->user_id		= $player->user_id;
                    $reward_character->character_id	= $reward->character_id;
                    $reward_character->was_reward	= 1;
                    $reward_character->save();

                    // Objetivo de Round
                    $player->achievement_check("character");
                }

				// Prêmios ( TEMA )
                if ($reward->character_theme_id) {
                    $reward_theme						= new UserCharacterTheme();
                    $reward_theme->user_id				= $player->user_id;
                    $reward_theme->character_theme_id	= $reward->character_theme_id;
                    $reward_theme->was_reward			= 1;
                    $reward_theme->save();

                    // Objetivo de Round
                    $player->achievement_check("character_theme");
                }

				// Prêmios ( TITULOS )
                if ($reward->headline_id) {
                    $reward_headline				= new UserHeadline();
                    $reward_headline->user_id		= $player->user_id;
                    $reward_headline->headline_id	= $reward->headline_id;
                    $reward_headline->save();
                }
				// Prêmios ( ITENS )
				if ($reward->item_id) {
                    if ($reward->item_id != "1709") {
                        $item		= Item::find_first($reward->item_id);
                        $player->add_consumable($item, $reward->quantity);
                    } else {
                        $user->character_slots++;
                        $user->save();
                    }
                }

				$atts	= [
					'for_atk','for_def','for_crit','for_abs',
					'for_prec','for_init','for_inc_crit','for_inc_abs'
				];

                foreach ($atts as $attribute) {
                    if ($reward->{$attribute})
                        $attributes->{$attribute}	+= $reward->{$attribute};
                }

                // Adiciona o Log
                $log					= new PlayerLuckLog();
                $log->player_id			= $player->id;
                $log->luck_reward_id	= $reward->id;
                $log->type				= 3;
                $log->save();

                $player->save();
                $attributes->save();

                $this->json->success	= TRUE;
            } else
                $this->json->messages	= $errors;
        }
    }
}