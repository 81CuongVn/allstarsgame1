<?php foreach($tops as $top): ?>
<?php $name	= $top->character_theme()->character()->description()->name ?>
<div class="float-noticias">
	<div class="fn-fotinho"><img src="<?php echo image_url('home/'.$top->character_theme()->character_id.'.jpg') ?>" width="24" alt="<?php echo $name ?>" /></div>
	<div class="fn-info">
		<b><?php echo $top->name ?></b><br />
		<span>Nível <?php echo $top->level?> / Pontos: <?php echo highamount($top->score)?></span>
	</div>
</div>	
<?php endforeach ?>