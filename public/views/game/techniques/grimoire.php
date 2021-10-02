<?php echo partial('shared/title', array('title' => 'menus.grimoire', 'place' => 'menus.grimoire')) ?>
<?php if (FW_ENV != 'dev') { ?>
	<!-- AASG - Techniques -->
	<ins class="adsbygoogle"
		style="display:inline-block;width:728px;height:90px"
		data-ad-client="ca-pub-6665062829379662"
		data-ad-slot="7857252910"></ins>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
	</script><br />
<?php } ?>
<?php if (!$player_tutorial->escola) { ?>
	<script type="text/javascript">
		$(function () {
			var tour = new Tour({
				backdrop: true,
				page: 5,
				steps: [{
					element: ".tutorial-grimoire",
					title: "Encontrando Páginas Perdidas",
					content: "Aprenda novos Golpes adquirindo todos as Páginas Perdidas do golpe em questão. Você tem chance de encontrar Páginas no final de cada Batalha realizada.",
					placement: "top"
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
		'title'		=> 'grimoire.title',
		'message'	=> t('grimoire.description')
	]);
?>
<br />
<?php $counter = 0; ?>
<?php foreach($items as $item): ?>
	<div class="<?=($counter == 0 ? 'tutorial-grimoire' : '');?> ability-speciality-box " style="width: 235px !important; height: 290px !important; padding-bottom: 40px">
		<div class="image">
			<img src="<?php echo image_url($item->image(true)) ?>" class="technique-popover item-image" data-source="#technique-content-<?php echo $item->id ?>" data-title="<?php echo $item->description()->name ?>" data-trigger="hover" data-placement="bottom" />
			<div class="technique-container" id="technique-content-<?php echo $item->id ?>">
				<?php echo $item->technique_tooltip() ?>
			</div>
		</div>
		<div class="name" style="height: 40px !important;">
			<span class="amarelo"><?=$item->description()->name;?></span>
		</div>
		<div class="details" style="height: 95px">
			<?php foreach($player->pages($item->id) as $page): ?>
				<?php
				$player_pages = $player->player_pages($page->id);
				$class = "";
				if (!$player_pages){
					$class = "style='opacity:.3'";
				}
				?>
				<div data-toggle="tooltip" data-title="<?php echo make_tooltip($page->description()->name); ?>" data-placement="bottom" style="display:inline-block; text-align:center">
					<img src="<?php echo image_url('grimoire/'.$page->description()->image)?>" width="40" <?php echo $class?> />
				</div>
			<?php endforeach ?>
		</div>
		<div class="button" style="position:relative; top: 15px;">
			<?php  $player_pages_ok = $player->player_pages_ok($item->id); ?>
			<?php if ($player_pages_ok) { ?>
				<?php if ($player->has_item($item->id)) { ?>
					<button type="button" class="btn btn-sm btn-success btn-disabled" disabled><?php echo t('grimoire.desbloqueado') ?></a>
				<?php } else { ?>
					<a href="javascript:;" class="btn btn-sm btn-primary player-item-finish" data-id="<?php echo $item->id ?>"><?php echo t('grimoire.desbloquear') ?></a>
				<?php } ?>
			<?php } else { ?>
				<button type="button" class="btn btn-sm btn-danger btn-disabled" disabled><?php echo t('grimoire.desbloquear') ?></a>
			<?php } ?>
		</div>
	</div>
<?php ++$counter; endforeach; ?>
