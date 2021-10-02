<?php
$menu_data		= [];
$raw_menu_data	= [];
$menu_actions	= [];
$url_allowed	= false;

function generate_menu_data($admin = false) {
    global $menu_data, $menu_actions, $raw_menu_data;

    $categories	= MenuCategory::find('is_admin = '. ($admin ? 1 : 0), ['cache' => true]);

    if ($_SESSION['player_id']) {
        $instance	= Player::find($_SESSION['player_id']);
    } else {
        $instance	= false;
    }

    foreach ($categories as $category) {
        $item	= [
            'id'	=> $category->id,
            'name'	=> $category->name,
			'icon'	=> $category->icon,
            'menus'	=> []
		];

        $raw_menu_data[$category->id]	= $item;

        if (!is_menu_accessible($category, $instance)) {
            continue;
        }

        $menus						= $category->menus();
        $menu_data[$category->id]	= $item;

        foreach ($menus as $menu) {
            $sub_item	= [
                'id'		=> $menu->id,
                'name'		=> $menu->name,
                'href'		=> !$menu->external ? make_url($menu->href) : $menu->href,
                'hidden'	=> $menu->hidden,
				'external'	=> $menu->external
			];

            if (!is_menu_accessible($menu, $instance)) {
                continue;
            }

            $menu_actions[]	= make_url($menu->href, [], true);

            if ($menu->hidden) {
                continue;
            }

            $menu_data[$category->id]['menus'][]		= $sub_item;
            $raw_menu_data[$category->id]['menus'][]	= $sub_item;
        }
    }

    $actions	= Menu::find('menu_category_id = 0 and is_admin = '. ($admin ? 1 : 0), [ 'cache' => true ]);
    foreach ($actions as $action) {
        if (!is_menu_accessible($action, $instance)) {
            continue;
        }

        $menu_actions[]	= make_url($action->href, [], true);
    }
}

