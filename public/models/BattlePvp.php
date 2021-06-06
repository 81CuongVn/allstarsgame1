<?php
class BattlePvp extends Relation {
	use BattleLogger;

	private	$_player	= null;

	function set_player($id) {
		$this->_player	= $id;
	}

	function enemy() {
		if (!$this->_player) {
			return false;
		}

		if ($this->_player == $this->player_id) {
			$id	= $this->enemy_id;
		} else {
			$id	= $this->player_id;
		}

		return Player::find($id);
	}

	function get_log() {
		return $this->get_battle_log($this->id, 'pvp');
	}

	function save_log($log) {
		return $this->add_battle_log($this->id, 'pvp', $log);
	}
}
