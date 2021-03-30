<?php
class PlayerBattleStat extends Relation {
	function graduation() {
		return Graduation::find($this->graduation_id);
	}

	function headline() {
		return Headline::find($this->headline_id);
	}
	function character() {
		return Character::find($this->character_id);
	}
	function anime() {
		return Anime::find($this->anime_id);
	}
	function character_theme() {
		return CharacterTheme::find($this->character_theme_id);
	}

	static function filter($where, $page, $limit,$campo) {
		$campo = $campo ? $campo : "victory_pvp";
		$result	= [];

		if(!$where) {
			$result['pages']	= ceil(Recordset::query('SELECT COUNT(id) AS _max FROM player_battle_stats')->row()->_max / $limit);
			$result['players']	= PlayerBattleStat::all(['limit' => ($page * $limit) . ', ' . $limit, 'reorder' => $campo .' DESC']);
		} else {
			$result['pages']	= ceil(Recordset::query('SELECT COUNT(id) AS _max FROM (SELECT id FROM player_battle_stats WHERE 1=1 ' . $where . ') _w')->row()->_max / $limit);
			$result['players']	= PlayerBattleStat::find('1=1 ' . $where, ['limit' => ($page * $limit) . ', ' . $limit, 'reorder' => $campo .' DESC']);
		}
		return $result;
	}
}