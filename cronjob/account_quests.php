<?php
require '_config.php';

$maxQuests  = 4;
$users      = User::find('active = 1 and banned = 0');
foreach ($users as $user) {
	// Verifica se o jogador tem 4 missões ativas
    $totalQuests	= sizeof(UserDailyQuest::find('user_id = ' . $user->id));
    $diff           = $maxQuests - $totalQuests;
    if ($diff > 0) {
        $quests		= DailyQuest::find("of = 'account'", [
			'reorder'	=> 'RAND()',
			'limit'		=> $diff
		]);
        foreach ($quests as $quest) {
            if ($quest->anime && !$quest->personagem) {
                $anime = Anime::find_first('active = 1', [
                    'reorder'	=> 'RAND()',
                    'limit'		=> 1
                ]);
            }

            $insert = new UserDailyQuest();
            $insert->user_id        = $user->id;
            $insert->daily_quest_id = $quest->id;
            $insert->type           = $quest->type;
            $insert->anime_id       = $anime ? $anime->id : 0;
            $insert->save();
        }
    }
}
echo "[Account Quests] Cron executada com sucesso!\n";