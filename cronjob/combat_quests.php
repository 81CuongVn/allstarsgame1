<?php
require('_config.php');

if (date('d') == 7 || date('d') == 14 || date('d') == 21 || date('d') == 28) {
	$questWeakly					= CombatQuest::find_first("period = 'semanal' order by rand()");
	$combatWeakly					= new PlayerCombatQuest();
	$combatWeakly->period			= $questWeakly->period;
	$combatWeakly->combat_quest_id	= $questWeakly->id;
	$combatWeakly->save();
} elseif (date('d') == 1) {
	$questMonthly					= CombatQuest::find_first("period = 'mensal' order by rand()");
	$combatMonthly					= new PlayerCombatQuest();
	$combatMonthly->period			= $questMonthly->period;
	$combatMonthly->combat_quest_id	= $questMonthly->id;
	$combatMonthly->save();
}

$questDaily							= CombatQuest::find_first("period = 'diario' order by rand()");
$combatDaily						= new PlayerCombatQuest();
$combatDaily->period				= $questDaily->period;
$combatDaily->combat_quest_id		= $questDaily->id;
$combatDaily->save();

echo '[Combat Quests] Cron executada com sucesso!';