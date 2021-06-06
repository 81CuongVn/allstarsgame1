<?php
class Faction extends Relation {
	static	$always_cached	= true;

	public function description() {
		return FactionDescription::find_first('faction_id=' . $this->id . ' AND language_id=' . $_SESSION['language_id'], array('cache' => true));
	}

	function image($path_only = false) {
		$path	= "factions/" . $this->id . ".jpg";
		if ($path_only) {
			return $path;
		} else {
			return '<img src="' . image_url($path) . '" />';
		}
	}
}
