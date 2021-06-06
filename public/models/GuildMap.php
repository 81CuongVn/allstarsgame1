<?php
class GuildMap extends Relation {
	function description() {
		return GuildMapDescription::find_first('guild_map_id=' . $this->id . ' AND language_id=' . $_SESSION['language_id'], array('cache' => true));
	}

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
