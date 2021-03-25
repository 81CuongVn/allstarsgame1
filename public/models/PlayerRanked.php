<?php
class PlayerRanked extends Relation {
    function up_points($rank) {
        return ranked_up_points($rank);
    }

	function down_points($rank) {
        return ranked_down_points($rank);
    }

	function league() {
		return Ranked::find_first('league = ' . $this->league);
	}

	function points() {
		$points = $this->wins * 4;
		$points += $this->draws * 2;
		$points -= $this->losses * 1;

		return $points;
	}

	function update() {
		$league = $this->league();
		if (!$league->finished) {
			$points = $this->points();
			if ($this->rank > 0 && $points >= $this->up_points($this->rank)) {
				--$this->rank;
			} elseif ($this->rank < 10 && $points <= $this->down_points($this->rank)) {
				++$this->rank;
			}
			$this->save();
		}
	}
}