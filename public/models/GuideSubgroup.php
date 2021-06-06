<?php
class GuideSubgroup extends Relation {
	static	$always_cached	= true;

	function description() {
		return GuideSubgroupDescription::find_first('guide_subgroup_id=' . $this->id . ' AND language_id=' . $_SESSION['language_id'], array('cache' => true));
	}
}
