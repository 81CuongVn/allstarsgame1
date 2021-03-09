<?php
class RankingsController extends Controller {
	function hall_of_fames(){
		$player		= Player::get_instance();
		$page		= isset($_POST['page']) && is_numeric($_POST['page']) ? $_POST['page'] : 0;
		$limit		= 32;
		$filter		= '';
		
		if(!$_POST) {
			$filter			.= '';
			$anime_id		= 0;
			$character_id	= 0;
			$faction_id		= 0;
			$round			= "r3";
			$graduation_id	= 0;
			$name			= '';
		} else {
			if(isset($_POST['anime_id']) && is_numeric($_POST['anime_id'])) {
				if($_POST['anime_id'] != 0) {
					$filter	.= ' AND anime_id=' . $_POST['anime_id'];						
				}
				$anime_id	= $_POST['anime_id'];
			} else {
				$anime_id	= $player->character()->anime_id;
			}
			if(isset($_POST['character_id']) && is_numeric($_POST['character_id'])) {
				if($_POST['character_id'] != 0) {
					$filter	.= ' AND character_id=' . $_POST['character_id'];						
				}

				$character_id	= $_POST['character_id'];
			} else {
				$character_id = 0;
			}
			
			if(isset($_POST['faction_id']) && is_numeric($_POST['faction_id'])) {
				if($_POST['faction_id'] != 0) {
					$filter	.= ' AND faction_id=' . $_POST['faction_id'];						
				}

				$faction_id	= $_POST['faction_id'];
			} else {
				//$anime_id	= $player->character()->anime_id;
			}
			if(isset($_POST['round']) && strlen(trim($_POST['round']))) {
				$filter	.= ' AND round="' . $_POST['round'].'"';						

				$round	= $_POST['round'];
			} else {
				//$anime_id	= $player->character()->anime_id;
				
				$round	= '';
			}

			if(isset($_POST['name']) && strlen(trim($_POST['name']))) {
				$filter	.= ' AND name LIKE "%' . addslashes($_POST['name']) . '%"';
				$name	= $_POST['name'];
			} else {
				$name	= '';
			}
		}
		$result		= HallOfFame::filter($filter, $page, $limit, $round, $anime_id);
		$animes		= Anime::find('active=1', ['cache' => true]);
		$factions	= Faction::find('1=1', ['cache' => true]);

		$this->assign('player', $player);
		$this->assign('players', $result['players']);
		$this->assign('pages', $result['pages']);
		$this->assign('page', $page);
		$this->assign('anime_id', $anime_id);
		$this->assign('character_id', $character_id);
		$this->assign('faction_id', $faction_id);
		$this->assign('round', $round);
		$this->assign('name', $name);
		$this->assign('animes', $animes);
		$this->assign('factions', $factions);
	}
	function players(){
		$player		= Player::get_instance();
		$page		= isset($_POST['page']) && is_numeric($_POST['page']) ? $_POST['page'] : 0;
		$limit		= 32;
		$filter		= '';
		
		if(!$_POST) {
			$filter			.= ' AND anime_id=' . $player->character()->anime_id;
			$anime_id		= $player->character()->anime_id;
			$character_id	= 0;
			$faction_id		= 0;
			$graduation_id	= 0;
			$name			= '';
		} else {
			if(isset($_POST['anime_id']) && is_numeric($_POST['anime_id'])) {
				if($_POST['anime_id'] != 0) {
					$filter	.= ' AND anime_id=' . $_POST['anime_id'];						
				}

				$anime_id	= $_POST['anime_id'];
			} else {
				$anime_id	= $player->character()->anime_id;
			}
			if(isset($_POST['character_id']) && is_numeric($_POST['character_id'])) {
				if($_POST['character_id'] != 0) {
					$filter	.= ' AND character_id=' . $_POST['character_id'];						
				}

				$character_id	= $_POST['character_id'];
			} else {
				$character_id = 0;
			}
			
			if(isset($_POST['faction_id']) && is_numeric($_POST['faction_id'])) {
				if($_POST['faction_id'] != 0) {
					$filter	.= ' AND faction_id=' . $_POST['faction_id'];						
				}

				$faction_id	= $_POST['faction_id'];
			} else {
				//$anime_id	= $player->character()->anime_id;
			}
			
			if(isset($_POST['graduation_id']) && is_numeric($_POST['graduation_id'])) {
				if($_POST['graduation_id'] != 0) {
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

			if(isset($_POST['name']) && strlen(trim($_POST['name']))) {
				$filter	.= ' AND name LIKE "%' . addslashes($_POST['name']) . '%"';
				$name	= $_POST['name'];
			} else {
				$name	= '';
			}
		}
		$result	= RankingPlayer::filter($filter, $page, $limit);
    $animes	= Anime::find('active=1', ['cache' => true]);
    $factions	= Faction::find('1=1', ['cache' => true]);

		if($anime_id) {
//				$graduations	= Graduation::find('anime_id=' . $anime_id, ['cache' => true]);
			$graduations	= Graduation::all();
		} else {
			$graduations	= [];

			foreach ($animes as $anime) {
//					$grads	= Graduation::find('anime_id=' . $anime->id, ['cache' => true]);
				$grads	= Graduation::find('1=1', ['cache' => true]);

				foreach ($grads as $grad) {
					if(!isset($graduations[$grad->sorting])) {
						$graduations[$grad->sorting]	= ['id' => $grad->sorting, 'name' => []];
					}

					$graduations[$grad->sorting]['name'][]	= $grad->description($anime->id)->name;
				}
			}

			foreach ($graduations as $key => $graduation) {
				$graduations[$key]['name']	= implode(' / ', $graduation['name']);
			}
		}

		$this->assign('player', $player);
		$this->assign('players', $result['players']);
		$this->assign('pages', $result['pages']);
		$this->assign('page', $page);
		$this->assign('anime_id', $anime_id);
		$this->assign('character_id', $character_id);
		$this->assign('faction_id', $faction_id);
		$this->assign('graduation_id', $graduation_id);
		$this->assign('name', $name);
    $this->assign('animes', $animes);
    $this->assign('factions', $factions);
		$this->assign('graduations', $graduations);
	}
	function battles(){
		$player		= Player::get_instance();
		$page		= isset($_POST['page']) && is_numeric($_POST['page']) ? $_POST['page'] : 0;
		$limit		= 32;
		$filter		= '';
		
		if(!$_POST) {
			$filter					.= ' AND anime_id=' . $player->character()->anime_id;
			$anime_id				= $player->character()->anime_id;
			$character_id			= 0;
			$faction_id				= 0;
			$periodo				= "";
			$status					= "";
			$campo 					= "";
			$graduation_id			= 0;
		} else {
			if(isset($_POST['anime_id']) && is_numeric($_POST['anime_id'])) {
				if($_POST['anime_id'] != 0) {
					$filter	.= ' AND anime_id=' . $_POST['anime_id'];						
				}

				$anime_id	= $_POST['anime_id'];
			} else {
				$anime_id	= $player->character()->anime_id;
			}
			if(isset($_POST['character_id']) && is_numeric($_POST['character_id'])) {
				if($_POST['character_id'] != 0) {
					$filter	.= ' AND character_id=' . $_POST['character_id'];						
				}

				$character_id	= $_POST['character_id'];
			} else {
				$character_id = 0;
			}
			
			if(isset($_POST['faction_id']) && is_numeric($_POST['faction_id'])) {
				if($_POST['faction_id'] != 0) {
					$filter	.= ' AND faction_id=' . $_POST['faction_id'];						
				}

				$faction_id	= $_POST['faction_id'];
			} else {
				//$anime_id	= $player->character()->anime_id;
			}
			if(isset($_POST['status']) && is_string($_POST['status'])) {
				switch($_POST['periodo']){
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
			} else {
				//$anime_id	= $player->character()->anime_id;
			}
			
			if(isset($_POST['graduation_id']) && is_numeric($_POST['graduation_id'])) {
				if($_POST['graduation_id'] != 0) {
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
		$result	= PlayerBattleStat::filter($filter, $page, $limit,$campo);
    $animes	= Anime::find('active=1', ['cache' => true]);
    $factions	= Faction::find('1=1', ['cache' => true]);

		if($anime_id) {
			//				$graduations	= Graduation::find('anime_id=' . $anime_id, ['cache' => true]);
			$graduations	= Graduation::all();
		} else {
			$graduations	= [];

			foreach ($animes as $anime) {
				//					$grads	= Graduation::find('anime_id=' . $anime->id, ['cache' => true]);
				$grads	= Graduation::find('1=1', ['cache' => true]);

				foreach ($grads as $grad) {
					if(!isset($graduations[$grad->sorting])) {
						$graduations[$grad->sorting]	= ['id' => $grad->sorting, 'name' => []];
					}

					$graduations[$grad->sorting]['name'][]	= $grad->description($anime->id)->name;
				}
			}

			foreach ($graduations as $key => $graduation) {
				$graduations[$key]['name']	= implode(' / ', $graduation['name']);
			}
		}

		$this->assign('player', $player);
		$this->assign('players', $result['players']);
		$this->assign('pages', $result['pages']);
		$this->assign('page', $page);
		$this->assign('anime_id', $anime_id);
		$this->assign('character_id', $character_id);
		$this->assign('faction_id', $faction_id);
		$this->assign('periodo', $periodo);
		$this->assign('status', $status);
		$this->assign('graduation_id', $graduation_id);
    $this->assign('animes', $animes);
    $this->assign('factions', $factions);
		$this->assign('graduations', $graduations);
	}
	function achievements(){
		$player		= Player::get_instance();
		$page		= isset($_POST['page']) && is_numeric($_POST['page']) ? $_POST['page'] : 0;
		$limit		= 32;
		$filter		= '';

		if(!$_POST) {
			$filter			.= ' AND anime_id=' . $player->character()->anime_id;
			$anime_id		= $player->character()->anime_id;
			$character_id	= 0;
			$faction_id		= 0;
			$graduation_id	= 0;
			$name			= '';
		} else {
			if(isset($_POST['anime_id']) && is_numeric($_POST['anime_id'])) {
				if($_POST['anime_id'] != 0) {
					$filter	.= ' AND anime_id=' . $_POST['anime_id'];						
				}

				$anime_id	= $_POST['anime_id'];
			} else {
				$anime_id	= $player->character()->anime_id;
			}
			if(isset($_POST['character_id']) && is_numeric($_POST['character_id'])) {
				if($_POST['character_id'] != 0) {
					$filter	.= ' AND character_id=' . $_POST['character_id'];						
				}

				$character_id	= $_POST['character_id'];
			} else {
				$character_id = 0;
			}
			
			if(isset($_POST['faction_id']) && is_numeric($_POST['faction_id'])) {
				if($_POST['faction_id'] != 0) {
					$filter	.= ' AND faction_id=' . $_POST['faction_id'];						
				}

				$faction_id	= $_POST['faction_id'];
			} else {
				//$anime_id	= $player->character()->anime_id;
			}
			
			if(isset($_POST['graduation_id']) && is_numeric($_POST['graduation_id'])) {
				if($_POST['graduation_id'] != 0) {
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

			if(isset($_POST['name']) && strlen(trim($_POST['name']))) {
				$filter	.= ' AND name LIKE "%' . addslashes($_POST['name']) . '%"';
				$name	= $_POST['name'];
			} else {
				$name	= '';
			}
		}
		$result	= RankingAchievement::filter($filter, $page, $limit);
    $animes	= Anime::find('active=1', ['cache' => true]);
    $factions	= Faction::find('1=1', ['cache' => true]);

		if($anime_id) {
			//				$graduations	= Graduation::find('anime_id=' . $anime_id, ['cache' => true]);
			$graduations	= Graduation::all();
		} else {
			$graduations	= [];

			foreach ($animes as $anime) {
				//					$grads	= Graduation::find('anime_id=' . $anime->id, ['cache' => true]);
				$grads	= Graduation::find('1=1', ['cache' => true]);

				foreach ($grads as $grad) {
					if(!isset($graduations[$grad->sorting])) {
						$graduations[$grad->sorting]	= ['id' => $grad->sorting, 'name' => []];
					}

					$graduations[$grad->sorting]['name'][]	= $grad->description($anime->id)->name;
				}
			}

			foreach ($graduations as $key => $graduation) {
				$graduations[$key]['name']	= implode(' / ', $graduation['name']);
			}
		}

		$this->assign('player', $player);
		$this->assign('players', $result['players']);
		$this->assign('pages', $result['pages']);
		$this->assign('page', $page);
		$this->assign('anime_id', $anime_id);
		$this->assign('character_id', $character_id);
		$this->assign('faction_id', $faction_id);
		$this->assign('graduation_id', $graduation_id);
		$this->assign('name', $name);
    $this->assign('animes', $animes);
    $this->assign('factions', $factions);
		$this->assign('graduations', $graduations);
	}
	function list_characters(){
		$this->layout	= false;
		//$player			= Player::get_instance();
		
		$this->assign('characters', Character::find("active = 1 AND anime_id=".$_POST['anime_id']));	
		
	}
	function challenges(){
		$player		= Player::get_instance();
		$page		= isset($_POST['page']) && is_numeric($_POST['page']) ? $_POST['page'] : 0;
		$limit		= 32;
		$filter		= '';
		$filter2	= '';

    $animes		= Anime::find('active=1', ['cache' => true]);
    $factions	= Faction::find('1=1', ['cache' => true]);
		$challenges	= Challenge::find('active=1', ['cache' => true]);

		if(!$_POST) {
			$filter			.= ' AND challenge_id=' . $challenges[0]->id;
			$filter2		.= '';
			$anime_id		= 0;
			$faction_id		= 0;
			$challenge_id	= $challenges[0]->id;
			$graduation_id	= 0;
			$name			= '';
		} else {
			if(isset($_POST['anime_id']) && is_numeric($_POST['anime_id'])) {
				if($_POST['anime_id'] != 0) {
					$filter2 .= ' AND anime_id=' . $_POST['anime_id'];						
				}

				$anime_id	= $_POST['anime_id'];
			} else {
				$anime_id	= $player->character()->anime_id;
			}
			
			if(isset($_POST['faction_id']) && is_numeric($_POST['faction_id'])) {
				if($_POST['faction_id'] != 0) {
					$filter	.= ' AND faction_id=' . $_POST['faction_id'];						
				}

				$faction_id	= $_POST['faction_id'];
			} else {
				//$anime_id	= $player->character()->anime_id;
			}
			if(isset($_POST['challenge_id']) && is_numeric($_POST['challenge_id'])) {
				if($_POST['challenge_id'] != 0) {
					$filter	.= ' AND challenge_id=' . $_POST['challenge_id'];
				}

				$challenge_id	= $_POST['challenge_id'];
			} else {
				//$anime_id	= $player->character()->anime_id;
			}
			
			if(isset($_POST['graduation_id']) && is_numeric($_POST['graduation_id'])) {
				if($_POST['graduation_id'] != 0) {
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

			if(isset($_POST['name']) && strlen(trim($_POST['name']))) {
				$filter	.= ' AND name LIKE "%' . addslashes($_POST['name']) . '%"';
				$name	= $_POST['name'];
			} else {
				$name	= '';
			}
		}

		$result		= RankingChallenge::filter($filter,$filter2, $page, $limit);

		if($anime_id) {
			//				$graduations	= Graduation::find('anime_id=' . $anime_id, ['cache' => true]);
			$graduations	= Graduation::all();
		} else {
			$graduations	= [];

			foreach ($animes as $anime) {
				//					$grads	= Graduation::find('anime_id=' . $anime->id, ['cache' => true]);
				$grads	= Graduation::find('1=1', ['cache' => true]);

				foreach ($grads as $grad) {
					if(!isset($graduations[$grad->sorting])) {
						$graduations[$grad->sorting]	= ['id' => $grad->sorting, 'name' => []];
					}

					$graduations[$grad->sorting]['name'][]	= $grad->description($anime->id)->name;
				}
			}

			foreach ($graduations as $key => $graduation) {
				$graduations[$key]['name']	= implode(' / ', $graduation['name']);
			}
		}

		$this->assign('player', $player);
		$this->assign('players', $result['players']);
		$this->assign('pages', $result['pages']);
		$this->assign('page', $page);
		$this->assign('anime_id', $anime_id);
		$this->assign('faction_id', $faction_id);
		$this->assign('challenge_id', $challenge_id);
		$this->assign('graduation_id', $graduation_id);
		$this->assign('name', $name);
    $this->assign('animes', $animes);
    $this->assign('factions', $factions);
		$this->assign('challenges', $challenges);
		$this->assign('graduations', $graduations);
	}
	function rankeds(){
		$player		= Player::get_instance();
		$page		= isset($_POST['page']) && is_numeric($_POST['page']) ? $_POST['page'] : 0;
		$limit		= 32;
		$filter		= '';
		$filter2	= '';

		if(!$_POST) {
			$filter			.= '';
			$filter2		.= '';
			$anime_id		= 0;
			$faction_id		= 0;
			$league_id		= 0;
			$graduation_id	= 0;
			$name			= '';
		} else {
			if(isset($_POST['anime_id']) && is_numeric($_POST['anime_id'])) {
				if($_POST['anime_id'] != 0) {
					$filter2 .= ' AND anime_id=' . $_POST['anime_id'];						
				}

				$anime_id	= $_POST['anime_id'];
			} else {
				$anime_id	= $player->character()->anime_id;
			}
			
			if(isset($_POST['faction_id']) && is_numeric($_POST['faction_id'])) {
				if($_POST['faction_id'] != 0) {
					$filter	.= ' AND faction_id=' . $_POST['faction_id'];						
				}

				$faction_id	= $_POST['faction_id'];
			} else {
				//$anime_id	= $player->character()->anime_id;
			}
			if(isset($_POST['league_id']) && is_numeric($_POST['league_id'])) {
				if($_POST['league_id'] != 0) {
					$filter	.= ' AND league_id=' . $_POST['league_id'];						
				}

				$league_id	= $_POST['league_id'];
			} else {
				//$anime_id	= $player->character()->anime_id;
			}
			
			if(isset($_POST['graduation_id']) && is_numeric($_POST['graduation_id'])) {
				if($_POST['graduation_id'] != 0) {
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

			if(isset($_POST['name']) && strlen(trim($_POST['name']))) {
				$filter	.= ' AND name LIKE "%' . addslashes($_POST['name']) . '%"';
				$name	= $_POST['name'];
			} else {
				$name	= '';
			}
		}

		$result		= RankingRanked::filter($filter,$filter2, $page, $limit);
		$animes		= Anime::find('active=1', ['cache' => true]);
		$leagues	= Ranked::find('started = 1 order by league desc');

		if($anime_id) {
			$graduations	= Graduation::find('anime_id=' . $anime_id, ['cache' => true]);
		} else {
			$graduations	= [];

			foreach ($animes as $anime) {
				$grads	= Graduation::find('anime_id=' . $anime->id, ['cache' => true]);

				foreach ($grads as $grad) {
					if(!isset($graduations[$grad->sorting])) {
						$graduations[$grad->sorting]	= ['id' => $grad->sorting, 'name' => []];
					}

					$graduations[$grad->sorting]['name'][]	= $grad->description()->name;
				}
			}

			foreach ($graduations as $key => $graduation) {
				$graduations[$key]['name']	= implode(' / ', $graduation['name']);
			}
		}

		$this->assign('player', $player);
		$this->assign('players', $result['players']);
		$this->assign('pages', $result['pages']);
		$this->assign('page', $page);
		$this->assign('anime_id', $anime_id);
		$this->assign('faction_id', $faction_id);
		$this->assign('league_id', $league_id);
		$this->assign('graduation_id', $graduation_id);
		$this->assign('name', $name);
		$this->assign('animes', $animes);
		$this->assign('leagues', $leagues);
		$this->assign('graduations', $graduations);
	}
	function organizations(){
		$player		= Player::get_instance();
		$page		= isset($_POST['page']) && is_numeric($_POST['page']) ? $_POST['page'] : 0;
		$limit		= 32;
		$filter		= '';

		if(!$_POST) {
			$filter		   .= ' AND faction_id=' . $player->faction_id;
			$faction_id		= $player->faction_id;
			$name			= '';
		} else {
			if(isset($_POST['faction_id']) && is_numeric($_POST['faction_id'])) {
				if($_POST['faction_id'] != 0) {
					$filter	.= ' AND faction_id=' . $_POST['faction_id'];						
				}

				$faction_id	= $_POST['faction_id'];
			} else {
				//$anime_id	= $player->character()->anime_id;
			}

			if(isset($_POST['name']) && strlen(trim($_POST['name']))) {
				$filter	.= ' AND name LIKE "%' . addslashes($_POST['name']) . '%"';
				$name	= $_POST['name'];
			} else {
				$name	= '';
			}
		}

    $result	= RankingOrganization::filter($filter, $page, $limit);
    $factions	= Faction::find('1=1', ['cache' => true]);

		$this->assign('player', $player);
		$this->assign('players', $result['players']);
		$this->assign('pages', $result['pages']);
		$this->assign('page', $page);
		$this->assign('faction_id', $faction_id);
    $this->assign('name', $name);
    $this->assign('factions', $factions);
	}
	function account(){
		$player		= Player::get_instance();
		$page		= isset($_POST['page']) && is_numeric($_POST['page']) ? $_POST['page'] : 0;
		$limit		= 32;
		$filter		= '';

		if(!$_POST) {
			$filter		   .= '';
			$name			= '';
		} else {
			if(isset($_POST['name']) && strlen(trim($_POST['name']))) {
				$filter	.= ' AND name LIKE "%' . addslashes($_POST['name']) . '%"';
				$name	= $_POST['name'];
			} else {
				$name	= '';
			}
		}

		$result	= RankingAccount::filter($filter, $page, $limit);

		$this->assign('player', $player);
		$this->assign('players', $result['players']);
		$this->assign('pages', $result['pages']);
		$this->assign('page', $page);
		$this->assign('name', $name);
	}
}