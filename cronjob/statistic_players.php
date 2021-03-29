<?php
require '_config.php';

Recordset::query('TRUNCATE TABLE statistic_players;');

$stats	= Recordset::query('
	SELECT
			b.id AS character_id,
			b.anime_id ,     
			COUNT(b.id) AS total
	FROM
		players a JOIN characters b ON b.id=a.character_id  
	WHERE 
		b.active = 1	      
	GROUP BY 1, 2');
foreach ($stats->result_array() as $stat) {
    Recordset::insert('statistic_players', [
        'anime_id'		=> $stat['anime_id'],
        'character_id'	=> $stat['character_id'],
        'total'			=> $stat['total']
    ]);
}

echo "[Statistics Players] Cron executada com sucesso!\n";