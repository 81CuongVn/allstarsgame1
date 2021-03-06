<?php
require '_config.php';

$players = Recordset::query('SELECT * FROM players');
foreach ($players->result_array() as $player) {
    $character_abilities = Recordset::query('SELECT * FROM character_abilities WHERE character_id=' . $player['character_id']);
    foreach ($character_abilities->result_array() as $character_ability) {
        Recordset::insert('player_character_abilities', [
            'player_id'					=> $player['id'],
            'character_ability_id'      => $character_ability['id'],
            'character_id'				=> $character_ability['character_id'],
            'item_effect_ids'			=> $character_ability['item_effect_ids'],
            'effect_chances'			=> $character_ability['effect_chances'],
            'effect_duration'			=> $character_ability['effect_duration'],
            'consume_mana'				=> $character_ability['consume_mana'],
            'cooldown'					=> $character_ability['cooldown'],
            'is_initial'				=> $character_ability['is_initial']
        ]);
    }

}

