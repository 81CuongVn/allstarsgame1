<?php
require '_config.php';

$wanteds = Recordset::query('SELECT * FROM player_wanteds WHERE death = 0');
foreach ($wanteds->result_array() as $wanted) {
    Recordset::update('player_wanteds', [
        'type_death'	=> rand(1, 10)
    ], [
        'id'	=> $wanted['id']
    ]);
}

echo "[Wanteds] Cron executada com sucesso!\n";