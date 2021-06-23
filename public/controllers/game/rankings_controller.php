<?php
class RankingsController extends Controller {
	public function hall_of_fames() {
		$player		= Player::get_instance();
		$page		= isset($_POST['page']) && is_numeric($_POST['page']) ? $_POST['page'] : 0;
		$limit		= 32;
		$filter		= '';

		if (!$_POST) {
			$filter			.= '';
			$anime_id		= 0;
			$character_id	= 0;
			$faction_id		= 0;
			$round			= "r1";
			$graduation_id	= 0;
			$name			= '';
		} else {
			if (isset($_POST['anime_id']) && is_numeric($_POST['anime_id'])) {
				if ($_POST['anime_id'] != 0) {
					$filter	.= ' AND anime_id=' . $_POST['anime_id'];
				}
				$anime_id	= $_POST['anime_id'];
			}/* else {
				$anime_id	= $player->character()->anime_id;
			}*/
			if (isset($_POST['character_id']) && is_numeric($_POST['character_id'])) {
				if ($_POST['character_id'] != 0) {
					$filter	.= ' AND character_id=' . $_POST['character_id'];
				}

				$character_id	= $_POST['character_id'];
			} else {
				$character_id = 0;
			}

			if (isset($_POST['faction_id']) && is_numeric($_POST['faction_id'])) {
				if($_POST['faction_id'] != 0) {
					$filter	.= ' AND faction_id=' . $_POST['faction_id'];
				}

				$faction_id	= $_POST['faction_id'];
			} else {
				$faction_id	= $player->faction_id;
			}

			if (isset($_POST['round']) && strlen(trim($_POST['round']))) {
				$filter	.= ' AND round="' . $_POST['round'].'"';

				$round	= $_POST['round'];
			} else {
				$round	= '';
			}

			if (isset($_POST['name']) && strlen(trim($_POST['name']))) {
				$filter	.= ' AND name LIKE "%' . addslashes($_POST['name']) . '%"';
				$name	= $_POST['name'];
			} else {
				$name	= '';
			}
		}
		$result		= HallOfFame::filter($filter, $page, $limit, $round, $anime_id);
		$animes		= Anime::find('active=1', ['cache' => true]);
		$factions	= Faction::find('active=1', ['cache' => true]);

		$this->assign('player',			$player);
		$this->assign('players',		$result['players']);
		$this->assign('pages',			$result['pages']);
		$this->assign('page',			$page);
		$this->assign('anime_id',		$anime_id);
		$this->assign('character_id',	$character_id);
		$this->assign('faction_id',		$faction_id);
		$this->assign('round',			$round);
		$this->assign('name',			$name);
		$this->assign('animes',			$animes);
		$this->assign('factions',		$factions);
	}

