<?php
class UserDailyQuest extends Relation {
	function quest() {
		return DailyQuest::find($this->daily_quest_id, ['cache' => true]);
	}
}
