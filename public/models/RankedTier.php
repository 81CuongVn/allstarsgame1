<?php
class RankedTier extends Relation {
	function description() {
		return RankedTierDescription::find_first('language_id = ' . $_SESSION['language_id'] . ' and ranked_tier_id = ' . $this->id);
	}
}
