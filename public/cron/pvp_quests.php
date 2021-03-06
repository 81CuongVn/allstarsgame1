<?php
require '_config.php';

Recordset::update('players', [
    'pvp_quest_id'	=> 0
]);
Recordset::query('TRUNCATE TABLE player_pvp_quests');
