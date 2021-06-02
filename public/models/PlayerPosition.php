<?php
class PlayerPosition extends Relation {
    static function from_organization_with_map($organization_id, $organization_map_id, $accepted_event) {
		return Recordset::query("
			SELECT
				a.*,
				b.name AS player_name,
				b.character_id,
				b.character_theme_id
			FROM
				player_positions a
				JOIN players b ON b.id = a.player_id AND a.organization_id = {$organization_id}
			WHERE
				a.organization_map_id = {$organization_map_id} AND
				b.organization_accepted_event_id = {$accepted_event}")->result();
	}
}
