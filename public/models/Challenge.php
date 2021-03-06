<?php
	class Challenge extends Relation {
		static	$always_cached	= true;
		private	$_player		= null;

		function description($language_id = null) {
			return ChallengeDescription::find_first('challenge_id=' . $this->id . ' AND language_id=' . ($language_id ? $language_id : $_SESSION['language_id']), ['cache' => true]);
		}

		function set_player($player) {
			$this->_player	= $player;
		}

		function unlocked() {
			if (!$this->_player) {
				throw new Exception("Player not speficied", 1);
			}

			return PlayerChallenge::find('player_id=' . $this->_player->id . ' AND challenge_id=' . $this->id . ' and complete = 0');
		}
		function limit_by_day() {
			if (!$this->_player) {
				throw new Exception("Player not speficied", 1);
			}

			return PlayerChallenge::find('player_id=' . $this->_player->id . ' AND DATE(created_at) = DATE(NOW())');
		}

		function image($path_only = false) {
			$path	= "/images/challenges/" . $this->id . ".jpg";

			if ($path_only) {
				return $path;
			} else {
				return '<img src="' . asset_url($path) . '" />';
			}
		}
	}