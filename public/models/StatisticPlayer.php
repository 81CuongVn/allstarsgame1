<?php
	class StatisticPlayer extends Relation {
		function character() {
			return Character::find($this->character_id, array('cache' => true));
		}


	}