<?php echo partial('shared/title', array('title' => 'menus.wanted', 'place' => 'menus.wanted')) ?>
<?php if (!$player_tutorial->bijuus) { ?>
	<script type="text/javascript">
		$(function () {
			var tour = new Tour({
				backdrop: true,
				page: 20,
				steps: [{
					element: ".msg-container",
					title: "Cace e seja Caçado",
					content: "Ao vencer 10 Batalhas PVP seguidas, você se torna um Jogador Procurado, com um preço por sua cabeça! Além disso, você poderá derrotar outros jogadores do jeito especificado e coletar a recompensa pela cabeça deles!",
					placement: "bottom"
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
	'title'		=> 'wanted.title',
	'message'	=> t('wanted.description')
]);?>
<form id="wanteds-filter-form" method="post">
	<input type="hidden" name="page" value="<?=$page;?>" />
	<?php 
	if ($wanteds) {
		foreach ($wanteds as $wanted) {
			$player_wanted = PlayerWanted::find_first("player_id=" . $wanted->id ." AND death = 0");
	?>
		<div class="bg-wanteds" data-id="<?=$player_wanted->player_id;?>" data-embed="<?=$player_wanted->embed();?>">
			<div class="wanteds-foto">
				<img src="<?=image_url("wanted/" . $wanted->character_id . ".jpg" );?>" width="139" height="107" />
			</div>
			<div class="wanteds-info">
				<b style="font-size:16px"><?=$wanted->name;?></b><br />
				<span><?t('wanted.'.$player_wanted->type_death);?></span><br />
				<?php $reward = $wanted->won_last_battle > 100 ? 100 * 250 : $wanted->won_last_battle * 250; ?>
				<span style="font-size:14px"><?=highamount($reward);?> <?=t('currencies.' . $player->character()->anime_id);?></span>
			</div>
		</div>
	<?php } ?>
	<div style="clear:both"></div>		
	<?=partial('shared/paginator', [
		'pages'		=> $pages,
		'current'	=> $page + 1
	]);?>
	<?php
	} else {
		echo "<div align='center'>
			<b class='laranja' style='font-size:14px;'>" . t('wanted.sem_nenhum') . "</b>
		</div>";
	}
	?>	
</form>