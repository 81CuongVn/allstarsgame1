<?php
	class OrganizationPlayer extends Relation {
		function player($full = false) {
			return Player::find($this->player_id, ['skip_after_assign' => !$full]);
		}
	}