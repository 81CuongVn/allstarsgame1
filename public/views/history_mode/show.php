<?php echo partial('shared/title', array('title' => 'history_mode.index.title', 'place' => 'history_mode.index.title')) ?>
<?php foreach ($subgroups as $subgroup): ?>
	<?php echo partial('subgroup', ['player' => $player, 'subgroup' => $subgroup]) ?>	
<?php endforeach ?>