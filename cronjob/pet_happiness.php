<?php
require '_config.php';

$players = Recordset::query('SELECT id FROM players WHERE removed = 0 AND level > 4');
foreach ($players->result_array() as $player) {
    $items	= Recordset::query('
			SELECT 
				pi.* 
			FROM player_items pi
			JOIN items i ON i.id = pi.item_id
			WHERE pi.player_id=' . $player['id'] . ' AND i.item_type_id = 3 AND pi.removed = 0
		');
    foreach($items->result_array() as $item) {
        Recordset::update('player_items', [
            'happiness'	=> ($item['happiness'] - 5) < 0 ? 0 : $item['happiness'] - 5
        ], [
            'player_id'	=> $player['id'],
            'item_id'	=> $item['item_id']
        ]);
    }
}
echo '[Pet Happiness] Cron executada com sucesso!';