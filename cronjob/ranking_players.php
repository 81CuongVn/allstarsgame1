<?php
require '_config.php';

Recordset::query('TRUNCATE TABLE ranking_players;');

$animes	= Recordset::query('SELECT id FROM animes');
foreach ($animes->result_array() as $anime) {
    $players	= Recordset::query('
			SELECT
				a.id,
				a.name,
				a.headline_id,
				a.graduation_id,
				c.anime_id,
				e.active,
				a.character_theme_id,
				a.character_id,
				a.faction_id,
				a.level,
				a.training_total,
				a.wins_pvp,
				a.wins_npc,
				d.sorting AS graduation_level,
				pqc.time_total,
				pqc.pvp_total,
				pqc.daily_total,
				pqc.pet_total,
				pqc.combat_total
			FROM
				players a JOIN character_themes b ON b.id=a.character_theme_id
				JOIN characters c ON c.id=a.character_id
				JOIN graduations d ON d.id=a.graduation_id
				JOIN animes e ON e.id = c.anime_id
				JOIN player_quest_counters pqc ON pqc.player_id = a.id
			WHERE
				c.anime_id = ' . $anime['id'] . ' AND e.active = 1 AND a.level >= 1 AND a.removed = 0');

    foreach ($players->result_array() as $player) {
        // Calcula os bosses mortos pelo player
        $challenges	= Recordset::query('SELECT quantity FROM player_challenges WHERE  player_id = '. $player['id']);
        $boss_score = 0;
        foreach ($challenges->result_array() as $challenge) {
            $boss_score = $boss_score + floor($challenge['quantity'] / 5) * 100;
        }

        $points	=
            ( $player['wins_pvp'] * 50 ) +
            ( $player['wins_npc'] * 10 ) +
            ( $player['graduation_level'] * 1000) +
            ( $player['level'] * 1000 ) +
            ( $player['time_total'] * 100 ) +
            ( $player['pvp_total'] * 200 ) +
            ( $player['daily_total'] * 250 ) +
            ( $player['pet_total'] * 50 ) +
            ( $player['combat_total'] * 200 ) +
            $boss_score
        ;

        Recordset::insert('ranking_players', [
            'player_id'				=> $player['id'],
            'anime_id'				=> $player['anime_id'],
            'character_id'			=> $player['character_id'],
            'character_theme_id'	=> $player['character_theme_id'],
            'graduation_id'			=> $player['graduation_id'],
            'headline_id'			=> $player['headline_id'],
            'faction_id'			=> $player['faction_id'],
            'name'					=> $player['name'],
            'level'					=> $player['level'],
            'score'					=> $points,
            'detail'				=> $player['wins_pvp'].",".($winRankeds ? $winRankeds : 0) .",".$player['wins_npc'].",".$player['graduation_level'].",".$player['time_total'].",".$player['pvp_total'].",".$player['daily_total'].",".$player['pet_total'].",".$player['combat_total']
        ]);
    }

    $position	= 1;
    $players	= Recordset::query('SELECT `id`,`score`,`level` FROM `ranking_players` WHERE `anime_id`=' . $anime['id'] . ' ORDER BY `score` DESC, `level` DESC');
    foreach ($players->result_array() as $player) {
        if ($player->score <= 0)
            $player->delete();
        else {
            Recordset::update('ranking_players', [
                'position_anime'	=> $position++
            ], ['id' => $player['id']]);
        }
    }
}

$position	= 1;
$players	= Recordset::query('SELECT `id`,`score`,`level` FROM `ranking_players` ORDER BY `score` DESC, `level` DESC');
foreach ($players->result_array() as $player) {
    if ($player->score <= 0)
        $player->delete();
    else {
        Recordset::update('ranking_players', [
            'position_general'	=> $position++
        ], ['id' => $player['id']]);
    }
}

echo '[Ranking Players] Cron executada com sucesso!';