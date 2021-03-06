<?php
	class PlayerDailyQuest extends Relation {
		function quest() {
			return DailyQuest::find($this->daily_quest_id, ['cache' => true]);
		}
	}