function is_menu_accessible($menu, $player) {
    $ok		= true;
	$user	= false;

	if ($_SESSION['loggedin']) {
		$user	= User::find_first('id = ' . $_SESSION['user_id']);
	}

    if ($menu->h_loggedin == 1 && !$_SESSION['loggedin']) {
		$ok	= false;
	}

	if ($menu->h_loggedin == 2 && $_SESSION['loggedin']) {
		$ok	= false;
	}

	if ($user && $menu->is_admin) {
		if ($menu->is_admin && !$user->admin) {
			$ok	= false;
		} elseif ($menu->is_admin && $user->admin) {
			if ($menu->h_admin > $user->admin) {
				$ok	= false;
			}
		}
	} else {
		if ($user && $user->admin < $menu->h_admin) {
			$ok	= false;
		}

		if ($menu->h_player == 1 && !$player) {
			$ok	= false;
		}

		if ($menu->h_player == 2 && $player) {
			$ok	= false;
		}

		if ($menu->h_next_level == 1 && !$player) {
			$ok	= false;
		} else {
			if ($menu->h_next_level == 1) {
				if (!$player || ($player && !$player->is_next_level())) {
					$ok	= false;
				}
			}

			if ($menu->h_next_level == 2) {
				if ($player && $player->is_next_level()) {
					$ok	= false;
				}
			}
		}

		if ($menu->h_training_technique == 1) {
			if (!$player || ($player && !$player->technique_training_id)) {
				$ok	= false;
			}
		} elseif ($menu->h_training_technique == 2) {
			if ($player && $player->technique_training_id) {
				$ok	= false;
			}
		}

		if ($menu->h_battle_npc) {
			if ($menu->h_battle_npc == 1) {
				if (!$player || ($player && !$player->battle_npc_id)) {
					$ok	= false;
				}
			} elseif ($menu->h_battle_npc == 2) {
				if ($player && $player->battle_npc_id) {
					$ok	= false;
				}
			}
		}

		if ($menu->h_battle_pvp) {
			if ($menu->h_battle_pvp == 1) {
				if (!$player || ($player && !$player->battle_pvp_id)) {
					$ok	= false;
				}
			} elseif ($menu->h_battle_pvp == 2) {
				if ($player && $player->battle_pvp_id) {
					$ok	= false;
				}
			}
		}

		if ($menu->h_battle_room) {
			if ($menu->h_battle_room == 1) {
				if (!$player || ($player && !$player->battle_room_id)) {
					$ok	= false;
				}
			} elseif ($menu->h_battle_room == 2) {
				if ($player && $player->battle_room_id) {
					$ok	= false;
				}
			}
		}

		if ($menu->h_hospital) {
			if ($menu->h_hospital == 1) {
				if (($player && !$player->hospital) || !$player) {
					$ok	= false;
				}
			} elseif ($menu->h_hospital == 2) {
				if (($player && $player->hospital) || !$player) {
					$ok	= false;
				}
			}
		}

		if ($menu->h_time_quest) {
			if ($menu->h_time_quest == 1) {
				if (($player && !$player->time_quest_id) || !$player) {
					$ok	= false;
				}
			} elseif ($menu->h_time_quest == 2) {
				if (($player && $player->time_quest_id) || !$player) {
					$ok	= false;
				}
			}
		}

		if ($menu->h_pvp_quest) {
			if ($menu->h_pvp_quest == 1) {
				if (($player && !$player->pvp_quest_id) || !$player) {
					$ok	= false;
				}
			} elseif ($menu->h_pvp_quest == 2) {
				if (($player && $player->pvp_quest_id) || !$player) {
					$ok	= false;
				}
			}
		}

		if ($menu->h_guild == 1) {
			if (!$player || ($player && !$player->guild_id)) {
				$ok	= false;
			}
		} elseif ($menu->h_guild == 2) {
			if ($player && $player->guild_id) {
				$ok	= false;
			}
		}

		if ($menu->h_guild_event == 1) {
			if (!$player || ($player && !$player->guild_accepted_event_id)) {
				$ok	= false;
			}
		} elseif ($menu->h_guild_event == 2) {
			if ($player && $player->guild_accepted_event_id) {
				$ok	= false;
			}
		}

		if ($menu->h_map == 1) {
			if (!$player || ($player && !$player->map_id)) {
				$ok	= false;
			}
		} elseif ($menu->h_map == 2) {
			if ($player && $player->map_id) {
				$ok	= false;
			}
		}
	}

    return $ok;
}

function is_menu_active($menu_href) {
	global $controller, $action, $is_admin, $site_url;

	$menu_url	= str_replace([$site_url . '/', '#'], ['', '/'], $menu_href);
	$real_url	= ($is_admin ? 'admin/' : '') . $controller . '/' . $action;
	$real_url	= str_replace('/index', '', $real_url);
	if ($real_url == $menu_url) {
		return true;
	}

	return false;
}

function validate_current_url() {
    global	$menu_actions, $framework_force_denied,
			$controller, $action, $_SERVER, $is_admin;

    $captcha		= strpos($_SERVER['PATH_INFO'], 'captcha');
    $url_allowed	= false;

    if ($captcha !== false && $captcha == 1) {
        return;
    }

    $real_url	= ($is_admin ? 'admin/' : '') . $controller . '/' . $action;

    foreach ($menu_actions as $menu_action) {
        if (strpos($menu_action, '/') === false) {
            $menu_action	.=	'/index';
        }

        if (!$menu_action && !$real_url) {
            $url_allowed	= true;
            break;
        } else {
            if ($menu_action) {
                $pos	= strpos($real_url, $menu_action);
                if ($pos !== false && $pos == 0) {
                    $url_allowed	= true;
                    break;
                }
            }
        }
    }

    if (!$url_allowed) {
        $framework_force_denied	= true;
    }
}

generate_menu_data($is_admin);
validate_current_url();
