<ul class="nav nav-pills nav-justified" style="margin-bottom: 15px;">
	<?php $quests_menu = Menu::find('id in (74, 87, 124, 136, 209, 232) and active = 1', [ 'reorder' => 'ordem asc' ]); ?>
	<?php $count = 1; ?>
	<?php foreach ($quests_menu as $menu) { ?>
		<?php if (is_menu_accessible($menu, $player)) { ?>
			<li class="<?=(is_menu_active($menu->href) ? 'active' : '');?>"><a href="<?=make_url($menu->href);?>"><?=t($menu->name);?></a></li>
			<?php if ($count == 2) { ?>
				<!-- <br /> -->
			<?php } ?>
		<?php } ?>
		<?php ++$count; ?>
	<?php } ?>
</ul>
