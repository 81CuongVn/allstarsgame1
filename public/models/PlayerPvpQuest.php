<?php
	class PlayerPvpQuest extends Relation {
		function quest() {
			return PvpQuest::find($this->pvp_quest_id, ['cache' => true]);
		}
	}