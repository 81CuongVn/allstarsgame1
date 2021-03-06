<?php
	class PetQuestNpc extends Relation {
		static $always_cached	= true;
		function anime() {
			return Anime::find($this->anime_id, array('cache' => true));
		}
	}