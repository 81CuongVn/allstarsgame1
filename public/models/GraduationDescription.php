<?php
class GraduationDescription extends Relation {
	static	$always_cached	= true;

	function graduation() {
		return Graduation::find_first($this->graduation_id);
	}
}