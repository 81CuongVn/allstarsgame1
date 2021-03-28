<?php
class PlayerPetQuest extends Relation {
	function quest() {
		return PetQuest::find($this->pet_quest_id, ['cache' => true]);
	}
	function npc() {
		return PetQuestNpc::find('pet_quest_id = '.$this->pet_quest_id, ['cache' => true]);
	}
	function description() {
		return PetQuestDescription::find_first('pet_quest_id=' . $this->pet_quest_id . ' AND language_id=' . $_SESSION['language_id']);
	}
	function pet_wait_diff($pet_quest_id) {
		$player				= Player::get_instance();
		$player_pet_quest	= $player->player_pet_quest_wait($pet_quest_id);
		$diff				= get_time_difference(now(), strtotime($player_pet_quest[0]->finish_at));
		return $diff;
	}
	function pet_wait_can_finish($pet_quest_id) {
		$player				= Player::get_instance();
		$player_pet_quest	= $player->player_pet_quest_wait($pet_quest_id);
		$quest				= PetQuest::find($pet_quest_id);
		$duration			= $quest->duration($quest->durations);
		
		$p_pet_quest = PlayerPetQuest::find_first("completed = 0  AND player_id=".$player->id." AND pet_quest_id=".$pet_quest_id);
		if($player_pet_quest){
			$can_finish			= now() >= strtotime($player_pet_quest[0]->finish_at) ? true : false;
			if($can_finish && !$p_pet_quest->success_at){
				$numero_random = rand(1, 100);
				if($numero_random <= $p_pet_quest->success_percent){
					$p_pet_quest->success = 1;	
				}
					$p_pet_quest->success_at		= date('Y-m-d H:i:s', strtotime('+' . $duration->hours . ' hour, +' . $duration->minutes . ' minute'));
					$p_pet_quest->save();
			}
			
		}else{
			$can_finish = 0;	
		}
		return $can_finish;
	}
	function pet_success($pet_quest_id) {
		$player				= Player::get_instance();
		$p_pet_quest		= PlayerPetQuest::find_first("completed = 0 AND player_id=".$player->id." AND pet_quest_id=".$pet_quest_id);
					
		return $p_pet_quest->success;
	}
	function durations() {
		$durations	= [];

		for ($i = 1; $i <= $this->durations; $i++) { 
			$durations[]	= $this->duration($i);
		}

		return $durations;
	}

	function duration($multiplier) {
		$hours		= substr($this->total_time, 0, 2) * $multiplier;
		$minutes	= ((substr($this->total_time, 3, 5) * 60) * $multiplier) / 60;
		$hours		+= floor($minutes / 60);
		$minutes	-= floor($minutes / 60) * 60;

		$duration				= new stdClass();
		$duration->multiplier	= $multiplier;
		$duration->exp			= $exp;
		$duration->currency		= $currency;
		$duration->hours		= $hours;
		$duration->minutes		= $minutes;
		$duration->seconds		= substr($this->total_time, 6, 8);

		return $duration;
	}

}