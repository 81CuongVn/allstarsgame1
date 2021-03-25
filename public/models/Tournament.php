<?php
class Tournament extends Relation {
    public function winner() {
        return TournamentPlayer::find_first('tournament_id = ' . $this->id . ' and winner = 1');
    }
    public function rewards() {
        return TournamentReward::find('tournament_id = ' . $this->id);
    }
    public function players() {
        return TournamentPlayer::find('tournament_id = ' . $this->id);
    }
    public function classifieds() {
        return TournamentPlayer::find('declassified = 0 and tournament_id = ' . $this->id);
    }
    public function bracket() {
        return TournamentBattle::find('round = 1 and tournament_id = ' . $this->id);
    }
    public function results($round) {
        $results    = [];
        $battles    = TournamentBattle::find('round = ' . $round . ' and finished = 1 and tournament_id = ' . $this->id);
        foreach ($battles as $battle) {
            $player = 0;
            $enemy  = 0;
            if ($battle->winner == $battle->player_id) {
                $player = 1;
            } else {
                $enemy  = 1;
            }

            $results[]  = "[{$player}, {$enemy}]";
        }

        return implode(',', $results);
    }
    public function draw() {
        if ($this->round != 0) {
            return FALSE;
        }

        $players        = [];
        $classifieds    = $this->classifieds();
        foreach ($classifieds as $classified) {
            $players[]  = $classified;
        }

        while (sizeof($players) > 0) {
            $match                      = array_rand($players, 2);
            list($player, $enemy)       = $match;

            $battle                 = new TournamentBattle();
            $battle->tournament_id  = $this->id;
            $battle->player_id      = $players[$player]->player_id;
            $battle->enemy_id       = $players[$enemy]->player_id;
            $battle->round          = 1;
            $battle->save();

            unset($players[$player], $players[$enemy]);
        }

        ++$this->round;
        $this->save();
    }
    public function teamWidth() {
        if ($this->places < 32) {
            return 120;
        } else {
            return 100;
        }
    }
    public function roundMargin() {
        if ($this->places < 32) {
            return 40;
        } else {
            return 20;
        }
    }
    public function has_requirement($player) {
        $ok				= true;
        $log			= '<ul class="requirement-list">';
        $error			= '<li class="error"><i class="fa fa-times fa-fw"></i> %result</li>';
        $success		= '<li class="success"><i class="fa fa-check fa-fw"></i> %result</li>';

        if ($this->req_level) {
            $ok		= $this->req_level > $player->level ? false : $ok;
            $log	.= str_replace('%result', t('tournaments.requirements.level', [
                'level' => $this->req_level
            ]), $this->req_level > $player->level ? $error : $success);
        }

        $log	.= '</ul>';

        return [
            'has_requirement' => $ok,
            'requirement_log' => $log
        ];
    }
    public function make_preliminary() {
        $classifieds = $this->classifieds();
        $sql = "SELECT `tp`.`player_id` FROM `tournament_players` AS `tp` WHERE `tp`.`tournament_id` = '{$this->id}' AND `tp`.`declassified` = 0 AND (SELECT COUNT(`tb`.`id`) FROM `tournament_battles` AS `tb` WHERE `tb`.`tournament_id` = '{$this->id}' AND `tb`.`round` = '{$this->round}' AND (`tp`.`player_id` = `tb`.`player_id` OR `tp`.`player_id` = `tb`.`enemy_id`)) = 0 ORDER BY RAND() LIMIT " . (sizeof($classifieds) / 4);
        $result = Recordset::query($sql);
    }
    public function next_round() {
        $battles = TournamentBattle::pending($this->round);
        foreach ($battles as $battle) {
            if (!$battle->created) {
                if (!$battle->player_ready) {
                    $battle->set_winner($battle->enemy_ready);
                } elseif (!$battle->enemy_ready) {
                    $battle->set_winner($battle->player_ready);
                }
            } else {
            }
        }
    }
    public function total_rounds() {
        $count  = 0;
        $places = $this->places;
        while ($places != 1) {
            $places /= 2;
            ++$count;
        }

        return $count;
    }
    public function battles() {
        return TournamentBattle::find('tournament_id = ' . $this->id);
    }
    public function getRound() {
        if ($this->round == 0) {
            return t('tournaments.rounds.subscriptions');
        } else {
            if ($this->places == 8) {
                if ($this->round == 3) {
                    return t('tournaments.rounds.final');
                } elseif ($this->round == 2) {
                    return t('tournaments.rounds.semifinal');
                } else {
                    return t('tournaments.rounds.normal', [
                        'round' => $this->round
                    ]);
                }
            } elseif ($this->places == 16) {
                if ($this->round == 4) {
                    return t('tournaments.rounds.final');
                } elseif ($this->round == 3) {
                    return t('tournaments.rounds.semifinal');
                } elseif ($this->round == 2) {
                    return t('tournaments.rounds.quarterfinals');
                } else {
                    return t('tournaments.rounds.normal', [
                        'round' => $this->round
                    ]);
                }
            } elseif ($this->places == 32) {
                if ($this->round == 5) {
                    return t('tournaments.rounds.final');
                } elseif ($this->round == 4) {
                    return t('tournaments.rounds.semifinal');
                } elseif ($this->round == 3) {
                    return t('tournaments.rounds.quarterfinals');
                } else {
                    return t('tournaments.rounds.normal', [
                        'round' => $this->round
                    ]);
                }
            }
        }
    }
}