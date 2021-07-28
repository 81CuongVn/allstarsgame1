<?php echo partial('shared/title', array('title' => 'menus.guild_quests_daily', 'place' => 'menus.guild_quests_daily')) ?>
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

		$player_quest 	= DailyQuest::find('id='.$quest->daily_quest_id,['cache' => true]);
		$personagem 	= Player::find($quest->enemy_id, array('cache' => true));
		$guild			= Guild::find($quest->guild_enemy_id, array('cache' => true));
		$currency 		= DailyQuest::find($quest->daily_quest_id, array('cache' => true));
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
				case 20:
				case 26:
					$descricao = "Derrote ". ($quest->total > $player_quest[0]->total ? $player_quest[0]->total : $quest->total) ." de ".$player_quest[0]->total." vezes qualquer oponente PvP da Organização <span class='laranja'>". $guild->name ."</span>";
					break;
				case 15:
				case 21:
				case 52:
					$descricao = "Derrote ". ($quest->total > $player_quest[0]->total ? $player_quest[0]->total : $quest->total) ." de ".$player_quest[0]->total." vezes o jogador <span class='laranja'>". $personagem->name ."</span> da Organização <span class='laranja'>". $guild->name ."</span> em combates PvP";
					break;
				case 16:
				case 22:
				case 28:
					$descricao = "Roube ". ($quest->total > $player_quest[0]->total ? $player_quest[0]->total : $quest->total) ." de ".$player_quest[0]->total." tesouros de qualquer oponente PvP da Organização <span class='laranja'>". $guild->name ."</span>";
					break;
				case 17:
				case 23:
				case 29:
					$descricao = "Roube ". ($quest->total > $player_quest[0]->total ? $player_quest[0]->total : $quest->total) ." de ".$player_quest[0]->total." tesouros do jogador <span class='laranja'>". $personagem->name ."</span> da Organização <span class='laranja'>". $guild->name ."</span> em combates PvP";
					break;
				case 18:
				case 24:
				case 30:
					$descricao = "Derrote ". ($quest->total > $player_quest[0]->total ? $player_quest[0]->total : $quest->total) ." de ".$player_quest[0]->total." oponentes PvP de qualquer <span class='laranja'>Organização</span>";
					break;
				case 19:
				case 25:
				case 31:
					$descricao = "Roube ". ($quest->total > $player_quest[0]->total ? $player_quest[0]->total : $quest->total) ." de ".$player_quest[0]->total." tesouros de qualquer <span class='laranja'>Organização</span>";
					break;
				default:
					$descricao = '??? (' . $quest->id . ')';
			}
		?>
		<?php echo $descricao?><br />
		</div>
		<div class="details">
			<img src="<?php echo image_url("icons/currency.png" ) ?>" /><span class="amarelo_claro" style="font-size: 16px; margin-left: 5px; top: 2px; position: relative"><?php echo $currency->currency?></span>
		</div>
		<div class="change-mission" style="margin-top: 10px">
			<?php if(!$quest->complete && $player->id == $total_treasure->player_id){?>
				<a data-id="<?php echo $quest->id ?>" data-quest="<?php echo $quest->daily_quest_id ?>" class="btn btn-sm btn-primary guild_daily_quests_change">
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
	<a id="guild_daily_quests_finish" class="btn btn-sm btn-primary"><?php echo t('quests.daily.finish') ?></a>
</div>
<?php } ?>
