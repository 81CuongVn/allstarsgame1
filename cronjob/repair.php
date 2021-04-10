<?php
require '_config.php';

$users = User::find('1 = 1');
foreach ($users as $user) {
    $objectives  = sizeof(UserObjective::find('user_id = ' . $user->id));
    if ($objectives < 1) {
        $user->objectives = 0;
        $user->save();
    }
}

echo "[Repair] Cron executada com sucesso!\n";