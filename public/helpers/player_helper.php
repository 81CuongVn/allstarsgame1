<?php
function update_player_online($id) {
	if (!is_player_online($id))
    	return Recordset::query("UPDATE `players` SET `last_activity` = " . time() . " WHERE `id` = {$id}");
	return TRUE;
}

function is_player_online($id) {
	$player = Player::find_first("id = {$id}");
	return $player->last_activity + (60 * 5) > time();
}

function __check_heal() {
    if($_SESSION['player_id']) {
        Player::set_instance(Player::find($_SESSION['player_id']));

        $instance	=& Player::get_instance();
        $effects	=  $instance->get_parsed_effects();
        $now		= new DateTime();

        if(!$instance->last_healed_at) {
            $instance->last_healed_at	= date('Y-m-d H:i:s');
            $instance->save();

            $last_heal	= $now;
        } else {
            $last_heal	= new DateTime($instance->last_healed_at);
        }

        $heal_diff	= $now->diff($last_heal);
        $num_runs	= floor((($heal_diff->d * (24 * 60)) + ($heal_diff->h * 60) + $heal_diff->i/5)); // / 2
        if($num_runs) {
            //($instance->less_life > 0 || $instance->less_mana > 0 || $instance->less_stamina > 0) &&
            if(!$instance->battle_npc_id && !$instance->battle_pvp_id) {
                $current_runs	= 0;

                $max_life		= $instance->for_life(true);
                $max_mana		= $instance->for_mana(true);
                $life_heal		= percent(20, $max_life);
                $mana_heal		= percent(20, $max_mana);
                $stamina_heal	= 2 + $effects['bonus_stamina_heal'];;

                if($instance->hospital) {
                    $life_heal	*= 2;
                    $mana_heal	*= 2;
                }
                /*
                $extras			= $instance->attributes();
                $life_heal		+= percent($extras->life_regen, $life_heal);
                $mana_heal		+= percent($extras->mana_regen, $mana_heal);
                $stamina_heal	+= percent($extras->stamina_regen, $stamina_heal);
                */
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

if($_SESSION['player_id']) {
    update_player_online($_SESSION['player_id']);
}

if (!isset($_SESSION['orig_player_id'])) {
    $_SESSION['orig_player_id']	= 0;
}