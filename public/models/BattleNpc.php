<?php
class BattleNpc extends Relation {
	use BattleLogger;

	function player() {
		if($this->player_id == Player::get_instance()->id) {
			return Player::get_instance();
		} else {
			return Player::find($this->player_id);
		}
	}

	function get_log() {
		return $this->get_battle_log($this->id, 'npc');
	}

	function save_log($log) {
		return $this->add_battle_log($this->id, 'npc', $log);
	}
}