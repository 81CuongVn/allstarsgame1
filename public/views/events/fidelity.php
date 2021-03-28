<?php echo partial('shared/title', array('title' => 'menus.fidelity', 'place' => 'menus.fidelity')) ?>
<?php if(!$player_tutorial->fidelity) { ?>
	<script type="text/javascript">
		$(function () {
			var tour = new Tour({
				backdrop: true,
				page: 18,
				steps: [{
					element: ".tutorial-0",
					title: "Colete sua Recompensa!",
					content: "Logue e colete sua recompensa diariamente! Toda meia noite você poderá vir coletar sua próxima recompensa, até 30 vezes por mês. Mas fique atento, a Fidelidade reseta todo dia 1, então não esqueça de pegar seu prêmio.",
					placement: "right"
				}]
			});

			tour.restart();
			tour.init(true);
			tour.start(true);
		});
	</script>
<?php } ?>
<?php
	echo partial('shared/info', [
		'id'		=> 1,
		'title'		=> 'fidelity.title',
		'message'	=> t('fidelity.description')
	]);
?><br />
<?php
$days = [];
for ($i = 0; $i < 30; ++$i) {
	$days[]	= $i;
}

$names	= [
	t('fidelity.days.1') .' '. t('currencies.' . $player->character()->anime_id),
	t('fidelity.days.2'),
	t('fidelity.days.3'),
	t('fidelity.days.4'),
	t('fidelity.days.5') .' '. t('currencies.' . $player->character()->anime_id),
	t('fidelity.days.6'),
	t('fidelity.days.7'),
	t('fidelity.days.8'),
	t('fidelity.days.9'),
	t('fidelity.days.10').' '. t('currencies.' . $player->character()->anime_id),
	t('fidelity.days.11'),
	t('fidelity.days.12'),
	t('fidelity.days.13'),
	t('fidelity.days.14'),
	t('fidelity.days.15').' '. t('currencies.' . $player->character()->anime_id),
	t('fidelity.days.16'),
	t('fidelity.days.17'),
	t('fidelity.days.18'),
	t('fidelity.days.19'),
	t('fidelity.days.20').' '. t('currencies.' . $player->character()->anime_id),
	t('fidelity.days.21'),
	t('fidelity.days.22'),
	t('fidelity.days.23'),
	t('fidelity.days.24'),
	t('fidelity.days.25') .' '. t('currencies.' . $player->character()->anime_id),
	t('fidelity.days.26'),
	t('fidelity.days.27'),
	t('fidelity.days.28'),
	t('fidelity.days.29'),
	t('fidelity.days.30')
];
?>
<?php foreach ($days as $day) { ?>
<?php 
	if ($player_fidelity->day == 1 && $day+1 == 1 && $player_fidelity->reward == 1) {
		$active = 'active';
	} elseif ($player_fidelity->day > 1 && $day+1 < $player_fidelity->day) {
		$active = 'active';
	} elseif ($player_fidelity->day == $day+1 && $player_fidelity->reward == 1) {
		$active = 'active';
	} else {
		$active = '';	
	}
?>
<div class="ability-speciality-box <?php echo $active?> tutorial-<?php echo $day?>" style="width: 175px !important; height: 250px !important">
	<div>
		<div class="image">
			<img src="<?php echo image_url('fidelity/'. ($day+1) .'.png') ?>" />
		</div>
		<div class="name">
			<?php echo t('fidelity.reward_title', [
				'days' => $day + 1
			]); ?>
		</div>
		<div class="description" style="height: 40px !important;">
			<?php 
				if($user_stats->credits){
					if(strtotime(date('Y-m-d H:i:s')) >= strtotime($user_stats->credits . "+29 days") || $day + 1 != 30){
						echo $names[$day];
					}else{	
						echo "<span class='laranja'>Apenas uma vez por mês!</span>";
					}
				}else{
					echo $names[$day];
				}
			?>
		</div>
		<div class="details"></div>
		<div class="button" style="position:relative;">
			<?php if ($player_fidelity->day == $day+1 && $player_fidelity->reward==0 && !$active) { ?>
				<a class="reward_fidelity btn btn-sm btn-primary" data-day="<?php echo $day+1?>"><?php echo t('fidelity.buttons.available');?></a>
			<?php } elseif ($active) { ?>
				<button class="btn btn-sm btn-success btn-disabled" disabled><?php echo t('fidelity.buttons.rewarded');?></button>
			<?php } else { ?>
				<button class="btn btn-sm btn-danger btn-disabled" disabled><?php echo t('fidelity.buttons.unavailable');?></button>
			<?php } ?>
		</div>
	</div>
</div>
<?php } ?>