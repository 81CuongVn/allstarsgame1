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
    function fidelity() {
        $player				= Player::get_instance();
        $playerFidelity		= PlayerFidelity::find_first("player_id=" . $player->id);
		$fidelities			= Fidelity::find('1=1');
        $userStats			= UserStat::find_first("user_id=" . $player->user_id);
        if (!$userStats) {
            $userStats			= new UserStat();
            $userStats->user_id	= $player->user_id;
            $userStats->save();
        }
		
        $this->assign('player',				$player);
		$this->assign('fidelities',			$fidelities);
		$this->assign('userStats',			$userStats);
        $this->assign('playerFidelity',		$playerFidelity);
        $this->assign('player_tutorial',	$player->player_tutorial());
    }
    function reward_fidelity() {
        $player					= Player::get_instance();
        $user					= User::get_instance();
		$this->as_json			= TRUE;
        $this->json->success	= FALSE;
        $errors					= [];

        if (!isset($_POST['day']) || (isset($_POST['day']) && !is_numeric($_POST['day'])))
			$errors[]	= t('fidelity.errors.day');
		elseif (!($fidelity = Fidelity::find_first('day = '. $_POST['day'])))
			$errors[]	= t('fidelity.errors.day');
        else {
            $playerFidelity = PlayerFidelity::find_first("player_id = ".$player->id);
            if ($playerFidelity->day != $_POST['day'])
                $errors[]	= t('fidelity.errors.day');
            if ($playerFidelity->reward)
                $errors[]	= t('fidelity.errors.reward');
            if (!sizeof($errors)) {
                $playerFidelity = PlayerFidelity::find_first("player_id = ".$player->id);
				$playerFidelity->reward = 1;
                $playerFidelity->reward_at = now(TRUE);
				$playerFidelity->save();
				
				switch ($fidelity->type) {
					case 'currency':
						$player->earn($fidelity->reward);
                        $player->save();
					break;
					case 'stars':
						// Verifica se é o prêmio de estrela.
                        $userStats = UserStat::find_first("user_id = {$user->id}");
                        if (!$userStats) {
                            $user->earn($fidelity->reward);
                            $user->save();

                            // Adiciona a data de hoje na conta do cara falando que ele já ganhou as estrelas.
                            $addUserStats			= new UserStat();
                            $addUserStats->stars	= now(TRUE);
                            $addUserStats->user_id	= $user->id;
                            $addUserStats->save();
                        } else {
                            if (!$userStats->stars || time() >= strtotime($userStats->stars . ' +7 days')){
                                $user->earn($fidelity->reward);
                                $user->save();

								$userStats->stars = now(TRUE);
                                $userStats->save();
                            }
                        }
					break;
					case 'experience':
						$player->exp += $fidelity->reward;
						$player->save();
					break;
					case 'pet':
						$pet = Item::find_first('item_type_id = 3 and is_initial = 1', [
							'reorder' => 'RAND()'
						]);
                        if (!$player->has_item($pet->id)) {
                            $player_pet						= new PlayerItem();
                            $player_pet->item_id			= $pet->id;
                            $player_pet->player_id			= $player->id;
                            $player_pet->save();

                            //Verifica se você tem pets - Conquista
                            $player->achievement_check("pets");
                            // Objetivo de Round
                            $player->check_objectives("pets");
                        }
					break;
					case 'sand':
						$item_1719 = PlayerItem::find_first("player_id = {$player->id} and item_id = 1719");
                        if ($item_1719) {
                            $playerSand				= $player->get_item(1719);
                            $playerSand->quantity	+= $fidelity->reward;
                            $playerSand->save();
                        } else {
                            $playerSand	= new PlayerItem();
                            $playerSand->item_id	= 1719;
                            $playerSand->player_id	= $player->id;
                            $playerSand->quantity	= $fidelity->reward;
                            $playerSand->save();
                        }

						//Verifica a conquista de areia - Conquista
                        $player->achievement_check("sands");
                        // Objetivo de Round
                        $player->check_objectives("sands");
					break;
					case 'blood':
						$item_1720 = PlayerItem::find_first("player_id = {$player->id} and item_id = 1720");
                        if ($item_1720) {
                            $playerBlood			 = $player->get_item(1720);
                            $playerBlood->quantity	+= $fidelity->reward;
                            $playerBlood->save();
                        } else {
                            $playerBlood			= new PlayerItem();
                            $playerBlood->item_id	= 1720;
                            $playerBlood->player_id	= $player->id;
                            $playerBlood->quantity	= $fidelity->reward;
                            $playerBlood->save();
						}

                        //Verifica a conquista de areia - Conquista
                        $player->achievement_check("bloods");
                        // Objetivo de Round
                        $player->check_objectives("bloods");
					break;
					case 'jewel':
						$gems = Item::find("item_type_id = 15", [
							'reorder'	=> 'RAND()'
						]);
                        foreach ($gems as $gem) {
                            $rand 	= rand(1, 100);
                            if ($rand <= $gem->drop_chance) {
                                $playerItem = PlayerItem::find_first("item_id = {$gem->id} and player_id = {$player->id}");
                                if ($playerItem) {
                                    $playerItem->quantity	+= $fidelity->reward;
                                    $playerItem->save();
                                } else {
                                    $playerItem = new PlayerItem();
                                    $playerItem->player_id	= $player->id;
                                    $playerItem->item_id	= $gem->id;
                                    $playerItem->rarity		= $gem->rarity;
                                    $playerItem->quantity	+= $fidelity->reward;
                                    $playerItem->save();
                                }
                                break;
                            }
                        }
					break;
					case 'souls':
						$item_446 = PlayerItem::find_first("player_id  = {$player->id} AND item_id = 446");
						if ($item_446) {
							$item_446->quantity		+= $fidelity->reward;
							$item_446->save();
						} else {
							$playerSouls			= new PlayerItem();
							$playerSouls->item_id	= 446;
							$playerSouls->player_id	= $player->id;
							$playerSouls->quantity 	= $fidelity->reward;
							$playerSouls->save();
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

                $this->json->success	= TRUE;
            } else
                $this->json->messages	= $errors;
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
        $wanteds	= array();

        $result	= PlayerWanted::filter($page, $limit);
        if ($result['players']) {
            foreach ($result['players'] as $player_wanted) {
                $wanteds[] = Player::find_first($player_wanted->player_id);
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