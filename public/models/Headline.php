<?php
class Headline extends Relation {
	static	$always_cached	= true;

	function description() {
		$lang_key			= Language::find($_SESSION['language_id'])->header_mini;

		$description		= new stdClass();
		$description->name	= $this->{'name_' . $lang_key};

		return $description;
	}
}
