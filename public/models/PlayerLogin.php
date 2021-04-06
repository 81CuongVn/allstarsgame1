<?php
class PlayerLogin extends Relation {
    public function player() {
        return Player::find_first($this->player_id);
    }
}