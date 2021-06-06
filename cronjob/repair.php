<?php
require '_config.php';

$players	= Player::all();
foreach ($players as $player) {
	$player->character_ability_id		= CharacterAbility::find_first('character_id=' . $player->character_id . ' AND is_initial=1', ['cache' => true])->id;
	$player->character_speciality_id	= CharacterSpeciality::find_first('character_id=' . $player->character_id . ' AND is_initial=1', ['cache' => true])->id;
	$player->save();
}

// $users = User::find('1 = 1');
// foreach ($users as $user) {
//     $objectives  = sizeof(UserObjective::find('user_id = ' . $user->id));
//     if ($objectives < 1) {
//         $user->objectives = 0;
//         $user->save();
//     }
// }

echo "[Repair] Cron executada com sucesso!\n";
