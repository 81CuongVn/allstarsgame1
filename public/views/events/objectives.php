<?=partial('shared/title', [
    'title' => 'objectives.title',
    'place' => 'objectives.title'
]);?>
<?php if (!$player_tutorial->objectives) { ?>
	<script type="text/javascript">
		$(function() {
			var tour = new Tour({
				backdrop: true,
				page: 24,
                steps: [{
                    element: "#tutorial-first",
                    title: "Metas do Jogo",
                    content: "Todo novo Round você irá receber 10 Objetivos, que deverão ser seu foco principal durante o Round. Complete um objetivo para receber 1 Ponto de Round e gastar com prêmios incríveis!",
                    placement: "top"
                }, {
                    element: "#luck-list-content",
                    title: "Prêmios Incríveis!",
                    content: "Use seus Pontos de Round para comprar All-Stars, itens, comidas e até títulos! Mas atenção, você só poderá comprar uma vez do mesmo item por Round!",
                    placement: "top"
                }]
            });

            tour.restart();
            tour.init(true);
            tour.start(true);
        });
    </script>
<?php }
$daysLeft = ceil((strtotime(ROUND_END) - now()) / 86400);
?>
<div id="tutorial-first">
	<?=partial('shared/info', [
		'id'		=> 1,
		'title'		=> 'objectives.mensagens.title1',
		'message'	=> t('objectives.mensagens.description1', ['days' => $daysLeft])
	]);?>
