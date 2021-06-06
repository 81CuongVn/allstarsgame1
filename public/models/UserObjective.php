<?php
class UserObjective extends Relation {
	public function description() {
		return AchievementDescription::find_first('achievement_id=' . $this->objective_id . ' AND language_id=' . $_SESSION['language_id'], array('cache' => true));
	}

	public function achievement() {
		return Achievement::find_first('id=' . $this->objective_id, array('cache' => true));
	}
}
