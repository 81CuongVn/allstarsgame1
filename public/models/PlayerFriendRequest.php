<?php
class PlayerFriendRequest extends Relation {
	function player($id) {
		return Player::find_first('id=' . $id);
	}
}
