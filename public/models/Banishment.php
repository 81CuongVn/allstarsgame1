<?php
class Banishment extends Relation {
	function admin() {
		return User::find_first('id = ' . $this->admin_id, [
			'skip_after_assign'	=> true
		]);
	}

	function user() {
		return User::find_first('id = ' . $this->user_id, [
			'skip_after_assign'	=> true
		]);
	}

	function player() {
		return Player::find_first('id = ' . $this->player_id, [
			'skip_after_assign'	=> true
		]);
	}
}
