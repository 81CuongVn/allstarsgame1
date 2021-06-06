<?php
class MapAnime extends Relation {
	static	$always_cached	= true;
	private	$_player		= null;

	function description($language_id = null) {
		return MapDescription::find_first('map_anime_id=' . $this->id . ' AND language_id=' . ($language_id ? $language_id : $_SESSION['language_id']), ['cache' => true]);
	}

	function set_player($player) {
		$this->_player	= $player;
	}

	function limit_by_day($anime_id) {
		if (!$this->_player) {
			throw new Exception("Player not speficied", 1);
		}
		return PlayerMapAnime::find('player_id=' . $this->_player->id . ' AND map_anime_id = '. $anime_id . ' AND DATE(created_at) = DATE(NOW())');
	}
}
