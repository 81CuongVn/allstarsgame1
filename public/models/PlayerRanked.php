<?php
class PlayerRanked extends Relation {
	function ranked() {
		return Ranked::find_first('id = ' . $this->ranked_id);
	}

	function tier() {
		return RankedTier::find_first('id = ' . $this->ranked_tier_id);
	}

	function update() {
		$changed	= false;

		// Corrige pontuação negativa
		if ($this->points < 0) {
			$changed	= true;

			$this->points = 0;
		}

		// Verifica se sobe, cai ou mantem no mesmo rank
		$ranked	= $this->ranked();
		if (!$ranked->finished) {
			$tier	= $this->tier();
			if ($tier->min_points && $this->points <= $tier->min_points) {
				$new_tier	= RankedTier::find_last('sort = ' . ($tier->sort + 1));
			} elseif ($tier->max_points && $this->points >= $tier->max_points) {
				$new_tier	= RankedTier::find_last('sort = ' . ($tier->sort - 1));
			} else {
				$new_tier	= false;
			}

			if ($new_tier) {
				$changed	= true;

				$this->ranked_tier_id	= $new_tier->id;
			}
		}

		if ($changed) {
			$this->save();
		}
	}
}
