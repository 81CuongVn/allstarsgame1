<?php
function is_user_online($id) {
    $redis = new Redis();
    if ($redis->pconnect(REDIS_SERVER, REDIS_PORT)) {
        $redis->auth(REDIS_PASS);
        $redis->select(0);

        $last_time  = $redis->get('user_' . $id . '_online');
        if ($last_time) {
            $date   = get_time_difference($last_time, now(true));
            if ($date['days'] == 0 && $date['hours'] == 0 && $date['minutes'] <= 15) {
                return true;
            }
        }
    }

    return false;
}

if (isset($_SESSION['user_id']) && $_SESSION['user_id']) {
	$keep	= true;
    $user	= User::find_first('id = ' . $_SESSION['user_id']);
	if ($user) {
		// Verifica troca de sessão
		$check_key	= $user->session_key != session_id() && !$_SESSION['universal'];
		if (!$check_key) {
			// Verifica banimento
			if (!$user->hasBanishment()) {
				// Verifica troca se IP
				$last_login	= UserLogin::find_last('user_id = ' . $user->id);
				$check_ip	= $last_login->ip != getIP();
				if ($check_ip) {
					$keep	= false;
				}
			} else {
				$keep	= false;
			}
		} else {
			$keep	= false;
		}
	}

	if ($keep) {
		User::set_instance($user);

		$user->update_online();
	} else {
		session_destroy();

        redirect_to();
	}
}
