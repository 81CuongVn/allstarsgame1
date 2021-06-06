<?php
class AchievementCategory extends Relation {
	static	$always_cached	= true;

	function description() {
		return AchievementCategoryDescription::find_first('achievement_category_id=' . $this->id . ' AND language_id=' . $_SESSION['language_id'], array('cache' => true));
	}
}
