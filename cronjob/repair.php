<?php
require '_config.php';

PlayerCharacterAbility::truncate();
PlayerCharacterSpeciality::truncate();

$players	= Player::all();
foreach ($players as $player) {
	//Adiciona as Habilidades do jogador
	$character_abilities = CharacterAbility::find("character_id=" . $player->character_id);
	foreach ($character_abilities as $character_ability){
		$player_character_ability = new PlayerCharacterAbility();
		$player_character_ability->player_id = $player->id;
		$player_character_ability->character_ability_id = $character_ability->id;
		$player_character_ability->character_id = $player->character_id;
		$player_character_ability->item_effect_ids = $character_ability->item_effect_ids;
		$player_character_ability->effect_chances = $character_ability->effect_chances;
		$player_character_ability->effect_duration = $character_ability->effect_duration;
		$player_character_ability->consume_mana = $character_ability->consume_mana;
		$player_character_ability->cooldown = $character_ability->cooldown;
		$player_character_ability->is_initial = $character_ability->is_initial;
		$player_character_ability->save();

	}
	//Adiciona as Especialidades do jogador
	$character_specialities = CharacterSpeciality::find("character_id=" . $player->character_id);
	foreach ($character_specialities as $character_speciality){
		$player_character_speciality = new PlayerCharacterSpeciality();
		$player_character_speciality->player_id = $player->id;
		$player_character_speciality->character_speciality_id = $character_speciality->id;
		$player_character_speciality->character_id = $player->character_id;
		$player_character_speciality->item_effect_ids = $character_speciality->item_effect_ids;
		$player_character_speciality->effect_chances = $character_speciality->effect_chances;
		$player_character_speciality->effect_duration = $character_speciality->effect_duration;
		$player_character_speciality->consume_mana = $character_speciality->consume_mana;
		$player_character_speciality->cooldown = $character_speciality->cooldown;
		$player_character_speciality->is_initial = $character_speciality->is_initial;
		$player_character_speciality->save();
	}

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
