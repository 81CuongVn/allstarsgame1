<?php
class GuidesController extends Controller {
	function game() {
		$categories	= GuideCategory::find("1=1", [
			'cache'		=> true,
			'reorder'	=> 'sort asc'
		]);
		$this->assign('categories',	$categories);
	}
	function character() {
		$user	= User::get_instance();
		$animes	= Anime::find($_SESSION['universal'] ? '1=1 and playable = 1' : 'active = 1 and playable = 1', [
			'cache'		=> true,
			'reorder'	=> 'id asc'
		]);

		$this->assign('user',	$user);
		$this->assign('animes',	$animes);
	}
	function attacks_list() {
		$this->layout	= false;

		$user			= User::get_instance();
		$player			= Player::get_instance();

		$filter = "";
		if (!$_SESSION['universal']) {
			$filter = " and active = 1";
		}
		$character 		= Character::find($_POST['character_id']);

		$this->assign('themes',			CharacterTheme::find('id=' . $_POST['id'] . $filter));
		$this->assign('character',		Character::find($_POST['character_id']));
		$this->assign('abilities',		$character->abilities2());
		$this->assign('specialities',	$character->specialities2());
		$this->assign('user',			$user);
		$this->assign('player',			$player);

	}
	function themes_list() {
		$this->layout	= false;

		$player			= Player::get_instance();
		$user			= User::get_instance();

		$filter	= "";
		if (!$_SESSION['universal']){
			$filter = " and active = 1";
		}
		$this->assign('themes',		CharacterTheme::find('character_id=' . $_POST['id'] . $filter));
		$this->assign('character',	Character::find_first('id=' . $_POST['id']));
		$this->assign('player',		$player);
		$this->assign('user',		$user);
	}
}
