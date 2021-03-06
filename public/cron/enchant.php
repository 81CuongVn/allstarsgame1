<?php
require '_config.php';

$players = Recordset::query('SELECT * FROM players WHERE enchant_points >0');
foreach ($players->result_array() as $player) {
    Recordset::update('players', [
        'enchant_points'	=> 0
    ], [
        'id'	=> $player['id']
    ]);
}