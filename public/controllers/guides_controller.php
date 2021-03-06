<?php
	class GuidesController extends Controller {
		function game() {
			$this->assign('categories', GuideCategory::find("1=1 ORDER BY ordem ASC"));
		}
		function character() {
			$user	= User::get_instance();
			$animes	= Anime::find($_SESSION['universal'] ? '1=1 AND playable=1' : 'active=1 AND playable=1', ['cache' => true, 'reorder' => 'id ASC']);

			$this->assign('user', $user);
			$this->assign('animes', $animes);
		}
		function attacks_list(){
			$user	= User::get_instance();
			$player	= Player::get_instance();
			
			$filter = "";
			if(!$_SESSION['universal']){
				$filter = " AND active=1";
			}
			$character 				= Character::find($_POST['character_id']);
			$this->layout			= false;
			
			$this->assign('themes', CharacterTheme::find('id=' . $_POST['id'] . $filter));
			$this->assign('character', Character::find($_POST['character_id']));
			$this->assign('abilities', $character->abilities2());
			$this->assign('specialities', $character->specialities2());
			$this->assign('user', $user);
			$this->assign('player', $player);

		}
		function themes_list(){
			$filter = "";
			$player	= Player::get_instance();
			$user	= User::get_instance();
			if(!$_SESSION['universal']){
				$filter = " AND active=1";
			}
			$this->layout			= false;
			$this->assign('themes', CharacterTheme::find('character_id=' . $_POST['id'] . $filter));
			$this->assign('character', Character::find_first('id=' . $_POST['id']));
			$this->assign('player', $player);
			$this->assign('user', $user);

		}
	}