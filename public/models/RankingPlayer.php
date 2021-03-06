<?php
	class RankingPlayer extends Relation {
		function graduation() {
			return Graduation::find($this->graduation_id);
		}

		function headline() {
			return Headline::find($this->headline_id);
		}

		function anime() {
			return Anime::find($this->anime_id);
		}
		function character_theme() {
			return CharacterTheme::find($this->character_theme_id);
		}

		static function filter($where, $page, $limit) {
			$result	= [];

			if(!$where) {
				$result['pages']	= ceil(Recordset::query('SELECT COUNT(id) AS _max FROM ranking_players')->row()->_max / $limit);
				$result['players']	= RankingPlayer::all(['limit' => ($page * $limit) . ', ' . $limit, 'reorder' => 'position_general ASC']);
			} else {
				$result['pages']	= ceil(Recordset::query('SELECT COUNT(id) AS _max FROM (SELECT id FROM ranking_players WHERE 1=1 ' . $where . ') _w')->row()->_max / $limit);
				$result['players']	= RankingPlayer::find('1=1 ' . $where, ['limit' => ($page * $limit) . ', ' . $limit, 'reorder' => 'position_anime ASC']);
			}
			return $result;
		}
	}