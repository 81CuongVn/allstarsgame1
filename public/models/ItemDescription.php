<?php
class ItemDescription extends Relation {
	static	$always_cached	= true;

	function item() {
		return Item::find_first('id=' . $this->item_id);
	}
}
