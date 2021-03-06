<?php echo partial('shared/title', array('title' => 'menus.organization_quests_daily', 'place' => 'menus.organization_quests_daily')) ?>
<?php
	echo partial('shared/info', [
		'id'		=> 1,
		'title'		=> 'quests.daily.help_title2',
		'message'	=> t('quests.daily.help_description2')
	]);
?>
<br />
<?php 
	foreach ($quests as $quest):
		if(!$quest->complete){
		
		$player_quest 		= DailyQuest::find('id='.$quest->daily_quest_id,['cache' => true]);
		$personagem 		= Player::find($quest->enemy_id, array('cache' => true));
		$organization		= Organization::find($quest->guild_enemy_id, array('cache' => true));
		$currency 			= DailyQuest::find($quest->daily_quest_id, array('cache' => true));
?>
	<div class="ability-speciality-box" data-id="<?php echo $quest->id ?>" style="height: 270px !important;">
	<div>
		<div class="image">
			<img src="<?php echo image_url('daily/14.png') ?>" />

		</div>
		<div class="name <?php echo $currency->dificuldade?>" style="height: 15px !important;">
			Missão Semanal <?php echo $quest->daily_quest_id ?>
		</div>
		<div class="description" style="height: 60px !important;">
		<?php 
			switch($quest->daily_quest_id){
				case 14:
					$descricao = "Derrote ". ($quest->total > 40 ? 40 : $quest->total) ." de ".$player_quest[0]->total." vezes qualquer oponente PVP da Organização <span class='laranja'>". $organization->name ."</span>";
					break;
				case 20:
					$descricao = "Derrote ". ($quest->total > 80 ? 80 : $quest->total) ." de ".$player_quest[0]->total." vezes qualquer oponente PVP da Organização <span class='laranja'>". $organization->name ."</span>";
					break;
				case 26:
					$descricao = "Derrote ". ($quest->total > 160 ? 160 : $quest->total) ." de ".$player_quest[0]->total." vezes qualquer oponente PVP da Organização <span class='laranja'>". $organization->name ."</span>";
					break;
				case 15:
					$descricao = "Derrote ". ($quest->total > 20 ? 20 : $quest->total) ." de ".$player_quest[0]->total." vezes o personagem <span class='laranja'>". $personagem->name ."</span> da Organização <span class='laranja'>". $organization->name ."</span> em combates PVP";
					break;
				case 21:
					$descricao = "Derrote ". ($quest->total > 40 ? 40 : $quest->total) ." de ".$player_quest[0]->total." vezes o personagem <span class='laranja'>". $personagem->name ."</span> da Organização <span class='laranja'>". $organization->name ."</span> em combates PVP";
					break;
				case 27:
					$descricao = "Derrote ". ($quest->total > 60 ? 60 : $quest->total) ." de ".$player_quest[0]->total." vezes o personagem <span class='laranja'>". $personagem->name ."</span> da Organização <span class='laranja'>". $organization->name ."</span> em combates PVP";
					break;
				case 16:
					$descricao = "Roube ". ($quest->total > 80 ? 80 : $quest->total) ." de ".$player_quest[0]->total." tesouros de qualquer oponente PVP da Organização <span class='laranja'>". $organization->name ."</span>";
					break;
				case 22:
					$descricao = "Roube ". ($quest->total > 160 ? 160 : $quest->total) ." de ".$player_quest[0]->total." tesouros de qualquer oponente PVP da Organização <span class='laranja'>". $organization->name ."</span>";
					break;
				case 28:
					$descricao = "Roube ". ($quest->total > 320 ? 320 : $quest->total) ." de ".$player_quest[0]->total." tesouros de qualquer oponente PVP da Organização <span class='laranja'>". $organization->name ."</span>";
					break;
				case 17:
					$descricao = "Roube ". ($quest->total > 40 ? 40 : $quest->total) ." de ".$player_quest[0]->total." tesouros do personagem <span class='laranja'>". $personagem->name ."</span> da Organização <span class='laranja'>". $organization->name ."</span> em combates PVP";
					break;
				case 23:
					$descricao = "Roube ". ($quest->total > 80 ? 80 : $quest->total) ." de ".$player_quest[0]->total." tesouros do personagem <span class='laranja'>". $personagem->name ."</span> da Organização <span class='laranja'>". $organization->name ."</span> em combates PVP";
					break;
				case 29:
					$descricao = "Roube ". ($quest->total > 160 ? 160 : $quest->total) ." de ".$player_quest[0]->total." tesouros do personagem <span class='laranja'>". $personagem->name ."</span> da Organização <span class='laranja'>". $organization->name ."</span> em combates PVP";
					break;
				case 18:
					$descricao = "Derrote ". ($quest->total > 80 ? 80 : $quest->total) ." de ".$player_quest[0]->total." oponentes PVP de qualquer <span class='laranja'>Organização</span>";
					break;
				case 24:
					$descricao = "Derrote ". ($quest->total > 160 ? 160 : $quest->total) ." de ".$player_quest[0]->total." oponentes PVP de qualquer <span class='laranja'>Organização</span>";
					break;
				case 30:
					$descricao = "Derrote ". ($quest->total > 320 ? 320 : $quest->total) ." de ".$player_quest[0]->total." oponentes PVP de qualquer <span class='laranja'>Organização</span>";
					break;
				break;
				case 19:
					$descricao = "Roube ". ($quest->total > 160 ? 160 : $quest->total) ." de ".$player_quest[0]->total." tesouros de qualquer <span class='laranja'>Organização</span>";
					break;
				case 25:
					$descricao = "Roube ". ($quest->total > 320 ? 320 : $quest->total) ." de ".$player_quest[0]->total." tesouros de qualquer <span class='laranja'>Organização</span>";
					break;
				case 31:
					$descricao = "Roube ". ($quest->total > 640 ? 640 : $quest->total) ." de ".$player_quest[0]->total." tesouros de qualquer <span class='laranja'>Organização</span>";
					break;
				break;
			}
		?>
		<?php echo $descricao?><br />
		</div>
		<div class="details">
			<img src="<?php echo image_url("icons/currency.png" ) ?>" /><span class="amarelo_claro" style="font-size: 16px; margin-left: 5px; top: 2px; position: relative"><?php echo $currency->currency?></span>
		</div>
		<div class="change-mission" style="margin-top: 10px">
			<?php if(!$quest->complete && $player->id == $total_treasure->player_id){?>
				<a data-id="<?php echo $quest->id ?>" data-quest="<?php echo $quest->daily_quest_id ?>" class="btn btn-primary organization_daily_quests_change">
					<?php 
						if($buy_mode_change){
							if($buy_mode_change->weekly == 0){
								echo "Trocar grátis";							
							}elseif($buy_mode_change->weekly > 0 && $buy_mode_change->weekly < 5){
								
								$valor_change = $buy_mode_change->weekly * 500;
								
								echo "Trocar por ".$valor_change .' '. t('currencies.' . $player->character()->anime_id);
					
							}elseif($buy_mode_change->weekly > 4){
								
								if($buy_mode_change->weekly > 4  && $buy_mode_change->weekly < 10){
									$valor_change = 1;
								}elseif($buy_mode_change->weekly > 9  && $buy_mode_change->weekly < 15){
									$valor_change = 2;
								}elseif($buy_mode_change->weekly > 14  && $buy_mode_change->weekly < 20){	
									$valor_change = 3;
								}elseif($buy_mode_change->weekly >= 20){
									$valor_change = 5;
								}
								echo "Trocar por ". $valor_change. " Estrela(s)";
							}
						}else{
							echo "Trocar grátis";
						}
					?>
					
				</a>
			<?php }?>	
		</div>
	</div>
</div>
<?php }?>
<?php endforeach ?>
<?php
	if(sizeof($quests) && $can_accept){
?>	
<div class="clearfix" align="center" style="position:relative; top:10px;">
	<a id="organization_daily_quests_finish" class="btn btn-primary"><?php echo t('quests.daily.finish') ?></a>
</div>					
<?php } ?>
