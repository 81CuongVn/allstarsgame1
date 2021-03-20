<?php
class Tournament extends Relation {
    public function rewards() {
        return TournamentReward::find('tournament_id = ' . $this->id);
    }
    public function players() {
        return TournamentPlayer::find('tournament_id = ' . $this->id);
    }
    public function rounds() {
        return TournamentRound::find('tournament_id = ' . $this->id);
    }
    public function battles() {
        return TournamentBattle::find('tournament_id = ' . $this->id);
    }
    public function getRound() {
        $classified_players = sizeof(TournamentPlayer::find('declassified = 0 and tournament_id = ' . $this->id));
        if ($this->places == 8) {
            if ($classified_players == 2) {
                return t('tournament.rounds.final');
            } elseif ($classified_players == 4) {
                return t('tournament.rounds.semifinal');
            } else {
                return t('tournament.rounds.normal', [
                    'round' => $this->round
                ]);
            }
        } else {
            if ($classified_players == 2) {
                return t('tournament.rounds.final');
            } elseif ($classified_players == 4) {
                return t('tournament.rounds.semifinal');
            } elseif ($classified_players == 8) {
                return t('tournament.rounds.quarterfinals');
            } else {
                return t('tournament.rounds.normal', [
                    'round' => $this->round
                ]);
            }
        }
    }
}