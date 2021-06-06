<?php
class LuckReward extends Relation {
	static	$always_cache	= true;

	public function luck_reward_log($id, $player) {
		return PlayerLuckLog::find_first('player_id='. $player.' AND luck_reward_id=' . $id . ' AND type=3');
	}
}
