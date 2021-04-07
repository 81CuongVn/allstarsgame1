<?php
class AchievementsController extends Controller {
	function index() {
		$this->assign('categories', AchievementCategory::find("language_id=" . $_SESSION['language_id'] . " ORDER BY ordem ASC"));
	}
	
	function make_list() {
		$this->layout			= false;
		$this->json->success	= true;

		$achievement_id	= isset($_POST['achievement_id']) ? $_POST['achievement_id'] : 1;
		$achievement	= AchievementCategory::find_first($achievement_id);
		$achievement_id	= AchievementCategory::find_first('ordem = ' . $achievement->ordem)->id;
		$player			= Player::get_instance();
		$user			= User::get_instance();

		$this->assign('player',			$player);
		$this->assign('user',			$user);
		$this->assign('achievements',	Achievement::find("type='achievement' AND achievement_category_id=" . $achievement_id . " ORDER BY ordem ASC"));
	
	}
}
?>	