</div><br />
<div class="tutorial-objectives">
    <?php foreach($objectives as $objective) { ?>
        <div class="tutorial" style="background-color:<?=($objective->complete ? '#092e4d':'#04192a');?>;">
            <div class="icon"><img src="<?=image_url('icons/' . ($objective->complete ? 'accept' : 'cancel') . '.png');?>" /></div>
            <div class="description">
                <b class="amarelo" style="font-size:14px;">
                    <?=$objective->description()->name;?>
                </b><br />
                <?=$objective->description()->description;?><br />
            </div>
            <div class="bar">
                <?php
                // Barrinhas de Progresso dos Fragmentos
                if ($objective->achievement()->fragments == 1) {
                    $player_fragments = PlayerItem::find_first("player_id=". $player->id." AND item_id=446");
                    if ($player_fragments) {
                        if (($player_fragments->quantity < $objective->achievement()->quantity) && !$objective->complete) {
                            echo exp_bar($player_fragments->quantity, $objective->achievement()->quantity, 175, $player_fragments->quantity . '/' . $objective->achievement()->quantity);
                        } else {
                            echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                        }
                    } else {
                        echo exp_bar(0, $objective->achievement()->quantity, 175, '0/' . $objective->achievement()->quantity);
                    }
                }
                if ($objective->achievement()->fragments == 2) {
                    $player_change = PlayerStat::find_first("player_id=".$player->id);
                    if (($player_change->fragments < $objective->achievement()->quantity) && !$objective->complete) {
                        echo exp_bar($player_change->fragments, $objective->achievement()->quantity, 175, $player_change->fragments . '/' . $objective->achievement()->quantity);
                    } else {
                        echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                    }
                }
                // Barrinhas de Progresso dos Fragmentos

                // Barrinhas do Mapa
                if ($objective->achievement()->map == 1 && $objective->achievement()->anime_id) {
                    $player_map_anime = PlayerMapLog::find("player_id=". $player->id." AND anime_id=".$objective->achievement()->anime_id);
                    if ((sizeof($player_map_anime) < $objective->achievement()->quantity) && !$objective->complete) {
                        echo exp_bar(sizeof($player_map_anime), $objective->achievement()->quantity, 175, sizeof($player_map_anime) . '/' . $objective->achievement()->quantity);
                    } else {
                        echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                    }
                }
                if ($objective->achievement()->map == 2 && $objective->achievement()->anime_id) {
                    $player_map_anime = Recordset::query("select sum(quantity) as total from player_map_logs WHERE anime_id=".$objective->achievement()->anime_id." and player_id=".$player->id)->result_array();
                    if (($player_map_anime[0]['total'] < $objective->achievement()->quantity) && !$objective->complete) {
                        echo exp_bar(($player_map_anime[0]['total'] ? $player_map_anime[0]['total'] : 0), $objective->achievement()->quantity, 175, ($player_map_anime[0]['total'] ? $player_map_anime[0]['total'] : 0) . '/' . $objective->achievement()->quantity);
                    } else {
                        echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                    }
                }
                if ($objective->achievement()->map == 2 && !$objective->achievement()->anime_id) {
                    $player_map_anime = Recordset::query("select sum(quantity) as total from player_map_logs WHERE player_id=".$player->id)->result_array();
                    if (($player_map_anime[0]['total'] < $objective->achievement()->quantity) && !$objective->complete) {
                        echo exp_bar(($player_map_anime[0]['total'] ? $player_map_anime[0]['total'] : 0), $objective->achievement()->quantity, 175, ($player_map_anime[0]['total'] ? $player_map_anime[0]['total'] : 0) . '/' . $objective->achievement()->quantity);
                    } else {
                        echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                    }
                }
                // Barrinhas do Mapa

                // Barrinhas de Progresso dos Amigos
                if ($objective->achievement()->friends > 1) {
                    $player_friends = Recordset::query("select count(id) as total from player_friend_lists WHERE  player_id=".$player->id)->result_array();

                    if (($player_friends[0]['total'] < $objective->achievement()->friends) && !$objective->complete) {
                        echo exp_bar($player_friends[0]['total'], $objective->achievement()->friends, 175, $player_friends[0]['total'] . '/' . $objective->achievement()->friends);
                    } else {
                        echo exp_bar($objective->achievement()->friends, $objective->achievement()->friends, 175, $objective->achievement()->friends . '/' . $objective->achievement()->friends);
                    }
                } elseif ($objective->achievement()->friends == 1 && $objective->achievement()->friends_send_gifts) {
                    $player_send_gifts = Recordset::query("select count(id) as total from player_gift_logs WHERE  player_id=".$player->id)->result_array();
                   if (($player_send_gifts[0]['total'] < $objective->achievement()->friends_send_gifts) && !$objective->complete) {
                        echo exp_bar($player_send_gifts[0]['total'], $objective->achievement()->friends_send_gifts, 175, $player_send_gifts[0]['total'] . '/' . $objective->achievement()->friends_send_gifts);
                    } else {
                        echo exp_bar($objective->achievement()->friends_send_gifts, $objective->achievement()->friends, 175, $objective->achievement()->friends_send_gifts . '/' . $objective->achievement()->friends_send_gifts);
                    }
                } elseif ($objective->achievement()->friends == 1 && $objective->achievement()->friends_received_gifts) {
                    $friends_received_gifts = Recordset::query("select count(id) as total from player_gift_logs WHERE  friend_id=".$player->id)->result_array();
                    if (($friends_received_gifts[0]['total'] < $objective->achievement()->friends_received_gifts) && !$objective->complete) {
                        echo exp_bar($friends_received_gifts[0]['total'], $objective->achievement()->friends_received_gifts, 175, $friends_received_gifts[0]['total'] . '/' . $objective->achievement()->friends_received_gifts);
                    } else {
                        echo exp_bar($objective->achievement()->friends_received_gifts, $objective->achievement()->friends, 175, $objective->achievement()->friends_received_gifts . '/' . $objective->achievement()->friends_received_gifts);
                    }
                }
                // Barrinhas de Progresso dos Amigos

                // Barrinhas de Progresso do Level
                if ($objective->achievement()->level_player > 1) {
                    if (($player->level < $objective->achievement()->level_player) && !$objective->complete) {
                        echo exp_bar($player->level, $objective->achievement()->level_player, 175, $player->level . '/' . $objective->achievement()->level_player);
                    } else {
                        echo exp_bar($objective->achievement()->level_player, $objective->achievement()->level_player, 175, $objective->achievement()->level_player . '/' . $objective->achievement()->level_player);
                    }
                }
                // Barrinhas de Progresso do Level

                // Barrinhas de Progresso dos Tesouros
                if ($objective->achievement()->treasure) {
                    if (($player->treasure_total < $objective->achievement()->quantity) && !$objective->complete ) {
                        echo exp_bar($player->treasure_total, $objective->achievement()->quantity, 175, $player->treasure_total . '/' . $objective->achievement()->quantity);
                    } else {
                        echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                    }
                }
                // Barrinhas de Progresso dos Tesouros

                // Barrinhas de Progresso do Desafio do Ceu
                if ($objective->achievement()->challenges > 0) {
                    $player_challenge = PlayerChallenge::find_first("challenge_id=".$objective->achievement()->challenges." AND player_id=".$player->id ." ORDER BY quantity desc");
                    if ($player_challenge) {
                        if (($player_challenge->quantity < $objective->achievement()->challenges_floor) && !$objective->complete) {
                            echo exp_bar($player_challenge->quantity, $objective->achievement()->challenges_floor, 175, $player_challenge->quantity . '/' . $objective->achievement()->challenges_floor);
                        } else {
                            echo exp_bar(1, 1, 175, '1/1');
                        }
                    } else {
                        echo exp_bar(0, $objective->achievement()->challenges_floor, 175, '0/' . $objective->achievement()->challenges_floor);
                    }
                }
                // Barrinhas de Progresso do Desafio do Ceu

                // Barrinhas de Progresso dos wanted
                if ($objective->achievement()->wanted == 1) {
                    $player_wanted = Recordset::query("select count(id) as total from player_wanteds WHERE enemy_id=".$player->id)->result_array();
                    if (($player_wanted[0]['total'] < $objective->achievement()->quantity) && !$objective->complete) {
                        echo exp_bar($player_wanted[0]['total'], $objective->achievement()->quantity, 175, $player_wanted[0]['total'] . '/' . $objective->achievement()->quantity);
                    } else {
                        echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                    }
                }
                if ($objective->achievement()->wanted == 2) {
                    if (($player->won_last_battle < $objective->achievement()->quantity) && !$objective->complete) {
                        echo exp_bar($player->won_last_battle, $objective->achievement()->quantity, 175, $player->won_last_battle . '/' . $objective->achievement()->quantity);
                    } else {
                        echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                    }
                }
                // Barrinhas de Progresso dos wanted

                // Barrinhas de Progresso do Modo Aventura
                if ($objective->achievement()->history_mode > 1) {
                    $user_history_mode_subgroup = UserHistoryModeSubgroup::find_first("history_mode_subgroup_id=".$objective->achievement()->history_mode." AND user_id=".$player->user_id." AND complete=1");
                   if (!$user_history_mode_subgroup && !$objective->complete) {
                        echo exp_bar(0, 1, 175, '0/1');
                    } else {
                        echo exp_bar(1, 1, 175, '1/1');
                    }
                }
                // Barrinhas de Progresso do Modo Aventura

                // Barrinhas de Progresso do Level da Conta
                if ($objective->achievement()->level_account > 1) {
                    if (($user->level < $objective->achievement()->level_account) && !$objective->complete) {
                        echo exp_bar($user->level, $objective->achievement()->level_account, 175, $user->level . '/' . $objective->achievement()->level_account);
                    } else {
                        echo exp_bar($objective->achievement()->level_account, $objective->achievement()->level_account, 175, $objective->achievement()->level_account . '/' . $objective->achievement()->level_account);
                    }
                }
                // Barrinhas de Progresso dos Amigos

                // Barrinhas de Progresso dos Equipamentos
                if ($objective->achievement()->equipment == 1 && $objective->achievement()->rarity) {
                    $player_equipments = Recordset::query("select count(id) as total from player_items WHERE player_id=".$player->id." AND item_id in (select id from items WHERE item_type_id=8) AND rarity='".$objective->achievement()->rarity."'")->result_array();
                    if (($player_equipments[0]['total'] < $objective->achievement()->quantity) && !$objective->complete) {
                        echo exp_bar($player_equipments[0]['total'], $objective->achievement()->quantity, 175, $player_equipments[0]['total'] . '/' . $objective->achievement()->quantity);
                    } else {
                        echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                    }
                }
                if ($objective->achievement()->equipment == 1 && !$objective->achievement()->rarity) {
                    $player_equipments = Recordset::query("select count(id) as total from player_items WHERE player_id=".$player->id." AND item_id in (select id from items WHERE item_type_id=8)")->result_array();
                    if (($player_equipments[0]['total'] < $objective->achievement()->quantity) && !$objective->complete) {
                        echo exp_bar($player_equipments[0]['total'], $objective->achievement()->quantity, 175, $player_equipments[0]['total'] . '/' . $objective->achievement()->quantity);
                    } else {
                        echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                    }
                }
                if ($objective->achievement()->equipment == 2) {
                    $player_equipments = Recordset::query("select count(id) as total from player_items WHERE player_id=".$player->id." AND item_id in (select id from items WHERE item_type_id=8) AND rarity='".$objective->achievement()->rarity."' AND equipped=1")->result_array();
                    if (($player_equipments[0]['total'] < $objective->achievement()->quantity) && !$objective->complete) {
                        echo exp_bar($player_equipments[0]['total'], $objective->achievement()->quantity, 175, $player_equipments[0]['total'] . '/' . $objective->achievement()->quantity);
                    } else {
                        echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                    }
                }
                // Barrinhas de Progresso dos Equipamentos

                // Barrinhas de Progresso do Dinheiro
                if ($objective->achievement()->currency) {
                    if (($player->currency < $objective->achievement()->quantity) && !$objective->complete) {
                        echo exp_bar($player->currency, $objective->achievement()->quantity, 175, $player->currency . '/' . $objective->achievement()->quantity);
                    } else {
                        echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                    }
                }
                // Barrinhas de Progresso do Dinheiro

                // Barrinhas de Progresso do crédito
                if ($objective->achievement()->credits) {
                    if (($user->credits < $objective->achievement()->quantity) && !$objective->complete) {
                        echo exp_bar($user->credits, $objective->achievement()->quantity, 175, $user->credits . '/' . $objective->achievement()->quantity);
                    } else {
                        echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                    }
                }
                // Barrinhas de Progresso do crédito

                // Barrinhas de Progresso dos Sangues
                if ($objective->achievement()->bloods == 1) {
                    $player_fragments = PlayerItem::find_first("player_id=". $player->id." AND item_id=1720");
                    if ($player_fragments) {
                        if (($player_fragments->quantity < $objective->achievement()->quantity) && !$objective->complete) {
                            echo exp_bar($player_fragments->quantity, $objective->achievement()->quantity, 175, $player_fragments->quantity . '/' . $objective->achievement()->quantity);
                        } else {
                            echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                        }
                    } else {
                        echo exp_bar(0, $objective->achievement()->quantity, 175, '0/' . $objective->achievement()->quantity);
                    }
                }
                if ($objective->achievement()->bloods == 2) {
                    $player_change = PlayerStat::find_first("player_id=".$player->id);
                    if (($player_change->bloods < $objective->achievement()->quantity) && !$objective->complete) {
                        echo exp_bar($player_change->bloods, $objective->achievement()->quantity, 175, $player_change->bloods . '/' . $objective->achievement()->quantity);
                    } else {
                        echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                    }
                }
                // Barrinhas de Progresso dos Sangues

                // Barrinhas de Progresso dos Areia
                if ($objective->achievement()->sands == 1) {
                    $player_fragments = PlayerItem::find_first("player_id=". $player->id." AND item_id=1719");
                    if ($player_fragments) {
                        if (($player_fragments->quantity < $objective->achievement()->quantity) && !$objective->complete) {
                            echo exp_bar($player_fragments->quantity, $objective->achievement()->quantity, 175, $player_fragments->quantity . '/' . $objective->achievement()->quantity);
                        } else {
                            echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                        }
                    } else {
                        echo exp_bar(0, $objective->achievement()->quantity, 175, '0/' . $objective->achievement()->quantity);
                    }
                }
                if ($objective->achievement()->sands == 2) {
                    $player_change = PlayerStat::find_first("player_id=".$player->id);
                    if (($player_change->sands < $objective->achievement()->quantity) && !$objective->complete) {
                        echo exp_bar($player_change->sands, $objective->achievement()->quantity, 175, $player_change->sands . '/' . $objective->achievement()->quantity);
                    } else {
                        echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                    }
                }
                // Barrinhas de Progresso dos Areia

                // Barrinhas de Progresso dos Pets
                if ($objective->achievement()->pets) {
                    if ($objective->achievement()->quantity && !$objective->achievement()->item_id && !$objective->achievement()->rarity && !$objective->achievement()->happiness) {
                        $total_pets = sizeof($player->your_pets_achievement());
                        if (($total_pets < $objective->achievement()->quantity) && !$objective->complete) {
                            echo exp_bar($total_pets, $objective->achievement()->quantity, 175, $total_pets . '/' . $objective->achievement()->quantity);
                        } else {
                            echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                        }
                    }
                    if ($objective->achievement()->quantity && !$objective->achievement()->item_id && $objective->achievement()->rarity && !$objective->achievement()->happiness) {
                        $total_pets = sizeof($player->your_pets_achievement($objective->achievement()->rarity));
                        if (($total_pets < $objective->achievement()->quantity) && !$objective->complete) {
                            echo exp_bar($total_pets, $objective->achievement()->quantity, 175, $total_pets . '/' . $objective->achievement()->quantity);
                        } else {
                            echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                        }
                    }
                    if ($objective->achievement()->quantity && !$objective->achievement()->item_id && !$objective->achievement()->rarity && $objective->achievement()->happiness) {
                        $total_pets = sizeof($player->your_pets_achievement(NULL, $objective->achievement()->happiness));
                        if (($total_pets < $objective->achievement()->quantity) && !$objective->complete) {
                            echo exp_bar($total_pets, $objective->achievement()->quantity, 175, $total_pets . '/' . $objective->achievement()->quantity);
                        } else {
                            echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                        }
                    }
                }
                // Barrinhas de Progresso dos Pets

                // Barrinhas de Progresso da Missão de Tempo
                if ($objective->achievement()->time_quests) {
                    $player_quest = PlayerQuestCounter::find_first("player_id=". $player->id);
                    if (($player_quest->time_total < $objective->achievement()->quantity) && !$objective->complete) {
                        echo exp_bar($player_quest->time_total, $objective->achievement()->quantity, 175, $player_quest->time_total . '/' . $objective->achievement()->quantity);
                    } else {
                        echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                    }
                }
                // Barrinhas de Progresso da Missão de Tempo

                // Barrinhas de Progresso da Missão de Combate
                if ($objective->achievement()->battle_quests) {
                    $player_quest = PlayerQuestCounter::find_first("player_id=". $player->id);
                    if (($player_quest->combat_total < $objective->achievement()->quantity) && !$objective->complete) {
                        echo exp_bar($player_quest->combat_total, $objective->achievement()->quantity, 175, $player_quest->combat_total . '/' . $objective->achievement()->quantity);
                    } else {
                        echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                    }
                }
                // Barrinhas de Progresso da Missão de Combate

                // Barrinhas de Progresso da Missão de PVP
                if ($objective->achievement()->pvp_quests) {
                    $player_quest = PlayerQuestCounter::find_first("player_id=". $player->id);
                    if (($player_quest->pvp_total < $objective->achievement()->quantity) && !$objective->complete) {
                        echo exp_bar($player_quest->pvp_total, $objective->achievement()->quantity, 175, $player_quest->pvp_total . '/' . $objective->achievement()->quantity);
                    } else {
                        echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                    }
                }
                // Barrinhas de Progresso da Missão de PVP

                // Barrinhas de Progresso da Missão de Daily
                if ($objective->achievement()->daily_quests) {
                    $player_quest = PlayerQuestCounter::find_first("player_id=". $player->id);
                    if (($player_quest->daily_total < $objective->achievement()->quantity) && !$objective->complete) {
                        echo exp_bar($player_quest->daily_total, $objective->achievement()->quantity, 175, $player_quest->daily_total . '/' . $objective->achievement()->quantity);
                    } else {
                        echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                    }
                }
                // Barrinhas de Progresso da Missão de Daily

                // Barrinhas de Progresso da Missão de Daily
                if ($objective->achievement()->account_quests) {
                    $user_quest = UserQuestCounter::find_first("user_id=". $player->user_id);
                    if (($user_quest->daily_total < $objective->achievement()->quantity) && !$objective->complete) {
                        echo exp_bar($user_quest->daily_total, $objective->achievement()->quantity, 175, $user_quest->daily_total . '/' . $objective->achievement()->quantity);
                    } else {
                        echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);

                    }
                }
                // Barrinhas de Progresso da Missão de Daily

                // Barrinhas de Progresso de Batalha NPC
                if ($objective->achievement()->battle_npc) {
                    if (($player->wins_npc < $objective->achievement()->quantity) && !$objective->complete) {
                        echo exp_bar($player->wins_npc, $objective->achievement()->quantity, 175, $player->wins_npc . '/' . $objective->achievement()->quantity);
                    } else {
                        echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                    }
                }
                // Barrinhas de Progresso de Batalha NPC

                // Barrinhas de Progresso de Batalha PVP
                if ($objective->achievement()->battle_pvp) {
                    // Só quer saber a quantidade de pvps
                    if ($objective->achievement()->battle_pvp && !$objective->achievement()->anime_id && !$objective->achievement()->character_id && !$objective->achievement()->faction_id) {
                        if (($player->wins_pvp < $objective->achievement()->quantity) && !$objective->complete) {
                            echo exp_bar($player->wins_pvp, $objective->achievement()->quantity, 175, $player->wins_pvp . '/' . $objective->achievement()->quantity);
                        } else {
                            echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                        }
                    // Quer saber a quantidade de pvps com determinada facção
                    } elseif ($objective->achievement()->battle_pvp && !$objective->achievement()->anime_id && !$objective->achievement()->character_id && $objective->achievement()->faction_id) {
                        $player_achievement_stats = Recordset::query("select sum(quantity) as total from player_achievement_stats WHERE player_id=".$player->id." AND faction_id=".$objective->achievement()->faction_id)->result_array();
                        if (($player_achievement_stats[0]['total'] < $objective->achievement()->quantity) && !$objective->complete) {
                            echo exp_bar(($player_achievement_stats[0]['total'] ? $player_achievement_stats[0]['total'] : 0), $objective->achievement()->quantity, 175, ($player_achievement_stats[0]['total'] ? $player_achievement_stats[0]['total'] : 0) . '/' . $objective->achievement()->quantity);
                        } else {
                            echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                        }
                    // Quer saber a quantidade de pvps com determinada anime
                    } elseif ($objective->achievement()->battle_pvp && $objective->achievement()->anime_id && !$objective->achievement()->character_id && !$objective->achievement()->faction_id) {
                        $player_achievement_stats = Recordset::query("select sum(quantity) as total from player_achievement_stats WHERE player_id=".$player->id." AND anime_id=".$objective->achievement()->anime_id)->result_array();
                        if (($player_achievement_stats[0]['total'] < $objective->achievement()->quantity) && !$objective->complete) {
                            echo exp_bar(($player_achievement_stats[0]['total'] ? $player_achievement_stats[0]['total'] : 0), $objective->achievement()->quantity, 175, ($player_achievement_stats[0]['total'] ? $player_achievement_stats[0]['total'] : 0) . '/' . $objective->achievement()->quantity);
                        } else {
                            echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);

                        }
                    // Quer saber a quantidade de pvps com determinada personagem
                    } elseif ($objective->achievement()->battle_pvp && !$objective->achievement()->anime_id && $objective->achievement()->character_id && !$objective->achievement()->faction_id) {
                        $player_achievement_stats = Recordset::query("select sum(quantity) as total from player_achievement_stats WHERE player_id=".$player->id." AND character_id=".$objective->achievement()->character_id)->result_array();
                        if (($player_achievement_stats[0]['total'] < $objective->achievement()->quantity) && !$objective->complete) {
                            echo exp_bar(($player_achievement_stats[0]['total'] ? $player_achievement_stats[0]['total'] : 0), $objective->achievement()->quantity, 175, ($player_achievement_stats[0]['total'] ? $player_achievement_stats[0]['total'] : 0) . '/' . $objective->achievement()->quantity);
                        } else {
                            echo exp_bar($objective->achievement()->quantity, $objective->achievement()->quantity, 175, $objective->achievement()->quantity . '/' . $objective->achievement()->quantity);
                        }
                    }
                }
                // Barrinhas de Progresso de Batalha PVP
                ?>
            </div>
        </div>
    <?php } ?>
