<!-- ========== Left Sidebar Start ========== -->
<div class="left-side-menu">
    <div class="slimscroll-menu">
        <!-- User box -->
        <div class="user-box text-center">
            <img src="<?=getGravatar($user->email);?>" alt="user-img" title="<?=$user->name;?>" class="rounded-circle avatar-md" />
			<span class="text-dark h5 mt-2 mb-1 d-block">
				<?=$user->name;?>
			</span>
            <p class="text-muted"><?=$user->email;?></p>
        </div>

        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <ul class="metismenu" id="side-menu">
                <li class="menu-title">Navegação</li>
				<!-- Navigation Menu-->
				<?php global $raw_menu_data; ?>
				<?php foreach ($raw_menu_data as $menu_category) { ?>
					<li>
						<a href="javascript: void(0);">
							<i class="<?=$menu_category['icon'];?>"></i>
							<span> <?=t($menu_category['name']);?> </span>
							<?php if (sizeof($menu_category['menus'])) { ?>
                        		<span class="menu-arrow"></span>
							<?php } ?>
						</a>
						<?php if (sizeof($menu_category['menus'])) { ?>
							<ul class="nav-second-level" aria-expanded="false">
								<?php foreach ($menu_category['menus'] as $menu) { ?>
									<li><a href="<?=$menu['href'];?>"<?=($menu['external'] ? ' target="_blank"' : '');?>><?=t($menu['name']);?></a></li>
								<?php } ?>
							</ul>
						<?php } ?>
					</li>
				<?php } ?>
            </ul>
        </div>
       <!-- End Sidebar -->
        <div class="clearfix"></div>
    </div>
    <!-- Sidebar -left -->
</div>
<!-- Left Sidebar End -->
