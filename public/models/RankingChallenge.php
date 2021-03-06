<?php
	class RankingChallenge extends Relation {
		function graduation() {
			return Graduation::find($this->graduation_id);
		}

		function headline() {
			return Headline::find($this->headline_id);
		}

		function anime() {
			return Anime::find($this->anime_id);
		}
		function challenge() {
			return Challenge::find($this->challenge_id);
		}
		function character_theme() {
			return CharacterTheme::find($this->character_theme_id);
		}

		static function filter($where,$where2, $page, $limit) {
			$result	= [];
			$where3 = $where2;
			$where2 = $where2 ? "position_anime" : "position_general";
			 
			if(!$where && !$where3) {
				$result['pages']	= ceil(Recordset::query('SELECT COUNT(id) AS _max FROM ranking_challenges')->row()->_max / $limit);
				$result['players']	= RankingChallenge::all(['limit' => ($page * $limit) . ', ' . $limit, 'reorder' => ''.$where2.' ASC']);
			} else {
				$result['pages']	= ceil(Recordset::query('SELECT COUNT(id) AS _max FROM (SELECT id FROM ranking_challenges WHERE 1=1 ' . $where . ') _w')->row()->_max / $limit);
				$result['players']	= RankingChallenge::find('1=1 ' . $where .' '. $where3, ['limit' => ($page * $limit) . ', ' . $limit, 'reorder' => ''.$where2.' ASC']);
			}

			return $result;
		}
	}