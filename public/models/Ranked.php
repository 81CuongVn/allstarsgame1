<?php
class Ranked extends Relation {
	public	$schedules	= [
		[ '10', '12' ],		// 10h at 12h
		[ '16', '18' ],		// 16h at 18h
		[ '22', '00' ]		// 22h at 00h
	];

	static function isOpen() {
		$ranked_time	= false;
		$ranked			= Ranked::find_first('started = 1 and finished = 0 order by id desc');
		if ($ranked) {
			// Metodo de fila por dia
			// if (date('w') == 0 || date('w') == 2 || date('w') == 4) {
			// 	$ranked_time	= true;
			// }

			// Verifica horario da fila ranqueada
			$schedules		= $ranked->schedules;
			foreach ($schedules as $schedule) {
				$start	= $schedule[0];
				$end	= $schedule[1];
				if (between(date('H'), $start, ($end - 1))) {
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
