<?php
require '_config.php';

$guilds = Recordset::query('SELECT * FROM guilds WHERE removed=0');
foreach ($guilds->result_array() as $guild) {
    $guilds  = 0;
    $personagens    = 0;

    $total_daily_quests		= Recordset::query('SELECT * FROM guild_daily_quests WHERE complete = 0 AND guild_id='. $guild['id'])->num_rows;

    // Verifica se a guild tem 4 missões ativas
    if ($total_daily_quests < 4) {
        $daily_quests			= Recordset::query('SELECT * FROM daily_quests WHERE of="guild" ORDER BY RAND() LIMIT 1')->row_array();
        if ($daily_quests['anime'] && !$daily_quests['personagem']) {
            $guilds			= Recordset::query('SELECT id FROM guilds WHERE removed = 0 AND id not in ('.$guild['id'].') ORDER BY RAND() LIMIT 1')->row_array();
        } elseif($daily_quests['anime'] && $daily_quests['personagem']) {
            $guilds			= Recordset::query('SELECT id FROM guilds WHERE removed = 0 AND id not in ('.$guild['id'].') ORDER BY RAND() LIMIT 1')->row_array();
            $personagens	= Recordset::query('SELECT id FROM players WHERE removed = 0 AND guild_id ='. $guilds['id'] .' ORDER BY RAND() LIMIT 1')->row_array();
        }

        Recordset::insert('guild_daily_quests', [
            'guild_id'		=> $guild['id'],
            'daily_quest_id'		=> $daily_quests['id'],
            'type'					=> $daily_quests['type'],
            'guild_enemy_id'		=> ($guilds['id']) ? $guilds['id'] : 0 ,
            'enemy_id'				=> ($personagens['id']) ? $personagens['id'] : 0
        ]);
    }
}
echo "[Guild Daily Quests] Cron executada com sucesso!\n";
