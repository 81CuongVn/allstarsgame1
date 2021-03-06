<?php foreach($players as $player): ?>
<?php $name = $player->character()->description()->name;?>
<div class="float-noticias">
	<div class="fn-fotinho"><img src="<?php echo image_url('home/'.$player->character_id.'.jpg') ?>" width="24" alt="<?php echo $name ?>" /></div>
	<div class="fn-info">
		<b><?php echo $name ?></b><br />
		<span><?php echo highamount($player->total)?> <?php echo t('global.jogadores')?></span>
	</div>
</div>	
<?php endforeach ?>