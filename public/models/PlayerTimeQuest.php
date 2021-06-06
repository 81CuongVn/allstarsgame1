<?php
class PlayerTimeQuest extends Relation {
	function quest() {
		return TimeQuest::find($this->time_quest_id, ['cache' => true]);
	}
}
