<?php echo partial('shared/title', array('title' => 'menus.fidelity', 'place' => 'menus.fidelity')) ?>
<?php if (FW_ENV != 'dev') { ?>
	<!-- AASG - Eventos -->
	<ins class="adsbygoogle"
		style="display:inline-block;width:728px;height:90px"
		data-ad-client="ca-pub-6665062829379662"
		data-ad-slot="7809792082"></ins>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
	</script><br />
<?php } ?>
<?php if (!$player_tutorial->fidelity) { ?>
	<script type="text/javascript">
		$(function () {
			var tour = new Tour({
				backdrop: true,
				page: 18,
				steps: [{
					element: ".tutorial-0",
					title: "Colete sua Recompensa!",
					content: "Logue e colete sua recompensa diariamente! Toda dia à meia noite você pode coletar uma nova recompensa, até 8 vezes por semana. Mas fique atento, caso você não faça a coleta do dia, sua sequência irá reiniciar e voltar para o dia 1.",
					placement: "right"
				}]
			});

			tour.restart();
			tour.init(true);
			tour.start(true);
		});
	</script>
<?php } ?>
<?=partial('shared/info', [
	'id'		=> 1,
	'title'		=> 'fidelity.title',
	'message'	=> t('fidelity.description')
]);?><br />
<?php
$days = [];
for ($i = 0; $i < 8; ++$i) {
	$days[]	= $i;
}

$names	= [
	t('fidelity.days.1') . ' ' . t('currencies.' . $player->character()->anime_id),
	t('fidelity.days.2'),
	t('fidelity.days.3'),
	t('fidelity.days.4'),
	t('fidelity.days.5'),
	t('fidelity.days.6'),
	t('fidelity.days.7'),
	t('fidelity.days.8')
];
?>
<?php foreach ($days as $day) { ?>
	<?php
	if ($player_fidelity->day == 1 && ($day + 1) == 1 && $player_fidelity->reward == 1) {
		$active = 'active';
	} elseif ($player_fidelity->day > 1 && ($day + 1) < $player_fidelity->day) {
		$active = 'active';
	} elseif ($player_fidelity->day == ($day + 1) && $player_fidelity->reward == 1) {
		$active = 'active';
	} else {
		$active = '';
	}
	?>
	<div class="ability-speciality-box <?=$active;?> tutorial-<?=$day;?>" style="width: 175px !important; height: 250px !important">
		<div>
			<div class="image">
				<img src="<?=image_url('fidelity/'. ($day + 1) .'.png');?>" />
			</div>
			<div class="name">
				<?=t('fidelity.reward_title', [
					'days' => $day + 1
				]);?>
			</div>
			<div class="description" style="height: 40px !important;">
				<?php
				if ($user_stats->credits) {
					if (strtotime(date('Y-m-d H:i:s')) >= strtotime($user_stats->credits . "+7 days") || $day + 1 != 8) {
						echo $names[$day];
					} else {
						// echo "<span class='laranja'>Apenas uma vez por mês!</span>";
						echo "<span class='laranja'>Já ganhou em outro personagem desta conta</span>";
					}
				} else {
					echo $names[$day];
				}
				?>
			</div>
			<div class="details"></div>
			<div class="button" style="position:relative;">
				<?php if ($player_fidelity->day == $day + 1 && $player_fidelity->reward == 0 && !$active) { ?>
					<a class="reward_fidelity btn btn-sm btn-primary" data-day="<?=($day + 1);?>"><?=t('fidelity.buttons.available');?></a>
				<?php } elseif ($active) { ?>
					<button class="btn btn-sm btn-success btn-disabled" disabled><?=t('fidelity.buttons.rewarded');?></button>
				<?php } else { ?>
					<button class="btn btn-sm btn-danger btn-disabled" disabled><?=t('fidelity.buttons.unavailable');?></button>
				<?php } ?>
			</div>
		</div>
	</div>
<?php } ?>
