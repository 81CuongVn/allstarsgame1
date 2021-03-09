<?php
require '_config.php';

$organizations = Recordset::query('SELECT * FROM organizations WHERE removed=0');
foreach ($organizations->result_array() as $organization) {
    $organizations  = 0;
    $personagens    = 0;

    $total_daily_quests		= Recordset::query('SELECT * FROM organization_daily_quests WHERE complete = 0 AND organization_id='. $organization['id'])->num_rows;

    // Verifica se a guild tem 4 missões ativas
    if ($total_daily_quests < 4) {
        $daily_quests			= Recordset::query('SELECT * FROM daily_quests WHERE of="organization" ORDER BY RAND() LIMIT 1')->row_array();
        if ($daily_quests['anime'] && !$daily_quests['personagem']) {
            $organizations			= Recordset::query('SELECT id FROM organizations WHERE removed = 0 AND id not in ('.$organization['id'].') ORDER BY RAND() LIMIT 1')->row_array();
        } elseif($daily_quests['anime'] && $daily_quests['personagem']) {
            $organizations			= Recordset::query('SELECT id FROM organizations WHERE removed = 0 AND id not in ('.$organization['id'].') ORDER BY RAND() LIMIT 1')->row_array();
            $personagens			= Recordset::query('SELECT id FROM players WHERE removed = 0 AND organization_id ='. $organizations['id'] .' ORDER BY RAND() LIMIT 1')->row_array();
        }

        Recordset::insert('organization_daily_quests', [
            'organization_id'		=> $organization['id'],
            'daily_quest_id'		=> $daily_quests['id'],
            'type'					=> $daily_quests['type'],
            'guild_enemy_id'		=> ($organizations['id']) ? $organizations['id'] : 0 ,
            'enemy_id'				=> ($personagens['id']) ? $personagens['id'] : 0
        ]);
    }
}
echo '[Organization Daily Quests] Cron executada com sucesso!';