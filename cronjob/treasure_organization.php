<?php
require '_config.php';

$organizations	= Recordset::query('SELECT id FROM organizations');
foreach ($organizations->result_array() as $organization) {
    $players	= Recordset::query('
			SELECT
				*
			FROM
				players 
			WHERE
				organization_id=' . $organization['id']);
    foreach ($players->result_array() as $player) {
        Recordset::query('UPDATE organizations SET treasure_atual= treasure_atual + '.$player['treasure_atual'].', treasure_total= treasure_total + '.$player['treasure_atual'].' WHERE id = '.$player['organization_id']);
        Recordset::query('UPDATE players SET treasure_total = treasure_atual + treasure_total where id = '.$player['id']);
        Recordset::query('UPDATE players SET treasure_atual=5 where id = '.$player['id']);
    }
}

echo "[Treasure Organization] Cron executada com sucesso!\n";