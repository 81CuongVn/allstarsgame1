<?php
class TournamentBattle extends Relation {
    public function player() {
        return Player::find_first('id = ' . $this->player_id);
    }
    public function enemy() {
        return Player::find_first('id = ' . $this->enemy_id);
    }
    public function pending($round) {
        // return TournamentBattle::find('finished = 0 and tournament_id = ' . $this->tour . ' and round = ' . $round);
    }
    public function proccess_wo($round) {
        // $battles = $this->pending($round);
        // foreach ($battles as $battle) {

        // }
    }
}