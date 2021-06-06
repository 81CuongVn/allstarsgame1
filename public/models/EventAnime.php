<?php
class EventAnime extends Relation {
	static	$always_cached	= true;

	public function description($id) {
		return AnimeDescription::find_first('anime_id=' . $id . ' AND language_id=' . $_SESSION['language_id'], array('cache' => true));
	}
}
