<?php echo partial('shared/title', array('title' => 'equipments.title', 'place' => 'equipments.title')) ?>
<?php if (FW_ENV != 'dev') { ?>
	<!-- AASG - Equipamentos -->
	<ins class="adsbygoogle"
		style="display:inline-block;width:728px;height:90px"
		data-ad-client="ca-pub-6665062829379662"
		data-ad-slot="7764919424"></ins>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
	</script><br />
<?php } ?>
<?php if (!$player_tutorial->equips) { ?>
	<script type="text/javascript">
		$(function () {
			var tour = new Tour({
				backdrop: true,
				page: 4,
				steps: [{
					element: "#position-container",
					title: "Equipe seu Personagem",
					content: "Existem seis tipos de equipamentos com os mais diversos bônus para seu personagem. Você consegue novos equipamentos em Batalhas e trocando por Fragmentos das Almas.",
					placement: "top"
				}, {
					element: "#position-container",
					title: "Melhore seus Equipamentos",
					content: "Ao clicar em um Equipamento, você pode equipá-lo, vendê-lo ou destruí-lo em troca de Fragmentos. Ao clicar com o botão direito em um Equipamento já equipado, você pode usar Itens Especiais para destruir o equipamento e criar outro na raridade descrita ou aprimorá-lo.",
					placement: "bottom"
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
		'title'		=> 'equipments.title',
		'message'	=> t('equipments.description')
	]);
?>
<br />
<div id="position-container" class="anime-<?php echo $anime->id ?> position-container-<?php echo $anime->id ?>" style="background-image: url(<?php echo image_url('equipments/' . $anime->id . '/background.jpg') ?>)">
	<?php foreach ($positions as $position): ?>
		<?php
			$equipped	= $player->get_equipment_at_slot($position->slot_name);
			$equipments	= $player->get_equipments($position->slot_name, true);
			$is_new		= false;

			foreach ($equipments as $equipment) {
				if ($equipment->attributes()->is_new) {
					$is_new	= true;
					break;
				}
			}

			if(!$equipped) {
				if (in_array($anime->id, [6])) {
					$background	= 'url(' . image_url('equipments/' . $anime->id . '/' . $player->character_id . '/' . $position->slot_name . '.png') . ')';
				} else {
					$background	= 'url(' . image_url('equipments/' . $anime->id . '/' . $position->slot_name . '.png') . ')';
				}
			} else {
				$item		= $equipped->item();
				$background	= 'url(' . image_url($item->image(true)) . ')';
			}
		?>
		<div class="<?php echo $equipped ? "equipped" : "" ?> slot slot-<?php echo $position->slot_name ?>" style="top: <?php echo $position->y ?>px; left: <?php echo $position->x ?>px; background-image: <?php echo $background ?>" data-url="<?php echo make_url('equipments#list_equipments') ?>" data-slot="<?php echo $position->slot_name ?>" data-id="<?php echo $equipped ? $equipped->id : 0 ?>" data-embed="<?php echo $equipped ? $item->embed() : '' ?>">
			<?php if ($is_new): ?>
				<div class="badge">
					<i class="fa fa-exclamation fa-fw"></i>
				</div>
			<?php endif ?>
		</div>
		<?php if ($equipped): ?>
			<?php echo $item->tooltip() ?>
		<?php endif ?>
	<?php endforeach ?>
</div>
