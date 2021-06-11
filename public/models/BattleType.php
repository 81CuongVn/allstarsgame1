<?php
class BattleType extends Relation {
	static	$always_cached	= true;

	function description() {
		return BattleTypeDescription::find_first('battle_type_id=' . $this->id . ' AND language_id=' . $_SESSION['language_id'], ['cache' => true]);
	}
}
