<?php
class MenuCategory extends Relation {
	static	$always_cached	= true;

	function menus() {
		return Menu::find('active=1 AND menu_category_id=' . $this->id.' ORDER BY ordem ASC', array('cache' => true));
	}
}
