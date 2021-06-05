<?php
require '_config.php';

$maxQuests  = 4;
$guilds		= Recordset::query('SELECT * FROM guilds WHERE removed = 0');
foreach ($guilds->result_array() as $guild) {
	$totalQuests = sizeof(GuildDailyQuest::find('complete = 0 and guild_id = ' . $guild['id']));
    $diff               = $maxQuests - $totalQuests;
    if ($diff > 0) {
        $quests		= DailyQuest::find("of = 'guild'", [
			'reorder'	=> 'RAND()',
			'limit'		=> $maxQuests - $totalQuests
		]);
		foreach ($quests as $quest) {
			$guild_target	= false;
    		$player_target	= false;

			if ($quest->anime && !$quest->personagem) {
                $guild_target	= Guild::find_first('id != ' . $guild['id'], [
                    'reorder'	=> 'RAND()',
                    'limit'		=> 1
                ]);
            } elseif ($quest->anime && $quest->personagem) {
                $guild_target	= Guild::find_first('id != ' . $guild['id'], [
                    'reorder'	=> 'RAND()',
                    'limit'		=> 1
                ]);
                $player_target  = Player::find_first('guild_id = ' . $guild_target->id, [
                    'reorder'	=> 'RAND()',
                    'limit'		=> 1
                ]);
            }

			$insert = new GuildDailyQuest();
            $insert->guild_id		= $guild['id'];
            $insert->daily_quest_id	= $quest->id;
            $insert->type			= $quest->type;
            $insert->guild_enemy_id	= $guild_target ? $guild_target->id : 0;
            $insert->enemy_id		= $player_target ? $player_target->id : 0;
            $insert->save();
		}
	}
}
echo "[Organization Daily Quests] Cron executada com sucesso!\n";
