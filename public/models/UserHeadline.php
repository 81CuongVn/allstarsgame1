<?php
class UserHeadline extends Relation {
	function headline() {
		return Headline::find($this->headline_id, ['cache' => true]);
	}
}
