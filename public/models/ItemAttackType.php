<?php
class ItemAttackType extends Relation {
	static	$always_cached	= true;

	function description() {
		return ItemAttackTypeDescription::find_first('item_attack_type_id=' . $this->id . ' AND language_id=' . $_SESSION['language_id'], ['cache' => true]);
	}
}
