<?php
class Ranked extends Relation {
    function up_points($rank) {
        return ranked_up_points($rank);
    }

	function down_points($rank) {
        return ranked_down_points($rank);
    }

    function reward($rank) {
		return RankedReward::find_first('league=' . $this->league. ' AND rank=' . $rank);
	}
}