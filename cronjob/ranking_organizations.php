<?php
require '_config.php';

Recordset::query('TRUNCATE TABLE ranking_organizations;');
$factions	= Recordset::query('SELECT id FROM factions');

foreach ($factions->result_array() as $faction) {

    $organizations	= Recordset::query('
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
				organizations o 
				JOIN players p ON o.player_id=p.id
				JOIN organization_quest_counters oq ON oq.organization_id=o.id
			WHERE
				o.faction_id=' . $faction['id'].' AND o.removed=0');


    foreach($organizations->result_array() as $organization) {
        $points	=
            ( $organization['member_count'] * 500 )  +
            ( $organization['treasure_total'] * 20 )  +
            ( $organization['daily_total'] * 500 )  +
            ( $organization['level'] * 1000 )
        ;

        Recordset::insert('ranking_organizations', [
            'organization_id'		=> $organization['id'],
            'faction_id'			=> $organization['faction_id'],
            'leader_id'				=> $organization['player_id'],
            'character_theme_id'	=> $organization['character_theme_id'],
            'leader_name'			=> $organization['leader_name'],
            'members'				=> $organization['member_count'],
            'faction_id'			=> $organization['faction_id'],
            'name'					=> $organization['name'],
            'level'					=> $organization['level'],
            'score'					=> $points
        ]);
    }

    $position	= 1;
    $organizations	= Recordset::query('SELECT id, score FROM ranking_organizations WHERE faction_id=' . $faction['id'] . ' ORDER BY 2 DESC');
    foreach($organizations->result_array() as $organization) {
        if ($organization->score <= 0)
            $organization->delete();
        else {
            Recordset::update('ranking_organizations', [
                'position_faction'	=> $position++
            ], [
                'id'				=> $organization['id']
            ]);
        }
    }
}

$position	    = 1;
$organizations	= Recordset::query('SELECT id, score FROM ranking_organizations ORDER BY 2 DESC');
foreach($organizations->result_array() as $organization) {
    if ($organization->score <= 0)
        $organization->delete();
    else {
        Recordset::update('ranking_organizations', [
            'position_general'	=> $position++
        ], [
            'id'				=> $organization['id']
        ]);
    }
}

echo '[Ranking Organizations] Cron executada com sucesso!';