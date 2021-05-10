<?php
require '_config.php';

Recordset::query('TRUNCATE TABLE ranking_rankeds');

$factions		= Recordset::query('SELECT id FROM factions WHERE active = 1');
$rankeds	= Recordset::query('SELECT league FROM rankeds WHERE finished = 0');
foreach ($rankeds->result_array() as $ranked) {
	foreach ($factions->result_array() as $faction) {
		$players	= Recordset::query('
			SELECT
				a.id,
				a.name,
				a.headline_id,
				a.graduation_id,
				c.anime_id,
				a.character_theme_id,
				a.faction_id,
				a.level,
				d.sorting AS graduation_level,
				e.wins,
				e.losses,
				e.draws,
				e.league,
				e.rank

			FROM
				players a JOIN character_themes b ON b.id=a.character_theme_id
				JOIN characters c ON c.id=a.character_id
				JOIN graduations d ON d.id=a.graduation_id
				JOIN player_rankeds e ON e.player_id=a.id

			WHERE
				a.faction_id=' . $faction['id'].' AND e.league = '.$ranked['league'].' AND a.removed = 0 AND a.banned = 0
				GROUP BY a.id
		');
		foreach ($players->result_array() as $player) {
			$points	= ($player['rank'] ==  0 ? 200000 : (100000 / $player['rank'])) + ( $player['wins'] * 50 );
			Recordset::insert('ranking_rankeds', [
				'player_id'				=> $player['id'],
				'anime_id'				=> $player['anime_id'],
				'character_theme_id'	=> $player['character_theme_id'],
				'graduation_id'			=> $player['graduation_id'],
				'headline_id'			=> $player['headline_id'],
				'faction_id'			=> $player['faction_id'],
				'name'					=> $player['name'],
				'level'					=> $player['level'],
				'score'					=> $points,
				'league_id'				=> $player['league'],
				'losses'				=> $player['losses'],
				'draws'					=> $player['draws'],
				'wins'					=> $player['wins'],
				'rank'					=> $player['rank']
			]);
		}

		$position	= 1;
		$players	= Recordset::query('SELECT id FROM ranking_rankeds WHERE league_id='.$ranked['league'].' AND faction_id=' . $faction['id'] . '  ORDER BY `score` DESC, `level` DESC');
		foreach ($players->result_array() as $player) {
			Recordset::update('ranking_rankeds', [
				'position_faction'	=> $position++
			], [
				'id'				=> $player['id']
			]);
		}

	$position	= 1;
	$players	= Recordset::query('SELECT id FROM ranking_rankeds WHERE league_id='.$ranked['league'].' ORDER BY `score` DESC, `level` DESC');
	foreach($players->result_array() as $player) {
		Recordset::update('ranking_rankeds', [
			'position_general'	=> $position++
		], [
			'id'				=> $player['id']
		]);
	}
}
}

echo "[Ranking Ranked] Cron executada com sucesso!\n";
