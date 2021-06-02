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
            if ($player_fidelity->day != $_POST['day']) {
                $errors[]	= t('fidelity.errors.day');
            }
            if ($player_fidelity->reward) {
                $errors[]	= t('fidelity.errors.reward');
            }
            if (!sizeof($errors)) {
                // Premiando os jogadores pelo dia correspondente
                switch ($player_fidelity->day) {
					case 1:
						$player->earn(100);
						$player->save();
						break;
					case 2:
						$player->exp	+= 200;
						$player->save();
						break;
					case 3:
						$item	= Item::find_first(50);
                        $player->add_consumable($item, 2);
						break;
					case 4:
						$player_pets = Recordset::query("SELECT * FROM player_items WHERE player_id = {$player->id} AND item_id IN (SELECT id FROM items WHERE item_type_id = 3)");
						foreach ($player_pets->result_array() as $player_pet){
							Recordset::update('player_items', [
								'exp'		=> $player_pet['exp'] + 100
							], [
								'player_id'	=> $player_pet['player_id'],
								'item_id'	=> $player_pet['item_id']
							]);
						}
						break;
					case 5:
						$dropped  = Item::generate_equipment($player);
						break;
					case 6:
						// Sorteia a joia
                        $gems = Item::find("item_type_id = 15 ORDER BY RAND()");
                        foreach ($gems as $gem) {
                            if (rand(1, 100) <= $gem->drop_chance) {
                                $player_item = PlayerItem::find_first("item_id = {$gem->id} AND player_id = " . $player->id);
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
					case 7:
						$player_item = PlayerItem::find_first("player_id = {$player->id} AND item_id = 446");
						if ($player_item) {
							$player_item->quantity += 20;
							$player_item->save();
						} else {
							$player_fragment			= new PlayerItem();
							$player_fragment->item_id	= 446;
							$player_fragment->player_id	= $player->id;
							$player_fragment->quantity 	= 20;
							$player_fragment->save();
						}
						break;
					case 8:
						// Verifica se é o prêmio de crédito.
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
                            if (!$user_stats->credits || strtotime(date('Y-m-d H:i:s')) >= strtotime($user_stats->credits . "+7 days")) {
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

				$player_fidelity = PlayerFidelity::find_first("player_id=".$player->id);
                $player_fidelity->reward	= 1;
                $player_fidelity->reward_at = now(true);
                $player_fidelity->save();

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

		// $player->achievement_check('level_player');
		// $player->check_objectives('level_player');
		// $player->achievement_check('level_account');
		// $player->check_objectives('level_account');
		// $player->achievement_check('tutorial');
		// $player->check_objectives('tutorial');
		// $player->achievement_check('map');
		// $player->check_objectives('map');
		// $player->achievement_check('credits');
		// $player->check_objectives('credits');
		// $player->achievement_check('currency');
		// $player->check_objectives('currency');
		// $player->achievement_check('pets');
		// $player->check_objectives('pets');
		// $player->achievement_check('battle_npc');
		// $player->check_objectives('battle_npc');
		// $player->achievement_check('battle_pvp');
		// $player->check_objectives('battle_pvp');
		// $player->achievement_check('history_mode');
		// $player->check_objectives('history_mode');
		// $player->achievement_check('challenges');
		// $player->check_objectives('challenges');
		// $player->achievement_check('organization');
		// $player->check_objectives('organization');
		// $player->achievement_check('treasure');
		// $player->check_objectives('treasure');
		// $player->achievement_check('friends');
		// $player->check_objectives('friends');
		// $player->achievement_check('character');
		// $player->check_objectives('character');
		// $player->achievement_check('character_theme');
		// $player->check_objectives('character_theme');
		// $player->achievement_check('luck');
		// $player->check_objectives('luck');
		// $player->achievement_check('fragments');
		// $player->check_objectives('fragments');
		// $player->achievement_check('wanted');
		// $player->check_objectives('wanted');
		// $player->achievement_check('sands');
		// $player->check_objectives('sands');
		// $player->achievement_check('bloods');
		// $player->check_objectives('bloods');
		// $player->achievement_check('equipment');
		// $player->check_objectives('equipment');
		// $player->achievement_check('grimoire');
		// $player->check_objectives('grimoire');
		// $player->achievement_check('time_quests');
		// $player->check_objectives('time_quests');
		// $player->achievement_check('battle_quests');
		// $player->check_objectives('battle_quests');
		// $player->achievement_check('pvp_quests');
		// $player->check_objectives('pvp_quests');
		// $player->achievement_check('daily_quests');
		// $player->check_objectives('daily_quests');
		// $player->achievement_check('account_quests');
		// $player->check_objectives('account_quests');
		// $player->achievement_check('pet_quests');
		// $player->check_objectives('pet_quests');
		// $player->achievement_check('weekly_quests');
		// $player->check_objectives('weekly_quests');

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
                if ($reward->character_id && !$user->is_character_bought($reward->character_id)) {
                    $reward_character				= new UserCharacter();
                    $reward_character->user_id		= $player->user_id;
                    $reward_character->character_id	= $reward->character_id;
                    $reward_character->was_reward	= 1;
                    $reward_character->save();

                    // Objetivo de Round
                    $player->achievement_check("character");
					$player->check_objectives("character");
                }

				// Prêmios ( TEMA )
                if ($reward->character_theme_id && !$user->is_theme_bought($reward->character_theme_id)) {
                    $reward_theme						= new UserCharacterTheme();
                    $reward_theme->user_id				= $player->user_id;
                    $reward_theme->character_theme_id	= $reward->character_theme_id;
                    $reward_theme->was_reward			= 1;
                    $reward_theme->save();

                    // Objetivo de Round
                    $player->achievement_check("character_theme");
					$player->check_objectives("character_theme");
                }

				// Prêmios ( TITULOS )
                if ($reward->headline_id && !$user->is_headline_bought($reward->headline_id)) {
                    $reward_headline				= new UserHeadline();
                    $reward_headline->user_id		= $player->user_id;
                    $reward_headline->headline_id	= $reward->headline_id;
                    $reward_headline->save();
                }

				// Prêmios ( ITENS )
				if ($reward->item_id) {
					if ($reward->item_id != 1709) {
                        $item		= Item::find_first($reward->item_id);
						if ($item->item_type_id == 3 && !$player->has_item($item->id)) {
							$player_pet				= new PlayerItem();
							$player_pet->item_id	= $item->id;
							$player_pet->player_id	= $player->id;
							$player_pet->save();
						} else {
                        	$player->add_consumable($item, $reward->quantity);
						}
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
