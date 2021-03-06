<?php
	class BattleNpc extends Relation {
		function player() {
			if($this->player_id == Player::get_instance()->id) {
				return Player::get_instance();
			} else {
				return Player::find($this->player_id);
			}
		}
	}