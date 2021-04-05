<?php echo partial('shared/title_battle', array('title' => 'battles.pvp.fight.title', 'place' => 'battles.pvp.fight.title')) ?>
<?php
	echo partial('shared/fight_panel', [
		'player'				=> $player,
		'enemy'					=> $enemy,
		'techniques'			=> $techniques,
		'battle'				=> $battle,
		'target_url'			=> $target_url,
		'log'					=> $log,
		'stats'					=> $stats,
		'player_wanted'			=> $player_wanted,
		'enemy_wanted'			=> $enemy_wanted,
		'is_watch'				=> FALSE
	]);
?>