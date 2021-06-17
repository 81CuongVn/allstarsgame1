<?php echo partial('shared/title', array('title' => 'menus.fragments', 'place' => 'menus.fragments')) ?>
<?php if (!$player_tutorial->mercado) { ?>
	<script type="text/javascript">
		$(function () {
			var tour = new Tour({
				backdrop: true,
				page: 6,
				steps: [{
					element: ".msg-container",
					title: "Consiga Fragmentos das Almas",
					content: "No final de cada Batalha você tem a chance de conseguir entre 1 e 10 Fragmentos das Almas. Além disso, cada Equipamento destruído lhe dará uma certa quantidade de Fragmentos.",
					placement: "bottom"
				}]
			});

			tour.restart();
			tour.init(true);
			tour.start(true);
		});
	</script>
<?php } ?>
<?php if (isset($_GET['message']) && $_GET['message']) { ?>
	<div class="alert alert-success" role="alert">
		<?=urldecode($_GET['message']);?>
	</div>
<?php } ?>

<div class="msg-container">
	<div class="msg_top"></div>
	 <div class="msg_repete">
		<div class="msg" style="background:url(<?=image_url('msg/fragmentos.png');?>); background-repeat: no-repeat;">
		</div>
		<div class="msgb" style="position:relative; margin-left: 231px; text-align: left; top: -37px">
			<b><?=t('fragments.title');?></b>
			<div class="content"><?=t('fragments.descriptions');?></div>
		</div>
	</div>
	<div class="msg_bot"></div>
	<div class="msg_bot2"></div>
</div><br />
<?php
$total = $total ? $total->quantity : 0;

$color			= [ 'commom', 'rare', 'epic' ];
$rarities		= [ '0', '1', '2' ];
$prices			= [ '80', '160', '320' ];
$names			= [ 'Equipamento Comum', 'Equipamento Raro', 'Equipamento Épico' ];
$descriptions	= [
	'Transforme 80 Fragmentos das Almas em um Equipamento aleatório da raridade Comum',
	'Transforme 160 Fragmentos das Almas em um Equipamento aleatório da raridade Raro',
	'Transforme 320 Fragmentos das Almas em um Equipamento aleatório da raridade Épica'
];
?>
<?php foreach ($rarities as $rarity) { ?>
	<div class="ability-speciality-box" data-id="<?=$rarity;?>" style="width: 236px !important; height: 290px !important">
		<div class="image">
			<img src="<?php echo image_url('fragments/' . $rarity . '.png'); ?>" />
		</div>
		<div class="name <?php echo $color[$rarity]; ?>" style="height: 30px !important;">
			<?php echo $names[$rarity]; ?>
		</div>
		<div class="description" style="height: 45px !important;">
			<?php echo $descriptions[$rarity]; ?>
		</div>
		<div class="details">
			<img src="<?php echo image_url("icons/fragmento.png" ) ?>" width="26" height="26" />
			<span class="amarelo_claro" style="font-size: 16px; margin-left: 5px; top: 2px; position: relative">
				<?php echo $total ?> / <?php echo $prices[$rarity]?>
			</span>
		</div>
		<div class="button" style="position:relative; top: 15px;">
			<?php if ($total >= $prices[$rarity]) { ?>
				<button type="button" class="fragments_change btn btn-sm btn-primary" data-mode="<?php echo $rarity?>"><?php echo t('fragments.change') ?></button>
			<?php } else { ?>
				<button type="button" class="btn btn-sm btn-danger btn-disabled" disabled><?php echo t('fragments.change') ?></button>
			<?php } ?>
		</div>
	</div>
<?php } ?>
