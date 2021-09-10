<?php if (!$profile) { ?>
	<div class="alert alert-danger text-center">
		<b><?=t('profile.unknow.description');?></b>
	</div>
<?php } else { ?>
	<script type="text/javascript">
		$('.technique-popover, .requirement-popover, .shop-item-popover').each(function () {
			$(this).popover({
				trigger:	'manual',
				content:	function () {
					return $($(this).data('source')).html();
				},
				html:		true
			}).on("mouseenter", function () {
				var _this = this;
				$(this).popover("show");
				$(this).siblings(".popover").on("mouseleave", function () {
					$(_this).popover('hide');
				});
			}).on("mouseleave", function () {
				var _this = this;
				setTimeout(function () {
					if (!$(".popover:hover").length) {
						$(_this).popover("hide")
					}
				}, 100);
			});
		});
	</script>
	<?php foreach ($achievements as $achievement):
		$player_achievement = $achievement->player_achievement($profile->id, $achievement->id);
		$on_off  = $player_achievement ? "on" : "off";
	?>
		<div class="ability-speciality-box <?=($on_off == "on" ? 'active' : '')?>" style="height: auto;">
			<div class="image">
				<img src="<?=image_url('achievement/trophy-' . $on_off . '.png')?>">
			</div>
			<div class="name" style="height:55px !important;">
				<span class="a-name-on"><?php echo $achievement->description()->name;?></span>
				<span style="top:5px; position: relative; font-size: 11px">
					<?php
					if ($on_off == "on") {
						$timestamp = strtotime($player_achievement->created_at);
						echo '<br />' . date('d/m/Y H:i:s', $timestamp);
					}
					?>
				</span>
			</div>
			<div class="details" style="padding: 9px;">
				<div class="a-req">
					<div class="<?=$on_off;?> requirement-popover" data-source="#tooltip-req-<?=$achievement->id;?>" data-title="<?=$achievement->description()->name;;?>" data-trigger="hover" data-placement="bottom">
						<img src="<?=image_url('achievement/req_' . $on_off . '.png')?>" width="38" />
					</div>
					<div id="tooltip-req-<?php echo $achievement->id?>" class="status-popover-container">
						<div class="status-popover-content">
							<?php echo $achievement->description()->description;?>
							<?php
							if ($on_off != "on") {
								// Barrinhas de Progresso dos Amigos
								if ($achievement->friends > 1) {
									$player_friends = Recordset::query("select count(id) as total from player_friend_lists WHERE  player_id=".$profile->id)->result_array();

									if ($player_friends[0]['total'] < $achievement->friends) {
										echo exp_bar($player_friends[0]['total'], $achievement->friends, 175, $player_friends[0]['total'] . '/' . $achievement->friends);
									}
								} elseif ($achievement->friends == 1 && $achievement->friends_send_gifts) {
									$player_send_gifts = Recordset::query("select count(id) as total from player_gift_logs WHERE  player_id=".$profile->id)->result_array();

									if ($player_send_gifts[0]['total'] < $achievement->friends_send_gifts) {
										echo exp_bar($player_send_gifts[0]['total'], $achievement->friends_send_gifts, 175, $player_send_gifts[0]['total'] . '/' . $achievement->friends_send_gifts);
									}
								} elseif ($achievement->friends == 1 && $achievement->friends_received_gifts) {
									$friends_received_gifts = Recordset::query("select count(id) as total from player_gift_logs WHERE  friend_id=".$profile->id)->result_array();

									if ($friends_received_gifts[0]['total'] < $achievement->friends_received_gifts) {
										echo exp_bar($friends_received_gifts[0]['total'], $achievement->friends_received_gifts, 175, $friends_received_gifts[0]['total'] . '/' . $achievement->friends_received_gifts);
									}
								}
								// Barrinhas de Progresso dos Amigos

								// Barrinhas do Mapa
								if ($achievement->map == 1 && $achievement->anime_id) {
									$player_map_anime = PlayerMapLog::find("player_id=". $profile->id." AND anime_id=".$achievement->anime_id);
									if (sizeof($player_map_anime) < $achievement->quantity) {
										echo exp_bar(sizeof($player_map_anime), $achievement->quantity, 175, sizeof($player_map_anime) . '/' . $achievement->quantity);
									}
								}
								if ($achievement->map == 2 && $achievement->anime_id) {
									$player_map_anime = Recordset::query("select sum(quantity) as total from player_map_logs WHERE anime_id=".$achievement->anime_id." and player_id=".$profile->id)->result_array();
									if($player_map_anime[0]['total'] < $achievement->quantity){
										echo exp_bar($player_map_anime[0]['total'], $achievement->quantity, 175, $player_map_anime[0]['total'] . '/' . $achievement->quantity);
									}
								}
								if ($achievement->map == 2 && !$achievement->anime_id) {
									$player_map_anime = Recordset::query("select sum(quantity) as total from player_map_logs WHERE player_id=".$profile->id)->result_array();
									if ($player_map_anime[0]['total'] < $achievement->quantity) {
										echo exp_bar($player_map_anime[0]['total'], $achievement->quantity, 175, $player_map_anime[0]['total'] . '/' . $achievement->quantity);
									}
								}
								// Barrinhas do Mapa

								// Barrinhas de Progresso dos Tesouros
								if ($achievement->treasure) {
									if ($profile->treasure_total < $achievement->quantity) {
										echo exp_bar($profile->treasure_total, $achievement->quantity, 175, $profile->treasure_total . '/' . $achievement->quantity);
									}
								}
								// Barrinhas de Progresso dos Tesouros

								// Barrinhas de Progresso dos Fragmentos
								if ($achievement->fragments == 1) {
									$player_fragments = PlayerItem::find_first("player_id=". $profile->id." AND item_id=446");
									if ($player_fragments) {
										if ($player_fragments->quantity < $achievement->quantity) {
											echo exp_bar($player_fragments->quantity, $achievement->quantity, 175, $player_fragments->quantity . '/' . $achievement->quantity);
										}
									} else {
										echo exp_bar(0, $achievement->quantity, 175, '0/' . $achievement->quantity);
									}
								}
								if ($achievement->fragments == 2) {
									$player_change = PlayerStat::find_first("player_id=".$profile->id);
									if ($player_change->fragments < $achievement->quantity){
										echo exp_bar($player_change->fragments, $achievement->quantity, 175, $player_change->fragments . '/' . $achievement->quantity);
									}
								}
								// Barrinhas de Progresso dos Fragmentos

								// Barrinhas de Progresso dos wanted
								if ($achievement->wanted == 1) {
									$player_wanted = Recordset::query("select count(id) as total from player_wanteds WHERE enemy_id=".$profile->id)->result_array();
									if ($player_wanted[0]['total'] < $achievement->quantity) {
										echo exp_bar($player_wanted[0]['total'], $achievement->quantity, 175, $player_wanted[0]['total'] . '/' . $achievement->quantity);
									}
								}
								if ($achievement->wanted == 2) {
									if ($profile->won_last_battle < $achievement->quantity) {
										echo exp_bar($profile->won_last_battle, $achievement->quantity, 175, $profile->won_last_battle . '/' . $achievement->quantity);
									}
								}
								// Barrinhas de Progresso dos wanted

								// Barrinhas de Progresso dos Sangues
								if ($achievement->bloods == 1) {
									$player_fragments = PlayerItem::find_first("player_id=". $profile->id." AND item_id=1720");
									if ($player_fragments) {
										if ($player_fragments->quantity < $achievement->quantity) {
											echo exp_bar($player_fragments->quantity, $achievement->quantity, 175, $player_fragments->quantity . '/' . $achievement->quantity);
										}
									} else {
										echo exp_bar(0, $achievement->quantity, 175, '0/' . $achievement->quantity);
									}
								}
								if ($achievement->bloods == 2){
									$player_change = PlayerStat::find_first("player_id=".$profile->id);
									if ($player_change->bloods < $achievement->quantity) {
										echo exp_bar($player_change->bloods, $achievement->quantity, 175, $player_change->bloods . '/' . $achievement->quantity);
									}
								}
								// Barrinhas de Progresso dos Fragmentos

								// Barrinhas de Progresso dos Areia
								if ($achievement->sands == 1) {
									$player_fragments = PlayerItem::find_first("player_id=". $profile->id." AND item_id=1719");
									if ($player_fragments) {
										if ($player_fragments->quantity < $achievement->quantity) {
											echo exp_bar($player_fragments->quantity, $achievement->quantity, 175, $player_fragments->quantity . '/' . $achievement->quantity);
										}
									} else {
										echo exp_bar(0, $achievement->quantity, 175, '0/' . $achievement->quantity);
									}
								}
								if ($achievement->sands == 2) {
									$player_change = PlayerStat::find_first("player_id=".$profile->id);
									if ($player_change->sands < $achievement->quantity) {
										echo exp_bar($player_change->sands, $achievement->quantity, 175, $player_change->sands . '/' . $achievement->quantity);
									}
								}
								// Barrinhas de Progresso dos Fragmentos

								// Barrinhas de Progresso dos Equipamentos
								if ($achievement->equipment == 1) {
									$player_equipments = Recordset::query("select count(id) as total from player_items WHERE player_id=".$profile->id." AND item_id in (select id from items WHERE item_type_id=8) AND rarity='".$achievement->rarity."'")->result_array();
									if ($player_equipments[0]['total'] < $achievement->quantity) {
										echo exp_bar($player_equipments[0]['total'], $achievement->quantity, 175, $player_equipments[0]['total'] . '/' . $achievement->quantity);
									}
								}
								if ($achievement->equipment == 2) {
									$player_equipments = Recordset::query("select count(id) as total from player_items WHERE player_id=".$profile->id." AND item_id in (select id from items WHERE item_type_id=8) AND rarity='".$achievement->rarity."' AND equipped=1")->result_array();
									if ($player_equipments[0]['total'] < $achievement->quantity) {
										echo exp_bar($player_equipments[0]['total'], $achievement->quantity, 175, $player_equipments[0]['total'] . '/' . $achievement->quantity);
									}
								}
								// Barrinhas de Progresso dos Fragmentos

								// Barrinhas de Progresso do Dinheiro
								if ($achievement->currency) {
									if ($profile->currency < $achievement->quantity) {
										echo exp_bar($profile->currency, $achievement->quantity, 175, $profile->currency . '/' . $achievement->quantity);
									}
								}
								//Barrinhas de Progresso do Dinheiro

								// Barrinhas de Progresso das Estrelas
								if ($achievement->credits) {
									if ($user_profile->credits < $achievement->quantity) {
										echo exp_bar($user_profile->credits, $achievement->quantity, 175, $user_profile->credits . '/' . $achievement->quantity);
									}
								}
								// Barrinhas de Progresso das Estrelas

								// Barrinhas de Progresso dos Pets
								if ($achievement->pets) {
									if ($achievement->quantity && !$achievement->item_id && !$achievement->rarity && !$achievement->happiness) {
										$total_pets = sizeof($profile->your_pets_achievement());
										if ($total_pets < $achievement->quantity) {
											echo exp_bar($total_pets, $achievement->quantity, 175, $total_pets . '/' . $achievement->quantity);
										}
									}
									if ($achievement->quantity && !$achievement->item_id && $achievement->rarity && !$achievement->happiness) {
										$total_pets = sizeof($profile->your_pets_achievement($achievement->rarity));
										if ($total_pets < $achievement->quantity) {
											echo exp_bar($total_pets, $achievement->quantity, 175, $total_pets . '/' . $achievement->quantity);
										}
									}
									if ($achievement->quantity && !$achievement->item_id && !$achievement->rarity && $achievement->happiness) {
										$total_pets = sizeof($profile->your_pets_achievement(NULL, $achievement->happiness));
										if ($total_pets < $achievement->quantity) {
											echo exp_bar($total_pets, $achievement->quantity, 175, $total_pets . '/' . $achievement->quantity);
										}
									}
								}
								// Barrinhas de Progresso dos Pets

								// Barrinhas de Progresso da Missão de Tempo
								if ($achievement->time_quests) {
									$player_quest = PlayerQuestCounter::find_first("player_id=". $profile->id);
									if ($player_quest->time_total < $achievement->quantity) {
										echo exp_bar($player_quest->time_total, $achievement->quantity, 175, $player_quest->time_total . '/' . $achievement->quantity);
									}
								}
								// Barrinhas de Progresso da Missão de Tempo

								// Barrinhas de Progresso da Missão de Combate
								if ($achievement->battle_quests) {
									$player_quest = PlayerQuestCounter::find_first("player_id=". $profile->id);
									if ($player_quest->combat_total < $achievement->quantity) {
										echo exp_bar($player_quest->combat_total, $achievement->quantity, 175, $player_quest->combat_total . '/' . $achievement->quantity);
									}
								}
								// Barrinhas de Progresso da Missão de Combate

								// Barrinhas de Progresso da Missão de PVP
								if ($achievement->pvp_quests) {
									$player_quest = PlayerQuestCounter::find_first("player_id=". $profile->id);
									if ($player_quest->pvp_total < $achievement->quantity) {
										echo exp_bar($player_quest->pvp_total, $achievement->quantity, 175, $player_quest->pvp_total . '/' . $achievement->quantity);
									}
								}
								// Barrinhas de Progresso da Missão de PVP

								// Barrinhas de Progresso da Missão de Daily
								if ($achievement->daily_quests) {
									$player_quest = PlayerQuestCounter::find_first("player_id=". $profile->id);
									if ($player_quest->daily_total < $achievement->quantity) {
										echo exp_bar($player_quest->daily_total, $achievement->quantity, 175, $player_quest->daily_total . '/' . $achievement->quantity);
									}
								}
								// Barrinhas de Progresso da Missão de Daily

								// Barrinhas de Progresso da Missão de Conta
								if ($achievement->account_quests) {
									$user_quest = UserQuestCounter::find_first("user_id=". $profile->user_id);
									if ($user_quest->daily_total < $achievement->quantity) {
										echo exp_bar($user_quest->daily_total, $achievement->quantity, 175, $user_quest->daily_total . '/' . $achievement->quantity);
									}
								}
								// Barrinhas de Progresso da Missão de Conta

								// Barrinhas de Progresso da Missão de Pet
								if ($achievement->pet_quests) {
									$player_quest = PlayerQuestCounter::find_first("player_id=". $profile->id);
									if ($player_quest->pet_total < $achievement->quantity) {
										echo exp_bar($player_quest->pet_total, $achievement->quantity, 175, $player_quest->pet_total . '/' . $achievement->quantity);
									}
								}
								// Barrinhas de Progresso da Missão de Pet

								// Barrinhas de Progresso da Missão de Organização
								if ($achievement->weekly_quests) {
									$guild_quest = GuildQuestCounter::find_first("guild_id=". $profile->guild_id);
									if ($guild_quest) {
										if ($guild_quest->daily_total < $achievement->quantity) {
											echo exp_bar($guild_quest->daily_total, $achievement->quantity, 175, $guild_quest->daily_total . '/' . $achievement->quantity);
										}
									} else {
										echo exp_bar(0, $achievement->quantity, 175, '0/' . $achievement->quantity);
									}
								}
								// Barrinhas de Progresso da Missão de Organização

								// Barrinhas de Progresso de Batalha NPC
								if ($achievement->battle_npc) {
									if ($profile->wins_npc < $achievement->quantity) {
										echo exp_bar($profile->wins_npc, $achievement->quantity, 175, $profile->wins_npc . '/' . $achievement->quantity);
									}
								}
								// Barrinhas de Progresso de Batalha NPC

								// Barrinhas de Progresso de Batalha PVP
								if($achievement->battle_pvp){

									// Só quer saber a quantidade de pvps
									if ($achievement->battle_pvp && !$achievement->anime_id && !$achievement->character_id && !$achievement->faction_id) {
										if ($profile->wins_pvp < $achievement->quantity) {
											echo exp_bar($profile->wins_pvp, $achievement->quantity, 175, $profile->wins_pvp . '/' . $achievement->quantity);
										}
									// Quer saber a quantidade de pvps com determinada facção
									} elseif ($achievement->battle_pvp && !$achievement->anime_id && !$achievement->character_id && $achievement->faction_id) {
										$player_achievement_stats = Recordset::query("select sum(quantity) as total from player_achievement_stats WHERE player_id=".$profile->id." AND faction_id=".$achievement->faction_id)->result_array();
										if ($player_achievement_stats[0]['total'] < $achievement->quantity) {
											echo exp_bar(($player_achievement_stats[0]['total'] ? $player_achievement_stats[0]['total'] : 0), $achievement->quantity, 175, ($player_achievement_stats[0]['total'] ? $player_achievement_stats[0]['total'] : 0) . '/' . $achievement->quantity);
										}
									// Quer saber a quantidade de pvps com determinada anime
									} elseif ($achievement->battle_pvp && $achievement->anime_id && !$achievement->character_id && !$achievement->faction_id) {
										$player_achievement_stats = Recordset::query("select sum(quantity) as total from player_achievement_stats WHERE player_id=".$profile->id." AND anime_id=".$achievement->anime_id)->result_array();
										if ($player_achievement_stats[0]['total'] < $achievement->quantity) {
											echo exp_bar(($player_achievement_stats[0]['total'] ? $player_achievement_stats[0]['total'] : 0), $achievement->quantity, 175, ($player_achievement_stats[0]['total'] ? $player_achievement_stats[0]['total'] : 0) . '/' . $achievement->quantity);
										}
									// Quer saber a quantidade de pvps com determinada personagem
									} elseif ($achievement->battle_pvp && !$achievement->anime_id && $achievement->character_id && !$achievement->faction_id) {
										$player_achievement_stats = Recordset::query("select sum(quantity) as total from player_achievement_stats WHERE player_id=".$profile->id." AND character_id=".$achievement->character_id)->result_array();
										if ($player_achievement_stats[0]['total'] < $achievement->quantity) {
											echo exp_bar(($player_achievement_stats[0]['total'] ? $player_achievement_stats[0]['total'] : 0), $achievement->quantity, 175, ($player_achievement_stats[0]['total'] ? $player_achievement_stats[0]['total'] : 0) . '/' . $achievement->quantity);
										}
									}
								}
								// Barrinhas de Progresso de Batalha PVP
							}
							?>
						</div>
					</div>
				</div>
				<div class="a-gift">
					<div class="<?=$on_off;?> requirement-popover" data-source="#tooltip-gift-<?=$achievement->id;?>" data-title="Recompensa" data-trigger="hover" data-placement="bottom">
						<img src="<?=image_url('achievement/gift_' . $on_off . '.png')?>" width="38" />
					</div>
					<div id="tooltip-gift-<?php echo $achievement->id?>" class="status-popover-container">
						<div class="status-popover-content">
							<?php
								$rewards = $achievement->achievement_rewards($achievement->id);
							?>
							<?php if($rewards){?>
								<ul>
									<?php if($rewards->exp){?>
										<li><?php echo highamount($rewards->exp); ?> <?php echo t('ranked.exp');?></li>
									<?php }?>
									<?php if($rewards->exp_user){?>
										<li><?php echo highamount($rewards->exp_user); ?> <?php echo t('ranked.exp_account');?></li>
									<?php }?>
									<?php if($rewards->currency){?>
										<li><?php echo highamount($rewards->currency); ?> <?php echo t('currencies.' . $profile->character()->anime_id) ?></li>
									<?php }?>
									<?php if($rewards->credits){?>
										<li><?php echo highamount($rewards->credits); ?> <?php echo t('treasure.show.credits')?></li>
									<?php }?>
									<?php if($rewards->item_id){?>
										<li><?php echo highamount($rewards->quantity);?>x "<?php echo Item::find($rewards->item_id)->description()->name ?>"</li>
									<?php }?>
									<?php if($rewards->character_theme_id){?>
										<li><?php echo t('treasure.show.theme')?> "<?php echo CharacterTheme::find($rewards->character_theme_id)->description()->name ?>"</li>
									<?php }?>
									<?php if($rewards->character_id){?>
										<li><?php echo t('treasure.show.character')?> "<?php echo Character::find($rewards->character_id)->description()->name ?>"</li>
									<?php }?>
									<?php if($rewards->equipment){?>
										<li><?php echo t('event.e12');?></li>
									<?php }?>
									<?php if($rewards->pet){?>
										<li><?php echo t('event.e14');?></li>
									<?php }?>
									<?php if($rewards->headline_id){?>
										<li><?php echo t('treasure.show.headline')?> "<?php echo Headline::find($rewards->headline_id)->description()->name ?>"</li>
									<?php }?>
								</ul>
							<?php }else{?>
								<span>Conquista sem premiação</span>
							<?php }?>
						</div>
					</div>
				</div>
				<div class="a-point">
					<div class="<?=$on_off;?> requirement-popover" data-source="#tooltip-point-<?=$achievement->id;?>" data-title="Pontos de Conquista" data-trigger="hover" data-placement="bottom">
						<span class="a-point-<?=$on_off;?>"><?=highamount($achievement->points);?></span>
					</div>
					<div id="tooltip-point-<?php echo $achievement->id?>" class="status-popover-container">
						<div class="status-popover-content">
							Ao concluir essa conquista você ganhará <?=highamount($achievement->points);?> pontos de conquista
						</div>
					</div>
				</div>
				<div class="break"></div>
			</div>
			<div class="button" style="position:relative; top: 15px;">
			</div>
		</div>
	<?php endforeach; ?>
<?php } ?>
