<?php
	class HistoryModeGroup extends Relation {
		static	$always_cached	= true;
		private	$_player		= null;

		function description($language_id = null) {
			return HistoryModeGroupDescription::find_first('history_mode_group_id=' . $this->id . ' AND language_id=' . ($language_id ? $language_id : $_SESSION['language_id']), ['cache' => true]);
		}

		function set_player($player) {
			$this->_player	= $player;
		}

		function unlocked() {
			if (!$this->_player) {
				throw new Exception("Player not speficied", 1);
			}

			return UserHistoryModeGroup::find('user_id=' . $this->_player->user_id . ' AND history_mode_group_id=' . $this->id);
		}

		function image($path_only = false) {
			$path	= "/images/adventure/group/" . $this->id . ".jpg";

			if ($path_only) {
				return $path;
			} else {
				return '<img src="' . asset_url($path) . '" />';
			}
		}

		function subgroups() {
			return HistoryModeSubgroup::find('history_mode_group_id=' . $this->id . ($_SESSION['universal'] ? '' : ' AND active=1'), ['cache' => true, 'reorder' => 'sorting ASC']);
		}
	}