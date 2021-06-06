<?php
class SiteNewsComment extends Relation {
	function user() {
		return User::find($this->user_id);
	}

	function player() {
		return Player::find($this->player_id);
	}
}
