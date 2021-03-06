<?php
require '_config.php';

$usersIgnore = [];
$objectives = Recordset::query("SELECT DISTINCT(`user_id`) FROM `user_objectives` WHERE `complete` = 0");
foreach ($objectives->result_array() as $objective) {
    $usersIgnore[] = $objective['user_id'];
}

$addSql = '';
if (count($usersIgnore) > 0)
    $addSql = "AND `id` NOT IN (" . implode(',', $usersIgnore) . ")";

$users = Recordset::query("SELECT * FROM `users` WHERE `active` = 1 AND `removed` = 0 " . $addSql);
foreach ($users->result_array() as $user) {
    $objectives = Recordset::query("SELECT * FROM `achievements` WHERE `type`='objectives' ORDER BY RAND() LIMIT 10");
    foreach ($objectives->result_array() as $objective) {
        Recordset::insert('user_objectives', [
            'user_id'				=> $user['id'],
            'objective_id'			=> $objective['id']
        ]);
    }
    Recordset::update('users', [
        'objectives'    => 1
    ], ['id' => $user['id']]);
}