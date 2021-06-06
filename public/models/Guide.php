<?php
class Guide extends Relation {
	static	$always_cached	= true;

	function description() {
		return GuideDescription::find_first('guide_id=' . $this->id . ' AND language_id=' . $_SESSION['language_id'], array('cache' => true));
	}
}
