<?php
	class CharacterThemeItem extends Relation {
		static	$always_cached	= true;

		function item() {
			return Item::find($this->item_id, array('cache' => true));
		}
	}