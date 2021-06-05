<?php
require '_config.php';

$guild_requests	= Recordset::query('SELECT player_id FROM guild_requests GROUP BY player_id');
foreach ($guild_requests->result_array() as $guild_request) {
    $players = Recordset::query('SELECT * FROM players WHERE id='. $guild_request['player_id'])->row_array();

    // Verifica se a guild tem 4 missões ativas
    if ($players['guild_id']) {
        Recordset::query('DELETE FROM guild_requests WHERE player_id='.$players['id']);
    }
}

echo "[Guild Requests] Cron executada com sucesso!\n";
