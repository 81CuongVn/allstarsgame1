<?php
class AchievementsController extends Controller
{
	public function index()
	{
		$categories = AchievementCategory::find("1=1", [
			'reorder'	=> 'sort asc'
		]);

		$this->assign('categories',	$categories);
	}
	public function make_list()
	{
		$this->layout			= false;
		$this->json->success	= true;

		$achievement_id	= isset($_POST['achievement_id']) ? $_POST['achievement_id'] : 1;
		$player			= Player::get_instance();
		$user			= User::get_instance();
		$achievements	= Achievement::find("type = 'achievement' and achievement_category_id = " . $achievement_id, [
			'reorder'	=> 'sort asc'
		]);

		$this->assign('player',			$player);
		$this->assign('user',			$user);
		$this->assign('achievements',	$achievements);
	}
}
