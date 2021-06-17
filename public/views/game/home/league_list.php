<?php 
	$counter = 1;
	foreach($ranked_rankings as $ranked_ranking):
?>
<?php if($counter==1){?>
	<div class="league-1" data-toggle="tooltip" title="<?php echo make_tooltip($ranked_ranking->name, 70); ?>">
		<div class="league-img-1"><img src="<?php echo image_url('home/'.$ranked_ranking->character_theme()->character_id.'.jpg') ?>" /></div>
	</div>
<?php }?>
<?php if($counter==2){?>
	<div class="league-2" data-toggle="tooltip" title="<?php echo make_tooltip($ranked_ranking->name, 70); ?>">
		<div class="league-img-2"><img src="<?php echo image_url('home/'.$ranked_ranking->character_theme()->character_id.'.jpg') ?>" width="62" /></div>
	</div>
<?php }?>
<?php if($counter==3){?>
	<div class="league-3" data-toggle="tooltip" title="<?php echo make_tooltip($ranked_ranking->name, 70); ?>">
		<div class="league-img-3"><img src="<?php echo image_url('home/'.$ranked_ranking->character_theme()->character_id.'.jpg') ?>" width="46"/></div>
	</div>
<?php }?>
<?php 
	$counter++;
	endforeach 
?>
<script type="text/javascript">
	(function() {
		generate_tooltips();
	})();
</script>