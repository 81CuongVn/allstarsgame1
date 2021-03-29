<?php
require '_config.php';

$players = Recordset::query('SELECT * FROM players WHERE removed = 0');
foreach ($players->result_array() as $player) {
    $animes         = 0;
    $personagens    = 0;

    // Verifica se o jogador tem 4 missões ativas
    $total_daily_quests = Recordset::query('SELECT * FROM player_daily_quests WHERE complete = 0 AND player_id='. $player['id'])->num_rows;
    if ($total_daily_quests < 4) {
        $daily_quests = Recordset::query('SELECT * FROM daily_quests WHERE of = "player" ORDER BY RAND() LIMIT 1')->row_array();
        if ($daily_quests['anime'] && !$daily_quests['personagem']) {
            $animes         = Recordset::query('SELECT id FROM animes WHERE active = 1 ORDER BY RAND() LIMIT 1')->row_array();
        } elseif ($daily_quests['anime'] && $daily_quests['personagem']) {
            $animes			= Recordset::query('SELECT id FROM animes WHERE active = 1 ORDER BY RAND() LIMIT 1')->row_array();
            $personagens	= Recordset::query('SELECT id FROM characters WHERE active = 1 AND anime_id = '. $animes['id'] .' ORDER BY RAND() LIMIT 1')->row_array();
        }

        Recordset::insert('player_daily_quests', [
            'player_id'			=> $player['id'],
            'daily_quest_id'	=> $daily_quests['id'],
            'type'				=> $daily_quests['type'],
            'anime_id'			=> ($animes['id']) ? $animes['id'] : 0 ,
            'character_id'		=> ($personagens['id']) ? $personagens['id'] : 0
        ]);
    }
}
echo "[Daily Quests] Cron executada com sucesso!\n";