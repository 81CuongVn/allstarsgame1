<?php echo partial('shared/title', array('title' => 'menus.quests_daily', 'place' => 'menus.quests_daily')) ?>
<?php if(!$player_tutorial->missoes_diarias){?>
<script>
$(function () {
	 $("#conteudo.with-player").css("z-index", 'initial');
	 $(".info").css("z-index", 'initial');
	 $("#background-topo2").css("z-index", 'initial');
	
    var tour = new Tour({
	  backdrop: true,
	  page: 14,
	 
	  steps: [
	  {
		element: ".msg-container",
		title: "Fique mais Forte!",
		content: "Você irá receber diariamente até 4 desafios para serem cumpridos e como recompensa você ganhará Dinheiro!",
		placement: "top"
	  },{
		element: ".msg-container",
		title: "Atenção",
		content: "No dia que você criou seu personagem você não irá ter nenhuma Missão Diária, mas à meia noite você já irá receber suas quatro primeiras missões!",
		placement: "bottom"
	  }
	]});
	//Renicia o Tour
	tour.restart();
	
	// Initialize the tour
	tour.init(true);
	
	// Start the tour
	tour.start(true);
	
});
</script>	
<?php }?>
<?php
	echo partial('shared/info', [
		'id'		=> 1,
		'title'		=> 'quests.daily.help_title',
		'message'	=> t('quests.daily.help_description')
	]);
?>
<br />
<?php 
	foreach ($quests as $quest):
	
		if(!$quest->complete){
		
		$player_quest = DailyQuest::find('id='.$quest->daily_quest_id,['cache' => true]);
		$personagem = Character::find($quest->character_id, array('cache' => true));
		$anime 		= Anime::find($quest->anime_id, array('cache' => true));
		$currency 	= DailyQuest::find($quest->daily_quest_id, array('cache' => true));
?>
	<div class="ability-speciality-box" data-id="<?php echo $quest->id ?>" style="height: 270px !important;">
	<div>
		<div class="image">
			<img src="<?php echo image_url('daily/'.$quest->daily_quest_id.'.png') ?>" />

		</div>
		<div class="name <?php echo $currency->dificuldade?>" style="height: 15px !important;">
			Missão Diária <?php echo $quest->daily_quest_id ?>
		</div>
		<div class="description" style="height: 60px !important;">
		<?php 
			switch($quest->daily_quest_id){
				case 1:
					$descricao = "Derrote ". ($quest->total > 5 ? 5 : $quest->total) ." de ".$player_quest[0]->total." vezes qualquer oponente PVP";
				break;
				case 2:
					$descricao = "Derrote ". ($quest->total > 5 ? 5 : $quest->total) ." de ".$player_quest[0]->total." vezes qualquer oponente NPC";
				break;
				case 3:
					$descricao = "Duele ". ($quest->total > 20 ? 20 : $quest->total) ." de ".$player_quest[0]->total." vezes com oponentes PVP / NPC";
				break;
				case 4:
					$descricao = "Complete ". ($quest->total > 20 ? 20 : $quest->total) ." de 1 missão de Tempo";
				break;
				case 5:
					$descricao = "Complete ". ($quest->total > 20 ? 20 : $quest->total) ." de 1 missão PVP";
				break;
				case 6:
					$descricao = "Derrote ". ($quest->total > 5 ? 5 : $quest->total) ." de ".$player_quest[0]->total." oponentes PVP do anime <span class='laranja'>". $anime->description()->name ."</span>";
				break;
				case 7:
					$descricao = "Duele ". ($quest->total > 20 ? 20 : $quest->total) ." de  ".$player_quest[0]->total." vezes com oponentes PVP / NPC do anime <span class='laranja'>". $anime->description()->name ."</span>";
				break;
				case 8:
					$descricao = "Derrote ". ($quest->total > 5 ? 5 : $quest->total) ." de ".$player_quest[0]->total." oponentes NPC do anime <span class='laranja'>". $anime->description()->name ."</span>";
				break;
				case 9:
					$descricao = "Duele ". ($quest->total > 20 ? 20 : $quest->total) ." de ".$player_quest[0]->total." vezes com oponente PVP / NPC do anime <span class='laranja'>". $anime->description()->name ."</span>";
				break;
				case 10:
					$descricao = "Derrote ". ($quest->total > 5 ? 5 : $quest->total) ." de ".$player_quest[0]->total." vezes o personagem <span class='laranja'>". $personagem->description()->name ."</span> do anime <span class='laranja'>". $anime->description()->name ."</span> em combates PVP";
				break;
				case 11:
					$descricao = "Duele ". ($quest->total > 10 ? 10 : $quest->total) ." de ".$player_quest[0]->total." vezes com o personagem <span class='laranja'>". $personagem->description()->name ."</span> do anime <span class='laranja'>". $anime->description()->name ."</span> em combates PVP / NPC";
				break;
				case 12:
					$descricao = "Derrote ". ($quest->total > 5 ? 5 : $quest->total) ." de ".$player_quest[0]->total." vezes o personagem <span class='laranja'>". $personagem->description()->name ."</span> do anime <span class='laranja'>". $anime->description()->name ."</span> em combates NPC";
				break;
				case 13:
					$descricao = "Duele ". ($quest->total > 10 ? 10 : $quest->total) ." de ".$player_quest[0]->total." vezes com o personagem <span class='laranja'>". $personagem->description()->name ."</span> do anime <span class='laranja'>". $anime->description()->name ."</span> em combates PVP / NPC";
				break;
			}
		?>
		<?php echo $descricao?><br />
		</div>
		<div class="details">
			<img src="<?php echo image_url("icons/currency.png" ) ?>" /><span class="amarelo_claro" style="font-size: 16px; margin-left: 5px; top: 2px; position: relative"><?php echo $currency->currency?></span>
		</div>
		<div class="change-mission" style="margin-top: 10px">
			<?php if(!$quest->complete){?>
				<a data-id="<?php echo $quest->id ?>" data-quest="<?php echo $quest->daily_quest_id ?>" class="btn btn-primary daily_quests_change">
					
					<?php 
						if($buy_mode_change){
							if($buy_mode_change->daily == 0){
								echo "Trocar grátis";							
							}elseif($buy_mode_change->daily > 0 && $buy_mode_change->daily < 5){
								
								$valor_change = $buy_mode_change->daily * 500;
								
								echo "Trocar por ".$valor_change .' '. t('currencies.' . $player->character()->anime_id);
					
							}elseif($buy_mode_change->daily > 4){
								
								if($buy_mode_change->daily > 4  && $buy_mode_change->daily < 10){
									$valor_change = 1;
								}elseif($buy_mode_change->daily > 9  && $buy_mode_change->daily < 15){
									$valor_change = 2;
								}elseif($buy_mode_change->daily > 14  && $buy_mode_change->daily < 20){	
									$valor_change = 3;
								}elseif($buy_mode_change->daily >= 20){
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
	if(sizeof($quests)){
?>	
<div class="clearfix" align="center" style="position:relative; top:10px;">
	<a id="daily_quests_finish" class="btn btn-primary"><?php echo t('quests.daily.finish') ?></a>
</div>					
<?php } ?>
