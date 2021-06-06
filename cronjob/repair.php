<?php
require '_config.php';

PlayerCharacterAbility::truncate();
PlayerCharacterSpeciality::truncate();

$players	= Player::all([
	'skip_after_assign'	=> true
]);
foreach ($players as $player) {
	// Adiciona as Habilidades do jogador
	$abilities = CharacterAbility::find("character_id = " . $player->character_id);
	foreach ($abilities as $abilitiy) {
		$player_ability = new PlayerCharacterAbility();
		$player_ability->player_id				= $player->id;
		$player_ability->character_ability_id	= $abilitiy->id;
		$player_ability->character_id			= $player->character_id;
		$player_ability->item_effect_ids		= $abilitiy->item_effect_ids;
		$player_ability->effect_chances			= $abilitiy->effect_chances;
		$player_ability->effect_duration		= $abilitiy->effect_duration;
		$player_ability->consume_mana			= $abilitiy->consume_mana;
		$player_ability->cooldown				= $abilitiy->cooldown;
		$player_ability->is_initial				= $abilitiy->is_initial;
		$player_ability->save();

	}

	// Adiciona as Especialidades do jogador
	$specialities = CharacterSpeciality::find("character_id = " . $player->character_id);
	foreach ($specialities as $speciality) {
		$player_speciality							= new PlayerCharacterSpeciality();
		$player_speciality->player_id				= $player->id;
		$player_speciality->character_speciality_id	= $speciality->id;
		$player_speciality->character_id			= $player->character_id;
		$player_speciality->item_effect_ids			= $speciality->item_effect_ids;
		$player_speciality->effect_chances			= $speciality->effect_chances;
		$player_speciality->effect_duration			= $speciality->effect_duration;
		$player_speciality->consume_mana			= $speciality->consume_mana;
		$player_speciality->cooldown				= $speciality->cooldown;
		$player_speciality->is_initial				= $speciality->is_initial;
		$player_speciality->save();
	}

	$player->character_ability_id		= CharacterAbility::find_first('character_id=' . $player->character_id . ' AND is_initial = 1', ['cache' => true])->id;
	$player->character_speciality_id	= CharacterSpeciality::find_first('character_id=' . $player->character_id . ' AND is_initial = 1', ['cache' => true])->id;
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
