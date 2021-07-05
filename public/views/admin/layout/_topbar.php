<!-- Topbar Start -->
<div class="navbar-custom">
    <ul class="list-unstyled topnav-menu float-right mb-0">
        <li class="d-none d-sm-block">
            <form action="<?=make_url('admin/search');?>" class="app-search">
                <div class="app-search-box">
                    <div class="input-group">
                        <input type="text" name="query" class="form-control" placeholder="Buscar..." />
                        <div class="input-group-append">
                            <button class="btn" type="submit">
                                <i class="fe-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </li>

		<li class="notification-list">
            <span class="nav-link nav-user mr-0">
                <img src="<?=getGravatar($user->email);?>" alt="<?=$user->name;?>" class="rounded-circle" />
                <span class="pro-user-name ml-1">
                    <?=$user->name;?>
                </span>
            </span>
        </li>

        <li class="notification-list">
            <a href="<?=make_url('home')?>" class="nav-link waves-effect waves-light">
                <i class="fe-log-out noti-icon"></i>
            </a>
        </li>
    </ul>

    <!-- LOGO -->
    <div class="logo-box">
        <a href="<?=make_url('admin#home');?>" class="logo text-center">
            <span class="logo-lg">
				<img src="<?=asset_url('admin/images/logo-light.png');?>" alt="" height="18" />
				<!-- <span class="logo-lg-text-light">UBold</span> -->
            </span>
            <span class="logo-sm">
                <!-- <span class="logo-sm-text-dark">U</span> -->
                <img src="<?=asset_url('admin/images/logo-sm.png');?>" alt="" height="24" />
            </span>
        </a>
    </div>
	<ul class="list-unstyled topnav-menu topnav-menu-left m-0">
        <li>
            <button class="button-menu-mobile waves-effect waves-light">
                <i class="fe-menu"></i>
            </button>
        </li>
	</ul>

</div>
<!-- end Topbar -->
