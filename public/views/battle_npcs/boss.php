<?=partial('shared/title', [
	'title' => 'battles.npc.boss_title',
	'place' => 'battles.npc.boss_title'
]);?>
<?php
echo partial('shared/info', [
	'id'		=> 1,
	'title'		=> 'battles.npcs.boss_title',
	'message'	=> t('battles.npcs.boss_description')
]);
?><br />