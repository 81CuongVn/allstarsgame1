<div class="topbar-menu">
    <div class="container-fluid">
        <div id="navigation">
            <!-- Navigation Menu-->
			<?php global $raw_menu_data; ?>
			<ul class="navigation-menu">
				<?php foreach ($raw_menu_data as $menu_category) { ?>
					<li class="has-submenu">
						<a href="#">
							<i class="<?=$menu_category['icon'];?>"></i>
							<?=t($menu_category['name']);?>
							<?php if (sizeof($menu_category['menus'])) { ?>
								<div class="arrow-down"></div>
								<ul class="submenu">
									<?php foreach ($menu_category['menus'] as $menu) { ?>
										<li><a href="<?=$menu['href'];?>"<?=($menu['external'] ? ' target="_blank"' : '');?>><?=t($menu['name']);?></a></li>
									<?php } ?>
								</ul>
							<?php } ?>
						</a>
					</li>
				<?php } ?>
			</ul>
            <!-- End navigation menu -->

            <div class="clearfix"></div>
        </div>
        <!-- end #navigation -->
    </div>
    <!-- end container -->
</div>
<!-- end navbar-custom -->
