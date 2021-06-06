<?php
class HistoryModeNpc extends Relation {
	static	$always_cached	= true;
	private	$_player		= null;

	function subgroup() {
		return HistoryModeSubgroup::find($this->history_mode_subgroup_id, ['cache' => true]);
	}

	function description($language_id = null) {
		return HistoryModeNpcDescription::find_first('history_mode_npc_id=' . $this->id . ' AND language_id=' . ($language_id ? $language_id : $_SESSION['language_id']));
	}

	function set_player($player) {
		$this->_player	= $player;
	}

	function killed() {
		if (!$this->_player) {
			throw new Exception("Player not specified", 1);
		}

		return UserHistoryModeNpc::find('user_id=' . $this->_player->user_id . ' AND history_mode_npc_id=' . $this->id);
	}

	function can_battle() {
		if (!$this->_player) {
			throw new Exception("Player not specified", 1);
		}

		$ok	= true;

		if ($this->killed()) {
			$ok	= false;
		}

		if ($this->stamina_cost > $this->_player->for_stamina()) {
			$ok	= false;
		}

		/*if ($this->faction_id != $this->_player->faction_id) {
			$ok	= false;
		}*/

		if ($this->sorting > 1) {
			$previous	= HistoryModeNpc::find_first('history_mode_subgroup_id=' . $this->history_mode_subgroup_id . ' AND faction_id=1 AND sorting=' . ($this->sorting - 1), ['cache' => true]);
			$previous->set_player($this->_player);
			if (!$previous->killed()) {
				$ok	= false;
			}
		}

		if ($this->subgroup()->sorting > 1) {
			$previous	= HistoryModeSubgroup::find_first('history_mode_group_id=' . $this->subgroup()->history_mode_group_id . ' AND sorting=' . ($this->subgroup()->sorting - 1));
			if (!$previous->completed($this->_player)) {
				$ok	= false;
			}
		}

		return $ok;
	}

	function image($path_only = false) {
		$path	= "/images/adventure/npc/" . $this->id . ".png";

		if ($path_only) {
			return $path;
		} else {
			return '<img src="' . asset_url($path) . '" />';
		}
	}
}
