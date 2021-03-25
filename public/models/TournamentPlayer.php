<?php
class TournamentPlayer extends Relation {
    public function player() {
        return Player::find_first('id = ' . $this->player_id);
    }
}