	public function players() {
		$player		= Player::get_instance();
		$page		= isset($_POST['page']) && is_numeric($_POST['page']) ? $_POST['page'] : 0;
		$limit		= 32;
		$filter		= '';

		if (!$_POST) {
			$filter			.= ' AND faction_id=' . $player->faction_id;
			$anime_id		= 0;
			$character_id	= 0;
			$faction_id		= $player->faction_id;
			$graduation_id	= 0;
			$name			= '';
		} else {
			if (isset($_POST['anime_id']) && is_numeric($_POST['anime_id'])) {
				if($_POST['anime_id'] != 0) {
					$filter	.= ' AND anime_id=' . $_POST['anime_id'];
				}

				$anime_id	= $_POST['anime_id'];
			}/* else {
				$anime_id	= $player->character()->anime_id;
			}*/
			if (isset($_POST['character_id']) && is_numeric($_POST['character_id'])) {
				if ($_POST['character_id'] != 0) {
					$filter	.= ' AND character_id=' . $_POST['character_id'];
				}

				$character_id	= $_POST['character_id'];
			} else {
				$character_id = 0;
			}

			if (isset($_POST['faction_id']) && is_numeric($_POST['faction_id'])) {
				if ($_POST['faction_id'] != 0) {
					$filter	.= ' AND faction_id=' . $_POST['faction_id'];
				}

				$faction_id	= $_POST['faction_id'];
			} else {
				$faction_id	= $player->faction_id;
			}

			if (isset($_POST['graduation_id']) && is_numeric($_POST['graduation_id'])) {
				if ($_POST['graduation_id'] != 0) {
					if ($anime_id) {
						$filter	.= ' AND graduation_id=' . $_POST['graduation_id'];
					} else {
						$filter	.= ' AND graduation_id IN(SELECT id FROM graduations WHERE sorting=' . $_POST['graduation_id'] . ')';
					}
				}

				$graduation_id	= $_POST['graduation_id'];
			} else {
				$graduation_id	= 0;
			}

			if (isset($_POST['name']) && strlen(trim($_POST['name']))) {
				$filter	.= ' AND name LIKE "%' . addslashes($_POST['name']) . '%"';
				$name	= $_POST['name'];
			} else {
				$name	= '';
			}
		}
		$result		= RankingPlayer::filter($filter, $page, $limit);
		$animes		= Anime::find('active=1', ['cache' => true]);
		$factions	= Faction::find('active=1', ['cache' => true]);

		if ($anime_id) {
			$graduations	= Graduation::all();
		} else {
			$graduations	= [];
			foreach ($animes as $anime) {
				$grads	= GraduationDescription::find('anime_id=' . $anime->id, ['cache' => true]);
				foreach ($grads as $grad) {
					$graduation = $grad->graduation();
					if (!isset($graduations[$graduation->sorting])) {
						$graduations[$graduation->sorting]		= ['id' => $graduation->sorting, 'name' => []];
					}
					$graduations[$graduation->sorting]['name'][]	= $graduation->description($anime->id)->name;
				}
			}
			foreach ($graduations as $key => $graduation) {
				$graduations[$key]['name']	= implode(' / ', $graduation['name']);
			}
		}

		$this->assign('player',			$player);
		$this->assign('players',		$result['players']);
		$this->assign('pages',			$result['pages']);
		$this->assign('page',			$page);
		$this->assign('anime_id',		$anime_id);
		$this->assign('character_id',	$character_id);
		$this->assign('faction_id',		$faction_id);
		$this->assign('graduation_id',	$graduation_id);
		$this->assign('name',			$name);
		$this->assign('animes',			$animes);
		$this->assign('factions',		$factions);
		$this->assign('graduations',	$graduations);
	}

