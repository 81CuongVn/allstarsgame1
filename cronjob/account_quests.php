<?php
require '_config.php';

$users	= User::find('active = 1');
foreach ($users as $user) {
	// Verifica se o jogador tem 4 missões ativas
    $totalQuests		= sizeof(UserDailyQuest::find('user_id = ' . $user->id));
    if ($totalQuests < 4) {
        $dailyQuest		= DailyQuest::find_first("of = 'account'", [
			'reorder'	=> 'RAND()',
			'limit'		=> 1
		]);
        if ($dailyQuest->anime && !$dailyQuest->personagem)
            $anime = Anime::find_first('active = 1', [
				'reorder'	=> 'RAND()',
				'limit'		=> 1
			]);

        Recordset::insert('user_daily_quests', [
            'user_id'				=> $user->id,
            'daily_quest_id'		=> $dailyQuest->id,
            'type'					=> $dailyQuest->type,
            'anime_id'				=> $anime->id ? $anime->id : 0
        ]);
    }
}
echo '[Account Quests] Cron executada com sucesso!';