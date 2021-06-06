<?php
class GuideCategory extends Relation {
	static	$always_cached	= true;

	function description() {
		return GuideCategoryDescription::find_first('guide_category_id=' . $this->id . ' AND language_id=' . $_SESSION['language_id'], array('cache' => true));
	}
}
