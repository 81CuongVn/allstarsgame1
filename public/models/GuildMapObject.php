<?php
class GuildMapObject extends Relation {
	function description() {
		return GuildMapObjectDescription::find_first('guild_map_object_id=' . $this->id . ' AND language_id=' . $_SESSION['language_id'], array('cache' => true));
	}
	function buildNpc($player) {

	}
}
