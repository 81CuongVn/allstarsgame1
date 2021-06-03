<?php
class RankedTier extends Relation {
	function description() {
		return RankedTierDescription::find_first('ranked_tier_id = ' . $this->id);
	}
}
