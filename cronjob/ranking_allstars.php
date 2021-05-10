<?php
require '_config.php';

$animes	= Recordset::query('SELECT id FROM animes WHERE active = 1');
foreach ($animes->result_array() as $anime) {
    $position	= 1;
    $players	= Recordset::query("SELECT `id` FROM `ranking_players` WHERE `anime_id` = {$anime['id']} ORDER BY `score` DESC, `level` DESC");
    foreach ($players->result_array() as $player) {
        Recordset::update('ranking_players', [
            'position_anime'	=> $position++
        ], [
            'id' => $player['id']
        ]);
    }
}

echo "[Ranking All-Stars] Cron executada com sucesso!\n";