	public function battles() {
		$player		= Player::get_instance();
		$page		= isset($_POST['page']) && is_numeric($_POST['page']) ? $_POST['page'] : 0;
		$limit		= 32;
		$filter		= '';

		if (!$_POST) {
			$filter					.= ' AND anime_id=' . $player->character()->anime_id;
			$anime_id				= $player->character()->anime_id;
			$character_id			= 0;
			$faction_id				= 0;
			$periodo				= "";
			$status					= "";
			$campo 					= "";
			$graduation_id			= 0;
		} else {
			if (isset($_POST['anime_id']) && is_numeric($_POST['anime_id'])) {
				if ($_POST['anime_id'] != 0) {
					$filter	.= ' AND anime_id=' . $_POST['anime_id'];
				}

				$anime_id	= $_POST['anime_id'];
			}/* else {
				$anime_id	= $player->character()->anime_id;
			}*/
			if (isset($_POST['character_id']) && is_numeric($_POST['character_id'])) {
				if ($_POST['character_id'] != 0) {
					$filter	.= ' AND character_id=' . $_POST['character_id'];
				}

				$character_id	= $_POST['character_id'];
			} else {
				$character_id = 0;
			}

			if (isset($_POST['faction_id']) && is_numeric($_POST['faction_id'])) {
				if ($_POST['faction_id'] != 0) {
					$filter	.= ' AND faction_id=' . $_POST['faction_id'];
				}

				$faction_id	= $_POST['faction_id'];
			} else {
				$faction_id	= $player->faction_id;
			}
			if (isset($_POST['status']) && is_string($_POST['status'])) {
				switch ($_POST['periodo']) {
					case "daily":
						$campo = $_POST['status'];
					break;
					case "weekly":
						$campo = $_POST['status']."_weekly";
					break;
					case "monthly":
						$campo = $_POST['status']."_monthly";
					break;
				}
				$periodo	= $_POST['periodo'];
				$status		= $_POST['status'];
			}

			if (isset($_POST['graduation_id']) && is_numeric($_POST['graduation_id'])) {
				if ($_POST['graduation_id'] != 0) {
					if ($anime_id) {
						$filter	.= ' AND graduation_id=' . $_POST['graduation_id'];
					} else {
						$filter	.= ' AND graduation_id IN(SELECT id FROM graduations WHERE sorting=' . $_POST['graduation_id'] . ')';
					}
				}

				$graduation_id	= $_POST['graduation_id'];
			} else {
				$graduation_id	= 0;
			}

		}
		$result		= PlayerBattleStat::filter($filter, $page, $limit,$campo);
		$animes		= Anime::find('active=1', ['cache' => true]);
		$factions	= Faction::find('active=1', ['cache' => true]);

		if ($anime_id) {
			$graduations	= Graduation::all();
		} else {
			$graduations	= [];
			foreach ($animes as $anime) {
				$grads	= GraduationDescription::find('anime_id=' . $anime->id, ['cache' => true]);
				foreach ($grads as $grad) {
					$graduation = $grad->graduation();
					if (!isset($graduations[$graduation->sorting])) {
						$graduations[$graduation->sorting]		= ['id' => $graduation->sorting, 'name' => []];
					}
					$graduations[$graduation->sorting]['name'][]	= $graduation->description($anime->id)->name;
				}
			}
			foreach ($graduations as $key => $graduation) {
				$graduations[$key]['name']	= implode(' / ', $graduation['name']);
			}
		}

		$this->assign('player',			$player);
		$this->assign('players',		$result['players']);
		$this->assign('pages',			$result['pages']);
		$this->assign('page',			$page);
		$this->assign('anime_id',		$anime_id);
		$this->assign('character_id',	$character_id);
		$this->assign('faction_id',		$faction_id);
		$this->assign('periodo',		$periodo);
		$this->assign('status',			$status);
		$this->assign('graduation_id',	$graduation_id);
    	$this->assign('animes',			$animes);
    	$this->assign('factions',		$factions);
		$this->assign('graduations',	$graduations);
	}

