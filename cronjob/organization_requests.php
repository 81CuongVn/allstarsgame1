<?php
require '_config.php';

$organization_requests	= Recordset::query('SELECT player_id FROM organization_requests GROUP BY player_id');
foreach ($organization_requests->result_array() as $organization_request) {
    $players = Recordset::query('SELECT * FROM players WHERE id='. $organization_request['player_id'])->row_array();

    // Verifica se a guild tem 4 missões ativas
    if ($players['organization_id']) {
        Recordset::query('DELETE FROM organization_requests WHERE player_id='.$players['id']);
    }
}

echo '[Organization Requests] Cron executada com sucesso!';