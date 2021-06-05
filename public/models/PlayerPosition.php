<?php
class PlayerPosition extends Relation {
    static function from_guild_with_map($guild_id, $guild_map_id, $accepted_event) {
		return Recordset::query("
			SELECT
				a.*,
				b.name AS player_name,
				b.character_id,
				b.character_theme_id
			FROM
				player_positions a
				JOIN players b ON b.id = a.player_id AND a.guild_id = {$guild_id}
			WHERE
				a.guild_map_id = {$guild_map_id} AND
				b.guild_accepted_event_id = {$accepted_event}")->result();
	}
}
