<?php foreach($players as $player): ?>
<?php if ($type == "players"){?>
	<?php $name	= $player->character_theme()->character()->description()->name ?>
	<div class="float-noticias">
		<div class="fn-fotinho"><img src="<?php echo image_url('home/'.$player->character_theme()->character_id.'.jpg') ?>" width="24" alt="<?php echo $name ?>" /></div>
		<div class="fn-info">
			<b><?php echo $player->name ?></b><br />
			<span>Nível <?php echo $player->level?> | Pontos: <?php echo highamount($player->score);?></span>
		</div>
	</div>
<?php } elseif ($type == "achievements") { ?>
    <?php $name	= $player->character_theme()->character()->description()->name ?>
	<div class="float-noticias">
		<div class="fn-fotinho"><img src="<?php echo image_url('home/'.$player->character_theme()->character_id.'.jpg') ?>" width="24" alt="<?php echo $name ?>" /></div>
		<div class="fn-info">
			<b><?php echo $player->name ?></b><br />
			<span>Nível <?php echo $player->level?> | Pontos: <?php echo highamount($player->score);?></span>
		</div>
	</div>
<?php } elseif ($type == "guilds") { ?>
    <?php $name	= $player->character_theme()->character()->description()->name ?>
	<div class="float-noticias">
		<div class="fn-fotinho"><img src="<?php echo image_url('home/'.$player->character_theme()->character_id.'.jpg') ?>" width="24" alt="<?php echo $name ?>" /></div>
		<div class="fn-info">
			<b><?php echo $player->name ?></b><br />
			<span>Pontos: <?php echo highamount($player->score);?></span>
		</div>
	</div>
<?php } elseif ($type == "accounts" ) { ?>
	<div class="float-noticias">
		<div class="fn-info">
			<b><?php echo str_limit($player->name, 23) ?></b><br />
			<span>Nível <?php echo $player->level?> | Pontos: <?php echo highamount($player->score);?></span>
		</div>
	</div>
<?php } ?>
<?php endforeach ?>
