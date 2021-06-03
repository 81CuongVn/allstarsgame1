<?php
require '_config.php';

Recordset::query('TRUNCATE TABLE ranking_guilds;');
$factions	= Recordset::query('SELECT id FROM factions');

foreach ($factions->result_array() as $faction) {

    $guilds	= Recordset::query('
			SELECT
				o.player_id,
				o.id,
				o.faction_id,
				o.name,
				o.level,
				o.treasure_total,
				o.member_count,
				p.name AS leader_name,
				p.character_theme_id,
				p.id as leader_id,
				oq.daily_total

			FROM
				guilds o
				JOIN players p ON o.player_id=p.id
				JOIN guild_quest_counters oq ON oq.guild_id=o.id
			WHERE
				o.faction_id=' . $faction['id'].' AND o.removed=0');


    foreach($guilds->result_array() as $guild) {
        $points	=
            ( $guild['member_count'] * 500 )  +
            ( $guild['treasure_total'] * 20 )  +
            ( $guild['daily_total'] * 500 )  +
            ( $guild['level'] * 1000 )
        ;

        Recordset::insert('ranking_guilds', [
            'guild_id'				=> $guild['id'],
            'faction_id'			=> $guild['faction_id'],
            'leader_id'				=> $guild['player_id'],
            'character_theme_id'	=> $guild['character_theme_id'],
            'leader_name'			=> $guild['leader_name'],
            'members'				=> $guild['member_count'],
            'faction_id'			=> $guild['faction_id'],
            'name'					=> $guild['name'],
            'level'					=> $guild['level'],
            'score'					=> $points
        ]);
    }

    $position	= 1;
    $guilds	= Recordset::query('SELECT id, score FROM ranking_guilds WHERE faction_id=' . $faction['id'] . ' ORDER BY 2 DESC');
    foreach($guilds->result_array() as $guild) {
        Recordset::update('ranking_guilds', [
            'position_faction'	=> $position++
        ], [
            'id'				=> $guild['id']
        ]);
    }
}

$position	    = 1;
$guilds	= Recordset::query('SELECT id, score FROM ranking_guilds ORDER BY 2 DESC');
foreach($guilds->result_array() as $guild) {
    Recordset::update('ranking_guilds', [
        'position_general'	=> $position++
    ], [
        'id'				=> $guild['id']
    ]);
}

echo "[Ranking Guilds] Cron executada com sucesso!\n";
