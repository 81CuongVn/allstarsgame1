<?php
class TimeQuest extends Relation {
	static	$always_cached	= TRUE;
	private	$_anime_id		= 0;

	function description() {
		return TimeQuestDescription::find_first('time_quest_id=' . $this->id . ' AND anime_id=' . $this->anime_id . ' AND language_id=' . $_SESSION['language_id']);
	}

	function durations() {
		$durations	= [];

		for ($i = 1; $i <= $this->durations; $i++) { 
			$durations[]	= $this->duration($i);
		}

		return $durations;
	}

	function duration($multiplier) {
		$exp		= (200 + ($this->req_graduation_sorting * 100) + ($this->req_level * 10)) * $multiplier;
		$currency	= floor($exp / 2);

		$duration				= new stdClass();
		$duration->multiplier	= $multiplier;
		$duration->exp			= $exp * EXP_RATE;
		$duration->currency		= $currency * MONEY_RATE;
		$duration->total_time	= $this->total_time * $multiplier;
		$duration->time			= format_time($duration->total_time);

		return $duration;
	}
}