	public function achievements() {
		$player		= Player::get_instance();
		$page		= isset($_POST['page']) && is_numeric($_POST['page']) ? $_POST['page'] : 0;
		$limit		= 32;
		$filter		= '';

		if (!$_POST) {
			$filter			.= ' AND anime_id=' . $player->character()->anime_id;
			$anime_id		= $player->character()->anime_id;
			$character_id	= 0;
			$faction_id		= 0;
			$graduation_id	= 0;
			$name			= '';
		} else {
			if (isset($_POST['anime_id']) && is_numeric($_POST['anime_id'])) {
				if ($_POST['anime_id'] != 0) {
					$filter	.= ' AND anime_id=' . $_POST['anime_id'];
				}

				$anime_id	= $_POST['anime_id'];
			}/* else {
				$anime_id	= $player->character()->anime_id;
			}*/
			if (isset($_POST['character_id']) && is_numeric($_POST['character_id'])) {
				if ($_POST['character_id'] != 0) {
					$filter	.= ' AND character_id=' . $_POST['character_id'];
				}

				$character_id	= $_POST['character_id'];
			} else {
				$character_id = 0;
			}

			if (isset($_POST['faction_id']) && is_numeric($_POST['faction_id'])) {
				if ($_POST['faction_id'] != 0) {
					$filter	.= ' AND faction_id=' . $_POST['faction_id'];
				}

				$faction_id	= $_POST['faction_id'];
			} else {
				$faction_id	= $player->faction_id;
			}

			if (isset($_POST['graduation_id']) && is_numeric($_POST['graduation_id'])) {
				if ($_POST['graduation_id'] != 0) {
					if ($anime_id) {
						$filter	.= ' AND graduation_id=' . $_POST['graduation_id'];
					} else {
						$filter	.= ' AND graduation_id IN(SELECT id FROM graduations WHERE sorting=' . $_POST['graduation_id'] . ')';
					}
				}

				$graduation_id	= $_POST['graduation_id'];
			} else {
				$graduation_id	= 0;
			}

			if (isset($_POST['name']) && strlen(trim($_POST['name']))) {
				$filter	.= ' AND name LIKE "%' . addslashes($_POST['name']) . '%"';
				$name	= $_POST['name'];
			} else {
				$name	= '';
			}
		}
		$result		= RankingAchievement::filter($filter, $page, $limit);
		$animes		= Anime::find('active=1', ['cache' => true]);
		$factions	= Faction::find('active=1', ['cache' => true]);

		if ($anime_id) {
			$graduations	= Graduation::all();
		} else {
			$graduations	= [];
			foreach ($animes as $anime) {
				$grads	= GraduationDescription::find('anime_id=' . $anime->id, ['cache' => true]);
				foreach ($grads as $grad) {
					$graduation = $grad->graduation();
					if (!isset($graduations[$graduation->sorting])) {
						$graduations[$graduation->sorting]		= ['id' => $graduation->sorting, 'name' => []];
					}
					$graduations[$graduation->sorting]['name'][]	= $graduation->description($anime->id)->name;
				}
			}
			foreach ($graduations as $key => $graduation) {
				$graduations[$key]['name']	= implode(' / ', $graduation['name']);
			}
		}

		$this->assign('player',			$player);
		$this->assign('players',		$result['players']);
		$this->assign('pages',			$result['pages']);
		$this->assign('page',			$page);
		$this->assign('anime_id',		$anime_id);
		$this->assign('character_id',	$character_id);
		$this->assign('faction_id',		$faction_id);
		$this->assign('graduation_id',	$graduation_id);
		$this->assign('name',			$name);
	    $this->assign('animes',			$animes);
    	$this->assign('factions',		$factions);
		$this->assign('graduations',	$graduations);
	}

	public function list_characters() {
		$this->layout	= false;
		$this->assign('characters', Character::find("active = 1 AND anime_id=".$_POST['anime_id']));
	}

	public function challenges() {
		$player		= Player::get_instance();
		$page		= isset($_POST['page']) && is_numeric($_POST['page']) ? $_POST['page'] : 0;
		$limit		= 32;
		$filter		= '';
		$filter2	= '';

		$animes		= Anime::find('active=1', ['cache' => true]);
		$factions	= Faction::find('active=1', ['cache' => true]);
		$challenges	= Challenge::find('active=1', ['cache' => true]);

		if (!$_POST) {
			$filter			.= ' AND challenge_id=' . $challenges[0]->id;
			$filter2		.= '';
			$anime_id		= 0;
			$faction_id		= 0;
			$challenge_id	= $challenges[0]->id;
			$graduation_id	= 0;
			$name			= '';
		} else {
			if (isset($_POST['anime_id']) && is_numeric($_POST['anime_id'])) {
				if ($_POST['anime_id'] != 0) {
					$filter2 .= ' AND anime_id=' . $_POST['anime_id'];
				}

				$anime_id	= $_POST['anime_id'];
			}/* else {
				$anime_id	= $player->character()->anime_id;
			}*/

			if (isset($_POST['faction_id']) && is_numeric($_POST['faction_id'])) {
				if ($_POST['faction_id'] != 0) {
					$filter	.= ' AND faction_id=' . $_POST['faction_id'];
				}

				$faction_id	= $_POST['faction_id'];
			} else {
				$faction_id	= $player->faction_id;
			}

			if (isset($_POST['challenge_id']) && is_numeric($_POST['challenge_id'])) {
				if ($_POST['challenge_id'] != 0) {
					$filter	.= ' AND challenge_id=' . $_POST['challenge_id'];
				}

				$challenge_id	= $_POST['challenge_id'];
			}

			if (isset($_POST['graduation_id']) && is_numeric($_POST['graduation_id'])) {
				if ($_POST['graduation_id'] != 0) {
					if ($anime_id) {
						$filter	.= ' AND graduation_id=' . $_POST['graduation_id'];
					} else {
						$filter	.= ' AND graduation_id IN(SELECT id FROM graduations WHERE sorting=' . $_POST['graduation_id'] . ')';
					}
				}

				$graduation_id	= $_POST['graduation_id'];
			} else {
				$graduation_id	= 0;
			}

			if (isset($_POST['name']) && strlen(trim($_POST['name']))) {
				$filter	.= ' AND name LIKE "%' . addslashes($_POST['name']) . '%"';
				$name	= $_POST['name'];
			} else {
				$name	= '';
			}
		}

		$result		= RankingChallenge::filter($filter,$filter2, $page, $limit);

		if ($anime_id) {
			$graduations	= Graduation::all();
		} else {
			$graduations	= [];
			foreach ($animes as $anime) {
				$grads	= GraduationDescription::find('anime_id=' . $anime->id, ['cache' => true]);
				foreach ($grads as $grad) {
					$graduation = $grad->graduation();
					if (!isset($graduations[$graduation->sorting])) {
						$graduations[$graduation->sorting]		= ['id' => $graduation->sorting, 'name' => []];
					}
					$graduations[$graduation->sorting]['name'][]	= $graduation->description($anime->id)->name;
				}
			}
			foreach ($graduations as $key => $graduation) {
				$graduations[$key]['name']	= implode(' / ', $graduation['name']);
			}
		}

		$this->assign('player',			$player);
		$this->assign('players',		$result['players']);
		$this->assign('pages',			$result['pages']);
		$this->assign('page',			$page);
		$this->assign('anime_id',		$anime_id);
		$this->assign('faction_id',		$faction_id);
		$this->assign('challenge_id',	$challenge_id);
		$this->assign('graduation_id',	$graduation_id);
		$this->assign('name',			$name);
    	$this->assign('animes',			$animes);
    	$this->assign('factions',		$factions);
		$this->assign('challenges',		$challenges);
		$this->assign('graduations',	$graduations);
	}

