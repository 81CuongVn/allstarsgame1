<?php
class HallOfFame extends Relation {
	function anime() {
		return Anime::find($this->anime_id);
	}

	function character_theme() {
		return CharacterTheme::find($this->character_theme_id);
	}

	static function filter($where, $page, $limit, $round = false, $anime = false) {
		$result	= [];

		$anime = $anime ? "position_anime" : "position_general";
		$round = $round ? "round = '{$round}'" : "";

		if (!$where) {
			$result['pages']	= 2;
			$result['pages']	= ceil(Recordset::query('SELECT COUNT(id) AS _max FROM hall_of_fames')->row()->_max / $limit);
			$result['players']	= HallOfFame::find('1=1 AND ' . $round ,['limit' => ($page * $limit) . ', ' . $limit, 'reorder' => 'position_general ASC']);
		} else {
			$result['pages']	= 2;
			$result['pages']	= ceil(Recordset::query('SELECT COUNT(id) AS _max FROM (SELECT id FROM hall_of_fames WHERE 1=1 ' . $where . ') _w')->row()->_max / $limit);
			$result['players']	= HallOfFame::find('1=1 ' . $where, ['limit' => ($page * $limit) . ', ' . $limit, 'reorder' => $anime.' ASC']);
		}
		return $result;
	}
}
