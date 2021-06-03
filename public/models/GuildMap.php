<?php
class GuildMap extends Relation {
	function objects() {
		return GuildMapObject::find('guild_map_id = ' . $this->id);
	}

	function npcs() {
		return GuildMapObject::find('kind IN ("npc", "sharednpc") AND guild_map_id = ' . $this->id);
	}

	function at($x, $y) {
		return GuildMapObject::find_first("xpos = {$x} and ypos = {$y} and guild_map_id = " . $this->id);
	}
}
