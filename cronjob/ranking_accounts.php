<?php
require '_config.php';

Recordset::query('TRUNCATE TABLE `ranking_accounts`;');

$users	= Recordset::query('
	SELECT
		a.id,
		a.name,
		a.level,
		b.daily_total
	FROM
		users a
		JOIN user_quest_counters b ON b.user_id = a.id
	WHERE
		a.level > 1 AND
		a.banned = 0
	ORDER BY
		a.level DESC');
foreach ($users->result_array() as $user) {
    $points     =	$user['level']				* 2000;
	$points		+=  $user['time_total']			* 100;
	$points		+=  $user['pvp_total']			* 200;
	$points		+=  $user['daily_total']		* 250;
	$points		+=  $user['pet_total']			* 50;

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
			a.banned = 0 AND a.removed = 0 AND a.user_id = ' . $user['id']);

    foreach ($players->result_array() as $player) {
		// Calcula os bosses mortos pelo player
        $boss_score = 0;
        $challenges = Recordset::query('SELECT quantity FROM player_challenges WHERE player_id = '. $player['id']);
        foreach ($challenges->result_array() as $challenge) {
            $boss_score += floor($challenge['quantity'] / 5) * 100;
        }

        $points +=  $player['wins_pvp']         * 50;
        $points +=  $player['wins_npc']         * 10;
        $points +=  $player['graduation_level'] * 1000;
        $points +=  $player['level']            * 1000;
        $points +=  $player['time_total']       * 100;
        $points +=  $player['pvp_total']        * 200;
        $points +=  $player['daily_total']      * 250;
        $points +=  $player['pet_total']        * 50;
        $points +=  $player['combat_total']     * 200;
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
foreach ($users->result_array() as $user) {
    Recordset::update('ranking_accounts', [
        'position_general'  => $rank++
    ], ['id' => $user['id']]);
}

echo "[Ranking Accounts] Cron executada com sucesso!\n";
