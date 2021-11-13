<?php
class MarketPlace extends Relation {
	public function user() {
		return User::find($this->user_id);
	}

	public function player() {
		return Player::find($this->player_id);
	}

	public function bids() {
		return MarketPlaceBid::find('marketplace_id = ' . $this->id);
	}

	public function lastBid() {
		return MarketPlaceBid::find_last('marketplace_id = ' . $this->id);
	}
}
