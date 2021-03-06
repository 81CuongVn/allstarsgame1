<?php
	class Achievement extends Relation {
		static	$always_cached	= true;

		public function description() {
			return AchievementDescription::find_first('achievement_id=' . $this->id . ' AND language_id=' . $_SESSION['language_id'], array('cache' => true));
		}
		public function player_achievement($player_id, $id){
			return PlayerAchievement::find_first("player_id=".$player_id." AND achievement_id = ". $id);
		}
		public function achievement_rewards($id){
			return AchievementReward::find_first("achievement_id = ". $id);
		}

	}