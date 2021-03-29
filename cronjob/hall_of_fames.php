<?php
require '_config.php';

$animes	= Recordset::query('SELECT id FROM animes');
foreach ($animes->result_array() as $anime) {
    $players	= Recordset::query('
			SELECT
				a.id,
				a.name,
				a.user_id,
				c.anime_id,
				u.level AS account_level,
				a.character_theme_id,
				a.character_id,
				a.faction_id,
				a.level,
				a.wins_pvp,
				a.wins_npc,
				a.draws_npc,
				a.draws_pvp,
				a.losses_npc,
				a.losses_pvp,
				pqc.time_total,
				pqc.pvp_total,
				pqc.daily_total,
				pqc.pet_total,
				pqc.combat_total,
				rp.score,
				rp.position_anime,
				rp.position_general
			FROM
				players a 
				JOIN characters c ON c.id=a.character_id
				JOIN users u ON u.id=a.user_id
				JOIN animes e ON e.id = c.anime_id
				JOIN player_quest_counters pqc ON pqc.player_id = a.id
				JOIN ranking_players rp ON rp.player_id = a.id
			WHERE
				c.anime_id=' . $anime['id'] . ' AND e.active = 1 AND a.level >= 5 AND a.removed = 0');
    foreach ($players->result_array() as $player) {
        Recordset::insert('hall_of_fames', [
            'round'					=> 'r3',
            'player_id'				=> $player['id'],
            'user_id'				=> $player['user_id'],
            'anime_id'				=> $player['anime_id'],
            'character_id'			=> $player['character_id'],
            'character_theme_id'	=> $player['character_theme_id'],
            'faction_id'			=> $player['faction_id'],
            'name'					=> $player['name'],
            'level'					=> $player['level'],
            'account_level'			=> $player['account_level'],
            'score'					=> $player['score'],
            'position_anime'		=> $player['position_anime'],
            'position_general'		=> $player['position_general'],
            'draws_npc'				=> $player['draws_npc'],
            'draws_pvp'				=> $player['draws_pvp'],
            'wins_npc'				=> $player['wins_npc'],
            'wins_pvp'				=> $player['wins_pvp'],
            'losses_npc'			=> $player['losses_npc'],
            'losses_pvp'			=> $player['losses_pvp'],
            'time_total'			=> $player['time_total'],
            'pvp_total'				=> $player['pvp_total'],
            'daily_total'			=> $player['daily_total'],
            'combat_total'			=> $player['combat_total'],
            'pet_total'				=> $player['pet_total']
        ]);
    }
}
echo "[Hall Of Fame] Cron executada com sucesso!\n";