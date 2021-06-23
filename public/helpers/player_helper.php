<?php
function is_player_online($id) {
    $redis = new Redis();
    if ($redis->pconnect(REDIS_SERVER, REDIS_PORT)) {
        $redis->auth(REDIS_PASS);
        $redis->select(0);

        $last_time  = $redis->get('player_' . $id . '_online');
        if ($last_time) {
            $date   = get_time_difference($last_time, now(true));

            if ($date['days'] == 0 && $date['hours'] == 0 && $date['minutes'] <= 15) {
                return true;
            }
        }
    }

    return false;
}

if (isset($_SESSION['player_id']) && $_SESSION['player_id']) {
	$keep	= true;
    $player	= Player::find_first('id = ' . $_SESSION['player_id']);
	if ($player) {
		// Verifica a titularidade do personagem
		$check_user	= $player->user_id != $_SESSION['user_id'];
		if (!$check_user) {
			// Verifica banimento
			$banishment	= Banishment::find_last("type = 'player' and player_id = " . $player->id);
			$check_ban	= $banishment && between(now(), strtotime($banishment->created_at), strtotime($banishment->finishes_at));
			if ($check_ban) {
				$keep	= false;
			}
		} else {
			$keep	= false;
		}
	}

	if (!$keep || (IS_MAINTENANCE && !$_SESSION['universal'])) {
		unset($_SESSION['player_id']);

		redirect_to('characters#select');
	} else {
		Player::set_instance($player);

		$player->update_online();
		$player->check_heal();
	}
}
