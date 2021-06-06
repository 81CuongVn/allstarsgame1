<?php
class PlayerBattlePvpLog extends Relation {
	function player() {
		$player	= Player::get_instance()->id;

		return $this->player_id == $player->id ? $player : Player::find_first($this->player_id);
	}

	function enemy() {
		$player	= Player::get_instance()->id;

		return $this->enemy_id == $player->id ? $player : Player::find_first($this->enemy_id);
	}
}
