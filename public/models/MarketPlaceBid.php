<?php
class MarketPlaceBid extends Relation {
	public function player() {
		return Player::find($this->player_id);
	}
}
