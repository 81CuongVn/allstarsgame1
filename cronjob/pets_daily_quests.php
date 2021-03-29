<?php
require '_config.php';

$players = Recordset::query('SELECT id FROM players WHERE removed = 0 AND level > 1');
foreach ($players->result_array() as $player) {
    $daily_quests = Recordset::query('SELECT * from pet_quests WHERE id NOT IN (select pet_quest_id from player_pet_quests where player_id = '.$player['id'].' and completed = 0) ORDER BY RAND() LIMIT 10');
    foreach ($daily_quests->result_array() as $daily_quest) {
        $total_pet_daily_quests		= Recordset::query('SELECT * FROM player_pet_quests WHERE completed = 0 AND player_id='. $player['id'])->num_rows;

        // Verifica se a guild tem 10 missões ativas
        if ($total_pet_daily_quests >= 10) {
            break;
        }
        Recordset::insert('player_pet_quests', [
            'player_id'				=> $player['id'],
            'pet_quest_id'			=> $daily_quest['id']
        ]);
    }
}
echo "[Pet Daily Quests] Cron executada com sucesso!\n";