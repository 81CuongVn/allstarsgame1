<?php
require '_config.php';

$users = Recordset::query("SELECT * FROM `users` WHERE `active` = 1 AND `removed` = 0 AND `objectives` = 0");
foreach ($users->result_array() as $user) {
    $objectives = Recordset::query("SELECT * FROM `achievements` WHERE `type` = 'objectives' ORDER BY RAND() LIMIT 10");
    foreach ($objectives->result_array() as $objective) {
        Recordset::insert('user_objectives', [
            'user_id'		=> $user['id'],
            'objective_id'	=> $objective['id']
        ]);
    }
    Recordset::update('users', [
        'objectives'        => 1
    ], [
        'id'                => $user['id']
    ]);
}

echo "[Objectives] Cron executada com sucesso!\n";