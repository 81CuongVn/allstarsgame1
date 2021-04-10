<?php
require '_config.php';

$users      = User::find('active = 1 and banned = 0 and objectives = 0');
foreach ($users as $user) {
    $objectives = Achievement::find("type = 'objectives'", [
        'reorder'	=> 'RAND()',
        'limit'		=> 10
    ]);
    foreach ($objectives as $objective) {
        $insert = new UserObjective();
        $insert->user_id        = $user->id;
        $insert->objective_id   = $objective->id;
    }

    $user->objectives   = 1;
    $user->save();
}

echo "[Objectives] Cron executada com sucesso!\n";