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
	$player_achievement = $achievement->player_achievement($player->id, $achievement->id);
	$on_off  = $player_achievement ? "on" : "off";  	
?>
	<div class="a-bg <?=$on_off;?>">
		<div class="a-name">
			<div align="center" style="width: 350px; position: relative; left: 140px; top: 39px;">
				<span class="a-name-<?php echo $on_off?>">
					<?php echo $achievement->description()->name;?><br/>
				</span>
				<span style="top:5px; position: relative">
					<?php 
						if($on_off=="on"){
							$timestamp = strtotime($player_achievement->created_at);
							echo date('d/m/Y H:i:s', $timestamp);
						}
					?>
				</span>
			</div>
		</div>
		<div class="a-req">
			<div class="<?php echo $on_off?> requirement-popover" data-source="#tooltip-req-<?php echo $achievement->id?>" data-title="<?php echo $achievement->description()->name;?>" data-trigger="hover" data-placement="bottom">
				<img src="<?php echo image_url('achievement/req_'.$on_off.'.png') ?>" />
				<div id="tooltip-req-<?php echo $achievement->id?>" class="status-popover-container">
					<div class="status-popover-content">
						<?php echo $achievement->description()->description;?>
						<?php 
							//Barrinhas de Progresso dos Amigos
							if($achievement->friends > 1){
								$player_friends = Recordset::query("select count(id) as total from player_friend_lists WHERE  player_id=".$player->id)->result_array();
								
								if($player_friends[0]['total'] < $achievement->friends){
									echo exp_bar($player_friends[0]['total'].'/'.$achievement->friends, $achievement->friends, 175);
								}
							}else if($achievement->friends == 1 && $achievement->friends_send_gifts){
								$player_send_gifts = Recordset::query("select count(id) as total from player_gift_logs WHERE  player_id=".$player->id)->result_array();

								if($player_send_gifts[0]['total'] < $achievement->friends_send_gifts){
									echo exp_bar($player_send_gifts[0]['total'].'/'.$achievement->friends_send_gifts, $achievement->friends_send_gifts, 175);
								}
							}else if($achievement->friends == 1 && $achievement->friends_received_gifts){
								$friends_received_gifts = Recordset::query("select count(id) as total from player_gift_logs WHERE  friend_id=".$player->id)->result_array();

								if($friends_received_gifts[0]['total'] < $achievement->friends_received_gifts){
									echo exp_bar($friends_received_gifts[0]['total'].'/'.$achievement->friends_received_gifts, $achievement->friends_received_gifts, 175);
								}
							}
							//Barrinhas de Progresso dos Amigos
							// Barrinhas do Mapa
							if($achievement->map==1 && $achievement->anime_id){
								$player_map_anime = PlayerMapLog::find("player_id=". $player->id." AND anime_id=".$achievement->anime_id);
								if(sizeof($player_map_anime) < $achievement->quantity){
									echo exp_bar(sizeof($player_map_anime).'/'.$achievement->quantity, $achievement->quantity, 175);
								}
							}
							if($achievement->map==2 && $achievement->anime_id){
								$player_map_anime = Recordset::query("select sum(quantity) as total from player_map_logs WHERE anime_id=".$achievement->anime_id." and player_id=".$player->id)->result_array();
								if($player_map_anime[0]['total'] < $achievement->quantity){
									echo exp_bar($player_map_anime[0]['total'].'/'.$achievement->quantity, $achievement->quantity, 175);
								}
							}
							if($achievement->map==2 && !$achievement->anime_id){
								$player_map_anime = Recordset::query("select sum(quantity) as total from player_map_logs WHERE player_id=".$player->id)->result_array();
								if($player_map_anime[0]['total'] < $achievement->quantity){
									echo exp_bar($player_map_anime[0]['total'].'/'.$achievement->quantity, $achievement->quantity, 175);
								}
							}
							
							// Barrinhas do Mapa
							//Barrinhas de Progresso dos Tesouros
							if($achievement->treasure){
								if($player->treasure_total < $achievement->quantity){
									echo exp_bar($player->treasure_total.'/'.$achievement->quantity, $achievement->quantity, 175);
								}
							}
							//Barrinhas de Progresso dos Tesouros
							//Barrinhas de Progresso dos Fragmentos
							if($achievement->fragments==1){
								$player_fragments = PlayerItem::find_first("player_id=". $player->id." AND item_id=446");
								if($player_fragments){
									if($player_fragments->quantity < $achievement->quantity){
										echo exp_bar($player_fragments->quantity.'/'.$achievement->quantity, $achievement->quantity, 175);
									}
								}else{
										echo exp_bar('0 /'.$achievement->quantity, $achievement->quantity, 175);

								}
							}
							if($achievement->fragments==2){
								$player_change = PlayerStat::find_first("player_id=".$player->id);
								if($player_change->fragments < $achievement->quantity){
									echo exp_bar($player_change->fragments.'/'.$achievement->quantity, $achievement->quantity, 175);
								}
							}
							//Barrinhas de Progresso dos Fragmentos
							//Barrinhas de Progresso dos wanted
							if($achievement->wanted==1){
								$player_wanted = Recordset::query("select count(id) as total from player_wanteds WHERE enemy_id=".$player->id)->result_array();
								if($player_wanted[0]['total'] < $achievement->quantity){
									echo exp_bar($player_wanted[0]['total'].'/'.$achievement->quantity, $achievement->quantity, 175);
								}
							}
							if($achievement->wanted==2){
								if($player->won_last_battle < $achievement->quantity){
									echo exp_bar($player->won_last_battle.'/'.$achievement->quantity, $achievement->quantity, 175);
								}
							}
							//Barrinhas de Progresso dos wanted
							//Barrinhas de Progresso dos Sangues
							if($achievement->bloods==1){
								$player_fragments = PlayerItem::find_first("player_id=". $player->id." AND item_id=1720");
								if($player_fragments){
									if($player_fragments->quantity < $achievement->quantity){
										echo exp_bar($player_fragments->quantity.'/'.$achievement->quantity, $achievement->quantity, 175);
									}
								}else{
										echo exp_bar('0 /'.$achievement->quantity, $achievement->quantity, 175);
								}
							}
							if($achievement->bloods==2){
								$player_change = PlayerStat::find_first("player_id=".$player->id);
								if($player_change->bloods < $achievement->quantity){
									echo exp_bar($player_change->bloods.'/'.$achievement->quantity, $achievement->quantity, 175);
								}
							}
							//Barrinhas de Progresso dos Fragmentos
							//Barrinhas de Progresso dos Areia
							if($achievement->sands==1){
								$player_fragments = PlayerItem::find_first("player_id=". $player->id." AND item_id=1719");
								if($player_fragments){
									if($player_fragments->quantity < $achievement->quantity){
										echo exp_bar($player_fragments->quantity.'/'.$achievement->quantity, $achievement->quantity, 175);
									}
								}else{
										echo exp_bar('0 /'.$achievement->quantity, $achievement->quantity, 175);
								}
							}
							if($achievement->sands==2){

								$player_change = PlayerStat::find_first("player_id=".$player->id);
								if($player_change->sands < $achievement->quantity){
									echo exp_bar($player_change->sands.'/'.$achievement->quantity, $achievement->quantity, 175);
								}
							}
							//Barrinhas de Progresso dos Fragmentos
							//Barrinhas de Progresso dos Equipamentos
							if($achievement->equipment == 1){
								$player_equipments = Recordset::query("select count(id) as total from player_items WHERE player_id=".$player->id." AND item_id in (select id from items WHERE item_type_id=8) AND rarity='".$achievement->rarity."'")->result_array();
								if($player_equipments[0]['total'] < $achievement->quantity){
									echo exp_bar($player_equipments[0]['total'].'/'.$achievement->quantity, $achievement->quantity, 175);
								}
							}
							if($achievement->equipment == 2){
								$player_equipments = Recordset::query("select count(id) as total from player_items WHERE player_id=".$player->id." AND item_id in (select id from items WHERE item_type_id=8) AND rarity='".$achievement->rarity."' AND equipped=1")->result_array();
								if($player_equipments[0]['total'] < $achievement->quantity){
									echo exp_bar($player_equipments[0]['total'].'/'.$achievement->quantity, $achievement->quantity, 175);
								}
							}
							//Barrinhas de Progresso dos Fragmentos
							//Barrinhas de Progresso do Dinheiro
							if($achievement->currency){
								if($player->currency < $achievement->quantity){
									echo exp_bar($player->currency.'/'.$achievement->quantity, $achievement->quantity, 175);
								}
							}
							//Barrinhas de Progresso do Dinheiro
							//Barrinhas de Progresso do Dinheiro
							if($achievement->credits){
								if($user->credits < $achievement->quantity){
									echo exp_bar($user->credits.'/'.$achievement->quantity, $achievement->quantity, 175);
								}
							}
							//Barrinhas de Progresso do Dinheiro
							//Barrinhas de Progresso dos Pets
							if($achievement->pets){
								if($achievement->quantity && !$achievement->item_id && !$achievement->rarity && !$achievement->happiness){
									$total_pets = sizeof($player->your_pets_achievement());
									if($total_pets < $achievement->quantity){
										echo exp_bar($total_pets.'/'.$achievement->quantity, $achievement->quantity, 175);
									}
								}
								if($achievement->quantity && !$achievement->item_id && $achievement->rarity && !$achievement->happiness){
									$total_pets = sizeof($player->your_pets_achievement($achievement->rarity));
									if($total_pets < $achievement->quantity){
										echo exp_bar($total_pets.'/'.$achievement->quantity, $achievement->quantity, 175);
									}
								}
								if($achievement->quantity && !$achievement->item_id && !$achievement->rarity && $achievement->happiness){
									$total_pets = sizeof($player->your_pets_achievement(NULL, $achievement->happiness));
									if($total_pets < $achievement->quantity){
										echo exp_bar($total_pets.'/'.$achievement->quantity, $achievement->quantity, 175);
									}
								}
							}
							//Barrinhas de Progresso dos Pets
							//Barrinhas de Progresso da Missão de Tempo
							if($achievement->time_quests){
								$player_quest = PlayerQuestCounter::find_first("player_id=". $player->id);
								if($player_quest->time_total < $achievement->quantity){
									echo exp_bar($player_quest->time_total.'/'.$achievement->quantity, $achievement->quantity, 175);
								}
							}
							//Barrinhas de Progresso da Missão de Tempo
							//Barrinhas de Progresso da Missão de Combate
							if($achievement->battle_quests){
								$player_quest = PlayerQuestCounter::find_first("player_id=". $player->id);
								if($player_quest->combat_total < $achievement->quantity){
									echo exp_bar($player_quest->combat_total.'/'.$achievement->quantity, $achievement->quantity, 175);
								}
							}
							//Barrinhas de Progresso da Missão de Combate
							//Barrinhas de Progresso da Missão de PVP
							if($achievement->pvp_quests){
								$player_quest = PlayerQuestCounter::find_first("player_id=". $player->id);
								if($player_quest->pvp_total < $achievement->quantity){
									echo exp_bar($player_quest->pvp_total.'/'.$achievement->quantity, $achievement->quantity, 175);
								}
							}
							//Barrinhas de Progresso da Missão de PVP
							//Barrinhas de Progresso da Missão de Daily
							if($achievement->daily_quests){
								$player_quest = PlayerQuestCounter::find_first("player_id=". $player->id);
								if($player_quest->daily_total < $achievement->quantity){
									echo exp_bar($player_quest->daily_total.'/'.$achievement->quantity, $achievement->quantity, 175);
								}
							}
							//Barrinhas de Progresso da Missão de Daily
							//Barrinhas de Progresso da Missão de Daily
							if($achievement->account_quests){
								$player_quest = UserQuestCounter::find_first("user_id=". $player->user_id);
								if($player_quest->daily_total < $achievement->quantity){
									echo exp_bar($player_quest->daily_total.'/'.$achievement->quantity, $achievement->quantity, 175);
								}
							}
							//Barrinhas de Progresso da Missão de Daily
							//Barrinhas de Progresso da Missão de Pet
							if($achievement->pet_quests){
								$player_quest = PlayerQuestCounter::find_first("player_id=". $player->id);
								if($player_quest->pet_total < $achievement->quantity){
									echo exp_bar($player_quest->pet_total.'/'.$achievement->quantity, $achievement->quantity, 175);
								}
							}
							//Barrinhas de Progresso da Missão de Pet
							//Barrinhas de Progresso da Missão de Organização
							if($achievement->weekly_quests){
								$organization_quest = OrganizationQuestCounter::find_first("organization_id=". $player->organization_id);
								if($organization_quest){
									if($organization_quest->daily_total < $achievement->quantity){
										echo exp_bar($organization_quest->daily_total.'/'.$achievement->quantity, $achievement->quantity, 175);
									}
								}else{
										echo exp_bar('0 /'.$achievement->quantity, $achievement->quantity, 175);

								}
							}
							//Barrinhas de Progresso da Missão de Organização
							//Barrinhas de Progresso de Batalha NPC
							if($achievement->battle_npc){
								if($player->wins_npc < $achievement->quantity){
									echo exp_bar($player->wins_npc.'/'.$achievement->quantity, $achievement->quantity, 175);
								}
							}
							//Barrinhas de Progresso de Batalha NPC
							//Barrinhas de Progresso de Batalha PVP
							if($achievement->battle_pvp){
								
								// Só quer saber a quantidade de pvps
								if($achievement->battle_pvp && !$achievement->anime_id && !$achievement->character_id && !$achievement->faction_id){
									if($player->wins_pvp < $achievement->quantity){
										echo exp_bar($player->wins_pvp.'/'.$achievement->quantity, $achievement->quantity, 175);
									}
								// Quer saber a quantidade de pvps com determinada facção	
								}else if($achievement->battle_pvp && !$achievement->anime_id && !$achievement->character_id && $achievement->faction_id){
									$player_achievement_stats = Recordset::query("select sum(quantity) as total from player_achievement_stats WHERE player_id=".$player->id." AND faction_id=".$achievement->faction_id)->result_array();
									if($player_achievement_stats[0]['total'] < $achievement->quantity){
										echo exp_bar(($player_achievement_stats[0]['total'] ? $player_achievement_stats[0]['total'] : 0).'/'.$achievement->quantity, $achievement->quantity, 175);
									}
								// Quer saber a quantidade de pvps com determinada anime	
								}else if($achievement->battle_pvp && $achievement->anime_id && !$achievement->character_id && !$achievement->faction_id){
									$player_achievement_stats = Recordset::query("select sum(quantity) as total from player_achievement_stats WHERE player_id=".$player->id." AND anime_id=".$achievement->anime_id)->result_array();
									if($player_achievement_stats[0]['total'] < $achievement->quantity){
										echo exp_bar(($player_achievement_stats[0]['total'] ? $player_achievement_stats[0]['total'] : 0).'/'.$achievement->quantity, $achievement->quantity, 175);
									}
								// Quer saber a quantidade de pvps com determinada personagem	
								}else if($achievement->battle_pvp && !$achievement->anime_id && $achievement->character_id && !$achievement->faction_id){
									$player_achievement_stats = Recordset::query("select sum(quantity) as total from player_achievement_stats WHERE player_id=".$player->id." AND character_id=".$achievement->character_id)->result_array();
									if($player_achievement_stats[0]['total'] < $achievement->quantity){
										echo exp_bar(($player_achievement_stats[0]['total'] ? $player_achievement_stats[0]['total'] : 0).'/'.$achievement->quantity, $achievement->quantity, 175);
									}
								}
							}
							//Barrinhas de Progresso de Batalha PVP
						?>
					</div>
				</div>	
			</div>
		</div>
		<div class="a-gift">
			<div class="<?php echo $on_off?> requirement-popover" data-source="#tooltip-gift-<?php echo $achievement->id?>" data-title="Recompensa" data-trigger="hover" data-placement="bottom">
				<img src="<?php echo image_url('achievement/gift_'.$on_off.'.png') ?>" />
				<div id="tooltip-gift-<?php echo $achievement->id?>" class="status-popover-container">
					<div class="status-popover-content">
						<?php 
							$rewards = $achievement->achievement_rewards($achievement->id);
						?>
						<?php if($rewards){?>
							<ul>
								<?php if($rewards->exp){?>
									<li><?php echo $rewards->exp ?> <?php echo t('ranked.exp');?></li><br />
								<?php }?>
								<?php if($rewards->exp_user){?>
									<li><?php echo $rewards->exp_user ?> <?php echo t('ranked.exp_account');?></li><br />
								<?php }?>
								<?php if($rewards->currency){?>
									<li><?php echo $rewards->currency ?> <?php echo t('currencies.' . $player->character()->anime_id) ?></li><br />
								<?php }?>	
								<?php if($rewards->credits){?>
									<li><?php echo $rewards->credits ?> <?php echo t('treasure.show.credits')?></li><br />
								<?php }?>	
								<?php if($rewards->item_id){?>
									<li><?php echo $rewards->quantity?>x "<?php echo Item::find($rewards->item_id)->description()->name ?>"</li><br />
								<?php }?>
								<?php if($rewards->character_theme_id){?>
									<li><?php echo t('treasure.show.theme')?> "<?php echo CharacterTheme::find($rewards->character_theme_id)->description()->name ?>"</li><br />
								<?php }?>
								<?php if($rewards->character_id){?>
									<li><?php echo t('treasure.show.character')?> "<?php echo Character::find($rewards->character_id)->description()->name ?>"</li><br />
								<?php }?>
								<?php if($rewards->equipment){?>
									<li><?php echo t('event.e12');?></li><br />
								<?php }?>
								<?php if($rewards->pet){?>
									<li><?php echo t('event.e14');?></li><br />
								<?php }?>
								<?php if($rewards->headline_id){?>
									<li><?php echo t('treasure.show.headline')?> "<?php echo Headline::find($rewards->headline_id)->description()->name ?>"</li><br />
								<?php }?>
							</ul>
						<?php }else{?>
							<span>Conquista sem premiação</span>
						<?php }?>			
					</div>
				</div>	
			</div>
		</div>
		<div class="a-point">
			<div class="<?php echo $on_off?> requirement-popover" data-source="#tooltip-point-<?php echo $achievement->id?>" data-title="Pontos de Conquista" data-trigger="hover" data-placement="bottom">
				<span class="a-point-<?php echo $on_off?>"><?php echo $achievement->points?></span>
				<div id="tooltip-point-<?php echo $achievement->id?>" class="status-popover-container">
					<div class="status-popover-content">
						Ao concluir essa conquista você ganhará <?php echo $achievement->points?> pontos de conquista
					</div>
				</div>	
			</div>
		</div>
	</div>
<?php endforeach ?>	