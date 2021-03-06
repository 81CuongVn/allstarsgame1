<?php
class PlayerStarItem extends Relation {
	function characters($character_id = NULL) {
		return Character::find_first('id=' . $character_id);
	}
	function description() {
		return CharacterDescription::find_first('character_id=' . $this->id . ' AND language_id=' . $_SESSION['language_id']);
	}
	function item() {
		return Item::find_first('id = ' . $this->item_id);
	}
	function player() {
		return Player::find_first('id = ' . $this->player_id);
	}
}