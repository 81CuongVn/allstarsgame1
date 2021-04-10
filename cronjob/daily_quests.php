<?php
require '_config.php';

$maxQuests  = 4;
$players    = Recordset::query('SELECT * FROM players WHERE banned = 0 AND removed = 0');
foreach ($players->result_array() as $player) {
    $totalQuests = sizeof(PlayerDailyQuest::find('complete = 0 and player_id = ' . $player['id']));
    $diff               = $maxQuests - $totalQuests;
    if ($diff > 0) {
        $quests		= DailyQuest::find("of = 'player'", [
			'reorder'	=> 'RAND()',
			'limit'		=> $maxQuests - $totalQuests
		]);
        foreach ($quests as $quest) {
            if ($quest->anime && !$quest->personagem) {
                $anime = Anime::find_first('active = 1', [
                    'reorder'	=> 'RAND()',
                    'limit'		=> 1
                ]);
            } elseif ($quest->anime && $quest->personagem) {
                $anime      = Anime::find_first('active = 1', [
                    'reorder'	=> 'RAND()',
                    'limit'		=> 1
                ]);
                $character  = Character::find_first('active = 1 and anime_id = ' . $anime->id, [
                    'reorder'	=> 'RAND()',
                    'limit'		=> 1
                ]);
            }

            $insert = new PlayerDailyQuest();
            $insert->player_id      = $player['id'];
            $insert->daily_quest_id = $quest->id;
            $insert->type           = $quest->type;
            $insert->anime_id       = $anime ? $anime->id : 0;
            $insert->character_id   = $character ? $character->id : 0;
            $insert->save();
        }
    }
}
echo "[Daily Quests] Cron executada com sucesso!\n";