<?=partial('shared/title', [
	'title' => 'menus.fidelity',
	'place' => 'menus.fidelity'
]);?>
<?php if ($player_tutorial->fidelity) { ?>
<script type="text/javascript">
	$(function () {
		$("#conteudo.with-player").css("z-index", 'initial');
		$(".info").css("z-index", 'initial');
		$("#background-topo2").css("z-index", 'initial');
		
		var tour = new Tour({
		backdrop: true,
		page: 18,
		
		steps: [
		{
			element: ".tutorial-1",
			title: "Colete sua Recompensa!",
			content: "Logue e colete sua recompensa diariamente! Toda meia noite você poderá vir coletar sua próxima recompensa, até 30 vezes por mês. Mas fique atento, a Fidelidade reseta todo dia 1, então não esqueça de pegar seu prêmio.",
			placement: "right"
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
<?=partial('shared/info', [
	'id'		=> 1,
	'title'		=> 'fidelity.title',
	'message'	=> t('fidelity.description')
]);?>
<?php foreach ($fidelities as $fidelity): ?>
	<?php
	$active = FALSE;
	if ($fidelity->day < $playerFidelity->day)
		$active = TRUE;
	elseif ($fidelity->day == $playerFidelity->day && $playerFidelity->reward == 1)
		$active = TRUE;
	?>
	<div class="ability-speciality-box <?=($active ? 'active' : '')?> tutorial-<?=$fidelity->day;?>" style="height: auto;">
		<div class="image">
			<img src="<?=image_url('fidelity/'. $fidelity->day .'.png');?>" />
		</div>
		<div class="name" style="height: auto;">
			<b class="amarelo">
				<?php if ($fidelity->type == 'currency') { ?>
					<?=t('currencies.' . $player->character()->anime_id);?>
				<?php } else { ?>
					<?=t('fidelity.type.' . $fidelity->type);?>
				<?php } ?>
			</b>
		</div>
		<div class="description" style="height: auto; font-size: 16px;">
			<?php
			if ($fidelity->type == 'stars') {
				if (!$userStats->stars || time() >= strtotime($userStats->stars . ' +7 days')){
					echo $fidelity->reward . 'x';
				} else {
					echo "<span class='laranja'>Você já resgatou!</span>";
				}
			} else {
				echo $fidelity->reward . 'x';
			}
			?>
		</div>
		<div class="details" style="height: auto;">
			<?php if($playerFidelity->day == $fidelity->id && $playerFidelity->reward == 0) { ?>
				<a class="reward_fidelity btn btn-primary" data-day="<?=$fidelity->day;?>"><?=t('fidelity.buttons.reward');?></a>
			<?php } elseif ($active) { ?>
				<a class="btn btn-success disabled"><?=t('fidelity.buttons.rewarded');?></a>
			<?php } else { ?>
				<a class="btn btn-danger disabled"><?=t('fidelity.buttons.unavailable');?></a>
			<?php } ?>
		</div>
	</div>
<?php endforeach; ?>