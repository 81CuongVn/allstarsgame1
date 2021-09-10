<?php
class ProfileController extends Controller {
	public function index() {
		$player		= Player::get_instance();

		$profile	= false;
		if (is_numeric($_GET['player'])) {
			$profile	= Player::find_first($_GET['player']);
		}

		if ($profile) {
			if ($profile->id == $player->id) {
				redirect_to('charaacters#status');
			}

			// Espionagem de Atributos
			if (($seeAttributes = $player->has_vip_item(2113))) {
				$player->use_vip_item($seeAttributes->id);
			}

			// Anti-Espionagem
			if (($antSpy = $profile->has_vip_item(2116))) {
				$profile->use_vip_item($antSpy->id);
			}

			$formulas	= [
				'for_atk'		=> t('formula.for_atk'),
				'for_def'		=> t('formula.for_def'),
				'for_crit'		=> t('formula.for_crit'),
				// 'for_crit_inc'	=> t('formula.for_inc_crit'),
				'for_abs'		=> t('formula.for_abs'),
				// 'for_abs_inc'	=> t('formula.for_inc_abs'),
				'for_prec'		=> t('formula.for_prec'),
				'for_init'		=> t('formula.for_init')
			];

			$max	= 0;
			if ($seeAttributes && !$antSpy) {
				foreach ($formulas as $_ => $formula) {
					$value	= $profile->{$_}();
					if ($value > $max) {
						$max	= $value;
					}
				}
			}

			$this->assign('user_quest_counters',	$profile->user()->quest_counters());
			$this->assign('quest_counters',			$profile->quest_counters());
			$this->assign('formulas',				$formulas);
			$this->assign('max',					$max);

			$this->assign('antSpy',					$antSpy);
			$this->assign('seeAttributes',			$seeAttributes);
		}

		$this->assign('player',		$player);
		$this->assign('profile',	$profile);
	}

	public function achievements() {
		$player		= Player::get_instance();

		$profile	= false;
		if (is_numeric($_GET['player'])) {
			$profile	= Player::find_first($_GET['player']);
		}

		if ($profile) {
			if ($profile->id == $player->id) {
				redirect_to('achievements');
			}

			$categories = AchievementCategory::find("1=1", [
				'reorder'	=> 'sort asc'
			]);

			$this->assign('categories',	$categories);
		}

		$this->assign('player',		$player);
		$this->assign('profile',	$profile);
	}
	public function achievements_list() {
		$this->layout			= false;
		$this->json->success	= true;

		$player		= Player::get_instance();

		$profile	= false;
		if (is_numeric($_GET['player'])) {
			$profile	= Player::find_first($_GET['player']);
		}

		if ($profile) {
			$achievement_id	= isset($_POST['achievement_id']) ? $_POST['achievement_id'] : 1;
			$user_profile	= $profile->user();
			$achievements	= Achievement::find("type = 'achievement' and achievement_category_id = " . $achievement_id, [
				'reorder'	=> 'sort asc'
			]);

			$this->assign('user_profile',	$user_profile);
			$this->assign('achievements',	$achievements);
		}

		$this->assign('player',		$player);
		$this->assign('profile',	$profile);
	}

	public function talents() {
		$player		= Player::get_instance();

		$profile	= false;
		if (is_numeric($_GET['player'])) {
			$profile	= Player::find_first($_GET['player']);
		}

		if ($profile) {
			if ($profile->id == $player->id) {
				redirect_to('charaacters#talents');
			}

			// Espionagem de Talentos
			if (($seeTalents = $player->has_vip_item(2114))) {
				$player->use_vip_item($seeTalents->id);
			}

			// Anti-Espionagem
			if (($antSpy = $profile->has_vip_item(2116))) {
				$profile->use_vip_item($antSpy->id);
			}

			if ($seeTalents && !$antSpy) {
				$items	= Item::find("item_type_id = 6", [
					'reorder'	=> 'mana_cost asc'
				]);

				$list	= [];
				foreach ($items as $item) {
					$lvl	= $item->mana_cost;
					if (!isset($list[$lvl])) {
						$list[$lvl]	= [];
					}

					$item->set_anime($profile->character()->anime_id);

					$list[$lvl][]	= $item;
				}

				$this->assign('list',			$list);
				$this->assign('profile_user',	$profile->user());
			}

			$this->assign('antSpy',		$antSpy);
			$this->assign('seeTalents',	$seeTalents);
		}

		$this->assign('player',		$player);
		$this->assign('profile',	$profile);
	}

	public function equipments() {
		$player		= Player::get_instance();

		$profile	= false;
		if (is_numeric($_GET['player'])) {
			$profile	= Player::find_first($_GET['player']);
		}

		if ($profile) {
			if ($profile->id == $player->id) {
				redirect_to('charaacters#status');
			}

			// Espionagem de Equipamentos
			if (($seeEquipments = $player->has_vip_item(2115))) {
				$player->use_vip_item($seeEquipments->id);
			}

			// Anti-Espionagem
			if (($antSpy = $profile->has_vip_item(2116))) {
				$profile->use_vip_item($antSpy->id);
			}

			$anime	= $profile->character()->anime();

			$this->assign('positions',		$anime->equipment_positions());
        	$this->assign('anime',			$anime);

			$this->assign('antSpy',			$antSpy);
			$this->assign('seeEquipments',	$seeEquipments);
		}

		$this->assign('player',		$player);
		$this->assign('profile',	$profile);
	}
}
