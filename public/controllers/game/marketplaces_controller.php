<?php
class MarketplacesController extends Controller {
	public function index() {
		$user	= User::get_instance();
		$player	= Player::get_instance();



		$this->assign('user',	$user);
		$this->assign('player',	$player);
	}
}
