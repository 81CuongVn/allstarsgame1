<?php
require '_config.php';

Recordset::update('player_battle_counters', [
    'current_pvp_made'	=> 0
]);

Recordset::query('TRUNCATE TABLE player_battle_pvps');