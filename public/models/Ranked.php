<?php
class Ranked extends Relation {
    function reward($tier) {
		return RankedReward::find_first('ranked_id = ' . $this->id. ' AND ranked_tier_id = ' . $tier);
	}
}
