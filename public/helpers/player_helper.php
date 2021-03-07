<?php
function update_player_online($id) {
    $redis = new Redis();
    if ($redis->pconnect(REDIS_SERVER, REDIS_PORT)) {
        $redis->auth(REDIS_PASS);
        $redis->select(0);
        $redis->set('player_' . $id . '_online', now(true));
    }
}

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

function __check_heal() {
    if ($_SESSION['player_id']) {
        Player::set_instance(Player::find($_SESSION['player_id']));

        $instance	=& Player::get_instance();
        $effects	=  $instance->get_parsed_effects();
        $now		= new DateTime();

        if (!$instance->last_healed_at) {
            $instance->last_healed_at	= date('Y-m-d H:i:s');
            $instance->save();

            $last_heal	= $now;
        } else {
            $last_heal	= new DateTime($instance->last_healed_at);
        }

        $heal_diff	= $now->diff($last_heal);
        $num_runs	= floor((($heal_diff->d * (24 * 60)) + ($heal_diff->h * 60) + $heal_diff->i / 5)); // / 2
        if($num_runs) {
            if(!$instance->battle_npc_id && !$instance->battle_pvp_id) {
                $current_runs	= 0;

                $max_life		= $instance->for_life(true);
                $max_mana		= $instance->for_mana(true);
                $life_heal		= percent(20, $max_life);
                $mana_heal		= percent(20, $max_mana);
                $stamina_heal	= 2 + $effects['bonus_stamina_heal'];;

                if ($instance->hospital) {
                    $life_heal	*= 2;
                    $mana_heal	*= 2;
                }

                $extras			= $instance->attributes();
                $life_heal		+= percent($extras->life_regen, $life_heal);
                $mana_heal		+= percent($extras->mana_regen, $mana_heal);
                // $stamina_heal	+= percent($extras->stamina_regen, $stamina_heal);

                while($current_runs++ < $num_runs) {
                    if ($instance->less_life > 0) {
                        $instance->less_life	-= $life_heal;
                    }

                    if ($instance->less_mana > 0) {
                        $instance->less_mana	-= $mana_heal;
                    }

                    if ($instance->less_stamina > 0) {
                        $instance->less_stamina	-= $stamina_heal;
                    }

                    if ($instance->less_life < 0) {
                        $instance->less_life	= 0;
                    }

                    if ($instance->less_mana < 0) {
                        $instance->less_mana	= 0;
                    }

                    if ($instance->less_stamina < 0) {
                        $instance->less_stamina	= 0;
                    }

                    if($instance->less_life == 0 && $instance->less_mana == 0) {
                        $instance->hospital	= 0;
                        //break;
                    }
                }
            }

            $instance->last_healed_at	= date('Y-m-d H:i:s');
        }

        $instance->save();
    }
}

__check_heal();

if ($_SESSION['player_id']) {
    update_player_online($_SESSION['player_id']);
}