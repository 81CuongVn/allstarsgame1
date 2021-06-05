<?php
class GuildEvent extends Relation {
	function maps() {
		return GuildMap::find('guild_event_id=' . $this->id);
	}

	function initial_map() {
		return GuildMap::find_first('guild_event_id=' . $this->id . ' AND is_initial = 1');
	}

	function npcs() {
		$npcs = [];
		foreach ($this->maps() as $map) {
			$npcs = array_merge($npcs, $map->npcs());
		}

		return $npcs;
	}

	function image($path_only = false) {
		$path = "dungeon/" . $this->id . ".jpg";

		if ($path_only) {
			return $path;
		} else {
			return '<img src="' . image_url($path) . '" />';
		}
	}

	function unlocked($guild_id, $event_id, $player_id) {
		return GuildAcceptedEvent::find_first('guild_id=' . $guild_id . ' AND guild_event_id = '.$event_id.' AND finished_at is NULL');
	}

	function reward() {
		return GuildEventReward::find($this->guild_event_reward_id);
	}
}
