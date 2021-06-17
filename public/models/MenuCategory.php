<?php
class MenuCategory extends Relation {
	static	$always_cached	= true;

	function menus() {
		return Menu::find('active = 1 and menu_category_id = ' . $this->id, [
			'reorder'	=> 'ordem asc',
			'cache'		=> true
		]);
	}
}
