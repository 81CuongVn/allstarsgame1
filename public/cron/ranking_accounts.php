<?php
require '_config.php';

Recordset::query('TRUNCATE TABLE `ranking_accounts`;');

$users	= Recordset::query('SELECT `id`,`name`,`level` FROM `users` WHERE `level` > 1 ORDER BY `level` DESC');
foreach ($users->result_array() as $user) {
    $points     = 0;
    $players	= Recordset::query('
			SELECT
				a.id,
				a.graduation_id,
				c.anime_id,
				a.character_theme_id,
				a.faction_id,
				a.level,
				a.wins_pvp,
				a.wins_npc,
				d.sorting AS graduation_level,
				pqc.time_total,
				pqc.pvp_total,
				pqc.daily_total,
				pqc.combat_total
			FROM
				players a JOIN character_themes b ON b.id=a.character_theme_id
				JOIN characters c ON c.id=a.character_id
				JOIN graduations d ON d.id=a.graduation_id
				JOIN player_quest_counters pqc ON pqc.player_id = a.id
			WHERE
				a.user_id=' . $user['id']);

    foreach ($players->result_array() as $player) {
        $points	+=
            ( $user['level'] * 2000 ) +
            ( $player['wins_pvp'] * 50 ) +
            ( $player['wins_npc'] * 10 ) +
            ( $player['graduation_level'] * 1000) +
            ( $player['level'] * 1000 ) +
            ( $player['time_total'] * 100 ) +
            ( $player['pvp_total'] * 200 ) +
            ( $player['daily_total'] * 250 ) +
            ( $player['combat_total'] * 200 )
        ;


    }
    Recordset::insert('ranking_accounts', [
        'user_id'	=> $user['id'],
        'name'		=> $user['name'],
        'level'		=> $user['level'],
        'score'		=> $points
    ]);
}

$rank	= 1;
$users	= Recordset::query('SELECT `id`,`score`,`level` FROM `ranking_accounts` ORDER BY `score` DESC, `level` DESC');
foreach($users->result_array() as $user) {
    /*if ($user->score <= 0)
        $user->delete();
    else {*/
        Recordset::update('ranking_accounts', [
            'position_general'  => $rank++
        ], ['id' => $user['id']]);
    // }
}
