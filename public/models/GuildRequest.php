<?php
class GuildRequest extends Relation {
	function player($full = false) {
		return Player::find($this->player_id, ['skip_after_assign' => !$full]);
	}

	function replier($full = false) {
		return Player::find($this->reply_player_id, ['skip_after_assign' => !$full]);
	}
}
