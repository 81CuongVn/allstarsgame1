<?php
require '_config.php';

$users = User::find('objectives = 1');
foreach ($users as $user) {
    $objectives  = UserObjective::find('user_id = ' . $user->id);
    if (!sizeof($objectives)) {
        $user->objectives = 0;
        $user->save();
    }
}

echo "[Repair] Cron executada com sucesso!\n";