</div>
<?=partial('shared/info', [
    'id'		=> 5,
    'title'		=> 'objectives.mensagens.title2',
    'message'	=> t('objectives.mensagens.description2', [
        'points' => $user->round_points
    ])
]);?><br />
<ul class="nav nav-pills nav-justified" id="luck-list-tabs">
    <?php $first = TRUE; ?>
    <?php foreach ($item_type_ids->result() as $item_type_id): ?>
        <?php
        $name = '';
        switch ($item_type_id->item_type_id) {
            case 1: $name = t('objectives.item_types.1');   break;
            case 2: $name = t('objectives.item_types.2');   break;
            case 3: $name = t('objectives.item_types.3');   break;
            case 4: $name = t('objectives.item_types.4');   break;
            case 5: $name = t('objectives.item_types.5');   break;
            case 6: $name = t('objectives.item_types.6');   break;
            case 7: $name = t('objectives.item_types.7');   break;
        }
        ?>
        <li <?php echo $first ? 'class="active"' : '' ?>><a href="#luck-tab-<?php echo $item_type_id->item_type_id ?>"><?php echo $name ?></a></li>
        <?php $first = FALSE; ?>
    <?php endforeach ?>
</ul>
<br />
<div class="tab-content" id="luck-list-content">
    <?php $first = true; ?>
    <?php foreach ($item_type_ids->result() as $item_type_id): ?>
        <div class="tab-pane<?php echo $first ? ' active' : '' ?>" id="luck-tab-<?php echo $item_type_id->item_type_id ?>">
            <?php foreach ($rewards as $reward): ?>
                <?php $i_have = false;?>
                <?php if ($reward->item_type_id != $item_type_id->item_type_id) { continue; } ?>
                <div  class="ability-speciality-box" data-item="<?=$reward->id;?>" style="height: 200px;">
                    <div class="image">
                        <img src="<?php echo image_url('rounds/rewards/' . $reward->id . '.png') ?>" />
                    </div>
                    <div class="name" style="height: 50px;">
                        <?php
                        $message	= '';

                       if ($reward->enchant_points) {
                            $message	.= highamount($reward->quantity) . ' ' . t('luck.index.names.8');
                        }

                       if ($reward->currency) {
                            $message	.= highamount($reward->currency) . ' ' . t('currencies.' . $player->character()->anime_id);
                        }
                       if ($reward->exp) {
                            $message	.= highamount($reward->exp) . ' ' . t('attributes.attributes.exp2');
                        }
                       if ($reward->exp_account) {
                            $message	.= highamount($reward->exp_account) . ' ' . t('objectives.exp_account');
                        }
                       if ($reward->credits) {
                            $message	.= highamount($reward->credits) . ' ' . t('currencies.credits');
                        }
                       if ($reward->character_id) {
                            $message	.= Character::find($reward->character_id)->description()->name;
                            $i_have 	 = $user->is_character_bought($reward->character_id) ? true : false;
                        }
                       if ($reward->character_theme_id) {
                            $message	.= CharacterTheme::find($reward->character_theme_id)->description()->name;
                            $i_have 	 = $user->is_theme_bought($reward->character_theme_id) ? true : false;
                        }
                       if ($reward->headline_id) {
                            $message	.= Headline::find($reward->headline_id)->description()->name;
                            $i_have 	 = $user->is_headline_bought($reward->headline_id) ? true : false;
                        }
                       if ($reward->equipment) {
                            switch ($reward->equipment) {
                                case 2:
                                    $message	.= highamount($reward->quantity) . ' ' . t('objectives.equip_comun');
                                    break;
                                case 3:
                                    $message	.= highamount($reward->quantity) . ' ' . t('objectives.equip_raro');
                                    break;
                                case 4:
                                    $message	.= highamount($reward->quantity) . ' ' . t('objectives.equip_lendario');
                                    break;
                            }
                        }

                       if ($reward->item_id) {
                            $item		= Item::find_first($reward->item_id);
                            $message	.= $item->description()->name . ' x' . highamount($reward->quantity);
                        }

                        $ats	= [
                            'for_atk'		=> t('formula.for_atk'),
                            'for_def'		=> t('formula.for_def'),
                            'for_crit'		=> t('formula.for_crit'),
                            'for_abs'		=> t('formula.for_abs'),
                            'for_prec'		=> t('formula.for_prec'),
                            'for_init'		=> t('formula.for_init'),
                            'for_inc_crit'	=> t('formula.for_inc_crit'),
                            'for_inc_abs'	=> t('formula.for_inc_abs')
                        ];

                        foreach ($ats as $key => $value) {
                           if ($reward->$key) {
                                $message	.= t('luck.index.messages.point', [
                                    'count' => $reward->$key,
                                    'attribute' => $value
                                ]);
                            }
                        }
                        echo $message;
                        ?>
                    </div>
                    <div class="details">
                        <?php if (!$i_have) { ?>
                            <?php if ($user->round_points >= $reward->chance) { ?>
                                <?php if ($reward->luck_reward_log($reward->id, $player->id)) { ?>
                                    <span class="laranja"><?=t('objectives.ja_trocou');?></span>
                                <?php } else { ?>
                                    <a class="btn btn-sm btn-primary objective_change" data-id="<?=$reward->id;?>"><?=t('objectives.buy_now', ['points' => $reward->chance]);?></a>
                                <?php } ?>
                            <?php } else { ?>
                                <button type="button" class="btn btn-sm btn-disabled btn-danger" disabled><?=t('objectives.buy_now', ['points' => $reward->chance]);?></a>
                            <?php } ?>
                        <?php } else { ?>
                            <button type="button" class="btn btn-sm btn-disabled btn-success" disabled><?=t('objectives.ja_adquirido');?></a>
                        <?php } ?>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
        <?php $first = false; ?>
    <?php endforeach ?>
</div>