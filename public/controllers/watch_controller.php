<?php
class WatchController extends Controller {
	public function index() {
		$player		= Player::get_instance();
		$battles	= BattlePvp::find('finished_at is null');

		$this->assign('battles',    $battles);
		$this->assign('player', 	$player);
	}
	public function battle($battle_id) {
		$battle	= BattlePvp::find($battle_id);
		if (!$battle) { redirect_to(); }

		$player		   = Player::find($battle->player_id);
		$enemy		   = Player::find($battle->enemy_id);

		$player_wanted = PlayerWanted::find_first("player_id = " . $player->id . " and death = 0");
		$enemy_wanted  = PlayerWanted::find_first("player_id = " . $enemy->id . " and death = 0");

		$this->assign('battle',			$battle);
		$this->assign('player',			$player);
		$this->assign('player_wanted',	$player_wanted);
		$this->assign('enemy',			$enemy);
		$this->assign('enemy_wanted',	$enemy_wanted);
		$this->assign('target_url',		make_url('battle_pvps'));
	}
	public function ping($battle_id) {

	}
}