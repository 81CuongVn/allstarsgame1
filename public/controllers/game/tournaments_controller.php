<?php
class TournamentsController extends Controller {
    public function index() {
        $player         = Player::get_instance();
        $tournaments    = Tournament::all();

        $this->assign('player',         $player);
        $this->assign('tournaments',    $tournaments);
    }

	public function show($id = null) {
        if (!$id || ($id && !is_numeric($id))) {
            redirect_to('tournaments?invalid');
        } else {
            $user       = User::get_instance();
            $player     = Player::get_instance();
            $tournament = Tournament::find_first('id = ' . $id);
            if (!$tournament) {
                redirect_to('tournaments?invalid');
            }

            // $players        = Player::find('level > 30', [
            //     'reorder'   => 'rand()',
            //     'limit'     => $tournament->places - 16
            // ]);
            // foreach ($players as $p) {
            //     $insert                 = new TournamentPlayer();
            //     $insert->player_id      = $p->id;
            //     $insert->tournament_id  = $tournament->id;
            //     $insert->save();
            // }
            // $tournament->draw();

            $can_action = TRUE;
            if (!$tournament->started) {
                if ($tournament->places <= sizeof($tournament->players())) {
                    $can_action = FALSE;
                } elseif (strtotime($tournament->subscribe_starts_at) > now() || strtotime($tournament->subscribe_ends_at) < now()) {
                    $can_action = FALSE;
                }
            } else {
                $can_action = FALSE;
            }

            $subscribed = TournamentPlayer::find_first('tournament_id=' . $tournament->id . ' and player_id=' . $player->id);

            $this->assign('user',       $user);
            $this->assign('player',     $player);
            $this->assign('can_action', $can_action);
            $this->assign('subscribed', $subscribed);
            $this->assign('tournament', $tournament);
        }
    }

    public function subscribe() {
        $this->layout			= FALSE;
        $this->as_json			= TRUE;
        $this->render			= FALSE;
        $this->json->success	= FALSE;

        $user					= User::get_instance();
        $player					= Player::get_instance();

        $errors					= [];
        $tournament_id          = isset($_POST['id']) ? $_POST['id'] : FALSE;
        $method                 = isset($_POST['method']) ? $_POST['method'] : FALSE;

        if (!$tournament_id || !is_numeric($tournament_id)) {
            $errors[]   = t('tournaments.errors.invalid_tournament');
        }

        if (!$method || !in_array($method, ['currency', 'credits'])) {
            $errors[]   = t('tournaments.errors.invalid_method');
        }

        $tournament = Tournament::find_first('id = ' . $tournament_id);
        if (!$tournament) {
            $errors[]   = t('tournaments.errors.invalid_tournament');
        } elseif ($tournament->places <= sizeof($tournament->players())) {
            $errors[]   = t('tournaments.errors.max_players');
        } elseif (strtotime($tournament->subscribe_starts_at) > now() || strtotime($tournament->subscribe_ends_at) < now()) {
            $errors[]   = t('tournaments.errors.subscribe_closed');
        }

        $characters = [];
        foreach ($user->players() as $p) { $characters[] = $p->id; }

        $already_subscribed  = TournamentPlayer::find_first('tournament_id = ' . $tournament->id . ' and player_id in (' . implode(', ', $characters) . ')');
        if ($already_subscribed) {
            $errors[]   = t('tournaments.errors.already_subscribed');
        }

        if ($method == 'currency' && $player->currency < $tournament->price_currency) {
            $errors[]   = t('tournaments.errors.enough_currency', [
                'currency'  => t('currencies.' . $player->character()->anime_id)
            ]);
        }

        if ($method == 'credits' && $user->currency < $tournament->price_credits) {
            $errors[]   = t('tournaments.errors.enough_credits');
        }

        if (!sizeof($errors)) {
            $subscribe                  = new TournamentPlayer();
            $subscribe->tournament_id   = $tournament->id;
            $subscribe->player_id       = $player->id;
            $subscribe->save();

            if ($method == 'currency') {
                $player->spend($tournament->price_currency);
            }
            if ($method == 'credits') {
                $player->spend($tournament->price_credits);
            }

            $this->json->success        = TRUE;
        } else {
            $this->json->errors = $errors;
        }
    }
}
