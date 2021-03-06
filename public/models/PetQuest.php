<?php
	class PetQuest extends Relation {
		static	$always_cached	= true;
		
		function description() {
			return PetQuestDescription::find_first('pet_quest_id=' . $this->id . ' AND language_id=' . $_SESSION['language_id']);
		}
		function anime() {
			return Anime::find($this->anime_id, array('cache' => true));
		}
		function durations() {
			$durations	= [];

			for ($i = 1; $i <= $this->durations; $i++) { 
				$durations[]	= $this->duration($i);
			}

			return $durations;
		}

		function duration($multiplier) {
			$exp		= (200) * $multiplier;
			$currency	= floor($exp / 2);

			$duration				= new stdClass();
			$duration->multiplier	= $multiplier;
			$duration->exp			= $exp;
			$duration->currency		= $currency;
			$duration->total_time	= $this->total_time * $multiplier;
			$duration->time			= format_time($duration->total_time);

			return $duration;
		}
	}