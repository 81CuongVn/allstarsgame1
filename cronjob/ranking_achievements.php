<?php
require '_config.php';

Recordset::query('TRUNCATE TABLE ranking_achievements;');

$factions	= Recordset::query('SELECT id FROM factions');
foreach ($factions->result_array() as $faction) {
    $players	= Recordset::query("SELECT
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
			d.sorting AS graduation_level
		FROM
			players a JOIN characters c ON c.id=a.character_id
			JOIN graduations d ON d.id=a.graduation_id
			JOIN animes e ON e.id = c.anime_id
		WHERE
			a.faction_id = {$faction['id']} AND
			e.active= 1 AND
			a.level >= 1 AND
			a.banned = 0 AND
			a.removed = 0");
    foreach ($players->result_array() as $player) {
        $players_achievement	= Recordset::query('select sum(points) as total from achievements WHERE id in (select achievement_id from player_achievements WHERE player_id='.$player['id'].')')->result_array();
        $points	= $players_achievement[0]['total'];

        Recordset::insert('ranking_achievements', [
            'player_id'				=> $player['id'],
            'anime_id'				=> $player['anime_id'],
            'character_id'			=> $player['character_id'],
            'character_theme_id'	=> $player['character_theme_id'],
            'graduation_id'			=> $player['graduation_id'],
            'headline_id'			=> $player['headline_id'],
            'faction_id'			=> $player['faction_id'],
            'name'					=> $player['name'],
            'level'					=> $player['level'],
            'score'					=> $points
        ]);
    }

	$position	= 1;
	$players	= Recordset::query("SELECT `id` FROM `ranking_achievements` WHERE `faction_id` = {$faction['id']} ORDER BY `score` DESC, `level` DESC");
    foreach ($players->result_array() as $player) {
        Recordset::update('ranking_achievements', [
            'position_faction'	=> $position++
        ], [
            'id'				=> $player['id']
        ]);
    }
}

$position	= 1;
$players	= Recordset::query('SELECT `id` FROM `ranking_achievements` ORDER BY `score` DESC, `level` DESC');
foreach ($players->result_array() as $player) {
    Recordset::update('ranking_achievements', [
        'position_general'	=> $position++
    ], [
        'id'				=> $player['id']
    ]);
}

echo "[Ranking Achievements] Cron executada com sucesso!\n";
