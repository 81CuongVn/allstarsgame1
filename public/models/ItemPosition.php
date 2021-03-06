<?php
	class ItemPosition extends Relation {
		static $always_cached	= true;

		function anime() {
			return Anime::find($this->anime_id, ['cache' => true]);
		}
	}