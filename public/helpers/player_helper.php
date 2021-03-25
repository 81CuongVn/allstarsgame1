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
                return TRUE;
            }
        }

        return FALSE;
    }

    return FALSE;
}

if (isset($_SESSION['player_id']) && $_SESSION['player_id']) {
    $player = Player::find($_SESSION['player_id']);
    Player::set_instance($player);

    $player->update_online();
    $player->check_heal();
}