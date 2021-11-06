<?php
class GuildMapObject extends Relation {
	function session($guildId, $acceptedEventId) {
		return GuildMapObjectSession::find_first("guild_id = {$guildId} and guild_map_object_id = {$this->id} and guild_accepted_event_id = {$acceptedEventId}");
	}
	function description() {
		return GuildMapObjectDescription::find_first('guild_map_object_id=' . $this->id . ' AND language_id=' . $_SESSION['language_id'], array('cache' => true));
	}
	function buildNpc($player) {

	}
}
