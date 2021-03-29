<?php
require '_config.php';

# Finaliza o Evento a cada 3 horas.
$battleAnimes = EventAnime::find('completed = 0');
foreach ($battleAnimes as $battleAnime) {
	$winner = 0;
	if ($battleAnime->points_a > $battleAnime->points_b)
		$winner = $battleAnime->anime_a_id;
	if ($battleAnime->points_a < $battleAnime->points_b)
		$winner = $battleAnime->anime_b_id;

	$battleAnime->completed		= TRUE;
	$battleAnime->anime_win_id	= $winner;
	$battleAnime->save();

	if ($winner != 0) {
		$anime = Anime::find_first('id = ' . $winner);
		++$anime->score;
		$anime->save();
	}
}

# Gera uma nova batalha a cada 3 horas
if (date('G') >= 9 && date('G') <= 23) {
	$lastWinners	= [];
	$winners		= EventAnime::find("completed = 1",[
		'reorder'	=> 'id desc',
		'limit'		=> 6
	]);
	foreach ($winners as $winner)
		$lastWinners[] = $winner->anime_win_id;

	$addSql = '';
	if (sizeof($lastWinners))
		$addSql = ' and id not in (' . implode(",", $lastWinners) .')';

	$animes = Anime::find('playable = 1' . $addSql, [
		'reorder'	=> 'RAND()',
		'limit'		=> 2
	]);
	Recordset::insert('event_animes', [
		'anime_a_id'	=> $animes[0]->id,
		'anime_b_id'	=> $animes[1]->id,
		'points_a'		=> 1000,
		'points_b'		=> 1000
	]);
}
echo "[Battle Animes] Cron executada com sucesso!\n";