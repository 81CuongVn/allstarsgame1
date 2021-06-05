<?php
require '_config.php';

$guilds	= Recordset::query('SELECT id FROM guilds');
foreach ($guilds->result_array() as $guild) {
    $players	= Recordset::query('
			SELECT
				*
			FROM
				players
			WHERE
				guild_id=' . $guild['id']);
    foreach ($players->result_array() as $player) {
        Recordset::query('UPDATE guilds SET treasure_atual= treasure_atual + '.$player['treasure_atual'].', treasure_total= treasure_total + '.$player['treasure_atual'].' WHERE id = '.$player['guild_id']);
        Recordset::query('UPDATE players SET treasure_total = treasure_atual + treasure_total where id = '.$player['id']);
        Recordset::query('UPDATE players SET treasure_atual=5 where id = '.$player['id']);
    }
}

echo "[Treasure Guild] Cron executada com sucesso!\n";
