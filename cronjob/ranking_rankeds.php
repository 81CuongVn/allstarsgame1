<?php
	require '_config.php';

	Recordset::query('TRUNCATE TABLE ranking_rankeds');

	$animes		= Recordset::query('SELECT id FROM animes WHERE active = 1');
	$rankeds	= Recordset::query('SELECT league FROM leagues WHERE finished = 0');
	foreach ($rankeds->result_array() as $ranked) {
		foreach ($animes->result_array() as $anime) {
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
					e.win,
					e.loss,
					e.draw,
					e.league,
					e.rank
	
				FROM
					players a JOIN character_themes b ON b.id=a.character_theme_id
					JOIN characters c ON c.id=a.character_id
					JOIN graduations d ON d.id=a.graduation_id
					JOIN player_rankeds e ON e.player_id=a.id
	
				WHERE
					c.anime_id=' . $anime['id'].' AND e.league='.$ranked['league'].' AND a.removed=0
					GROUP BY a.id
				  ');	
				  
	
			foreach($players->result_array() as $player) {
				$points	= (($player['rank'] == 0 ? 200000 : 100000) / $player['rank'])  +  ($player['wins'] * 50);
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
					'loss'					=> $player['loss'],
					'draw'					=> $player['draw'],
					'win'					=> $player['win'],
					'rank'					=> $player['rank']
				]);
			}
	
			$position	= 1;
			$players	= Recordset::query('SELECT id, score FROM ranking_rankeds WHERE league_id='.$ranked['league'].' AND anime_id=' . $anime['id'] . ' ORDER BY 2 DESC');
			foreach($players->result_array() as $player) {
				// if ($player->score <= 0)
					// $player->delete();
				// else {
					Recordset::update('ranking_rankeds', [
						'position_anime'	=> $position++
					], [
						'id'				=> $player['id']
					]);
				// }
			}
		
		$position	= 1;
		$players	= Recordset::query('SELECT id, score FROM ranking_rankeds WHERE league_id='.$ranked['league'].'  ORDER BY 2 DESC');
		foreach($players->result_array() as $player) {
			// if ($player->score <= 0)
				// $player->delete();
			// else {
				Recordset::update('ranking_rankeds', [
					'position_general'	=> $position++
				], [
					'id'				=> $player['id']
				]);
			// }
		}
	}
}	

echo '[Ranking Ranked] Cron executada com sucesso!';