<?php
class Ranked extends Relation {
	static function isOpen() {
		global $ranked_schedules;

		$ranked_time	= false;
		$ranked			= Ranked::find_first('started = 1 and finished = 0 order by id desc');
		if ($ranked) {
			// Metodo de fila por dia
			// if (date('w') == 0 || date('w') == 2 || date('w') == 4) {
			// 	$ranked_time	= true;
			// }

			// Verifica horario da fila ranqueada
			$schedules		= $ranked_schedules;
			foreach ($schedules as $schedule) {
				$start	= $schedule[0];
				$end	= $schedule[1] - 1;
				if (between(date('H'), $start, $end)) {
					$ranked_time	= true;

					break;
				}
			}
		}

		return $ranked_time;
	}

	function reward($tier) {
		return RankedReward::find_first('ranked_id = ' . $this->id. ' AND ranked_tier_id = ' . $tier);
	}
}