	public function rankeds() {
		$player			= Player::get_instance();
		$page			= isset($_POST['page']) && is_numeric($_POST['page']) ? $_POST['page'] : 0;
		$limit			= 32;
		$filter			= '';
		$filter2		= '';
		$last_ranked	= Ranked::find_first('started = 1', [
			'reorder' => 'id desc'
		]);

		if (!$_POST) {
			$filter			.= '';
			$filter2		.= '';
			$anime_id		= 0;
			$faction_id		= 0;
			$ranked_id		= $last_ranked ? $last_ranked->id : 0;
			$graduation_id	= 0;
			$name			= '';
		} else {
			if (isset($_POST['anime_id']) && is_numeric($_POST['anime_id'])) {
				if ($_POST['anime_id'] != 0) {
					$filter2 .= ' AND anime_id=' . $_POST['anime_id'];
				}

				$anime_id	= $_POST['anime_id'];
			}/* else {
				$anime_id	= $player->character()->anime_id;
			}*/

			if (isset($_POST['faction_id']) && is_numeric($_POST['faction_id'])) {
				if ($_POST['faction_id'] != 0) {
					$filter	.= ' AND faction_id=' . $_POST['faction_id'];
				}

				$faction_id	= $_POST['faction_id'];
			} else {
				$faction_id	= $player->faction_id;
			}

			if (isset($_POST['ranked_id']) && is_numeric($_POST['ranked_id'])) {
				if ($_POST['ranked_id'] != 0) {
					$filter	.= ' AND ranked_id=' . $_POST['ranked_id'];
				}

				$ranked_id	= $_POST['ranked_id'];
			} else {
				$ranked_id	= $last_ranked ? $last_ranked->id : 0;
			}

			if (isset($_POST['graduation_id']) && is_numeric($_POST['graduation_id'])) {
				if ($_POST['graduation_id'] != 0) {
					if ($anime_id) {
						$filter	.= ' AND graduation_id=' . $_POST['graduation_id'];
					} else {
						$filter	.= ' AND graduation_id IN(SELECT id FROM graduations WHERE sorting=' . $_POST['graduation_id'] . ')';
					}
				}

				$graduation_id	= $_POST['graduation_id'];
			} else {
				$graduation_id	= 0;
			}

			if (isset($_POST['name']) && strlen(trim($_POST['name']))) {
				$filter	.= ' AND name LIKE "%' . addslashes($_POST['name']) . '%"';
				$name	= $_POST['name'];
			} else {
				$name	= '';
			}
		}

		$result		= RankingRanked::filter($filter,$filter2, $page, $limit);
		$animes		= Anime::find('active=1', ['cache' => true]);
		$factions	= Faction::find('active=1', ['cache' => true]);
		$rankeds	= Ranked::find('started = 1 order by id desc');

		if ($anime_id) {
			$graduations	= Graduation::all();
		} else {
			$graduations	= [];
			foreach ($animes as $anime) {
				$grads	= GraduationDescription::find('anime_id=' . $anime->id, ['cache' => true]);
				foreach ($grads as $grad) {
					$graduation = $grad->graduation();
					if (!isset($graduations[$graduation->sorting])) {
						$graduations[$graduation->sorting]		= ['id' => $graduation->sorting, 'name' => []];
					}
					$graduations[$graduation->sorting]['name'][]	= $graduation->description($anime->id)->name;
				}
			}
			foreach ($graduations as $key => $graduation) {
				$graduations[$key]['name']	= implode(' / ', $graduation['name']);
			}
		}

		$this->assign('player',			$player);
		$this->assign('players',		$result['players']);
		$this->assign('pages',			$result['pages']);
		$this->assign('page',			$page);
		$this->assign('anime_id',		$anime_id);
		$this->assign('faction_id',		$faction_id);
		$this->assign('ranked_id',		$ranked_id);
		$this->assign('graduation_id',	$graduation_id);
		$this->assign('name',			$name);
		$this->assign('animes',			$animes);
		$this->assign('factions',		$factions);
		$this->assign('rankeds',		$rankeds);
		$this->assign('graduations',	$graduations);
	}
	function guilds() {
		$player		= Player::get_instance();
		$page		= isset($_POST['page']) && is_numeric($_POST['page']) ? $_POST['page'] : 0;
		$limit		= 32;
		$filter		= '';

		if (!$_POST) {
			$filter		   .= ' AND faction_id=' . $player->faction_id;
			$faction_id		= $player->faction_id;
			$name			= '';
		} else {
			if (isset($_POST['faction_id']) && is_numeric($_POST['faction_id'])) {
				if ($_POST['faction_id'] != 0) {
					$filter	.= ' AND faction_id=' . $_POST['faction_id'];
				}

				$faction_id	= $_POST['faction_id'];
			}

			if (isset($_POST['name']) && strlen(trim($_POST['name']))) {
				$filter	.= ' AND name LIKE "%' . addslashes($_POST['name']) . '%"';
				$name	= $_POST['name'];
			} else {
				$name	= '';
			}
		}

    	$result	= RankingGuild::filter($filter, $page, $limit);
    	$factions	= Faction::find('active=1', ['cache' => true]);

		$this->assign('player',		$player);
		$this->assign('players',	$result['players']);
		$this->assign('pages',		$result['pages']);
		$this->assign('page',		$page);
		$this->assign('faction_id',	$faction_id);
    	$this->assign('name',		$name);
    	$this->assign('factions',	$factions);
	}
	function account() {
		$player		= Player::get_instance();
		$page		= isset($_POST['page']) && is_numeric($_POST['page']) ? $_POST['page'] : 0;
		$limit		= 32;
		$filter		= '';

		if (!$_POST) {
			$filter		   .= '';
			$name			= '';
		} else {
			if (isset($_POST['name']) && strlen(trim($_POST['name']))) {
				$filter	.= ' AND name LIKE "%' . addslashes($_POST['name']) . '%"';
				$name	= $_POST['name'];
			} else {
				$name	= '';
			}
		}

		$result	= RankingAccount::filter($filter, $page, $limit);

		$this->assign('player',		$player);
		$this->assign('players',	$result['players']);
		$this->assign('pages',		$result['pages']);
		$this->assign('page',		$page);
		$this->assign('name',		$name);
	}
}
