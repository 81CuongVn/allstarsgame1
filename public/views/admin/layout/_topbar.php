<!-- Topbar Start -->
<div class="navbar-custom">
    <div class="container-fluid">
        <ul class="list-unstyled topnav-menu float-right mb-0">
            <li class="dropdown notification-list">
                <!-- Mobile menu toggle-->
                <a class="navbar-toggle nav-link">
                    <div class="lines">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </a>
                <!-- End mobile menu toggle-->
            </li>

            <li class="d-none d-sm-block">
                <form class="app-search">
                    <div class="app-search-box">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Buscar...">
                            <div class="input-group-append">
                                <button class="btn" type="submit">
                                    <i class="fe-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </li>
            <li class="dropdown notification-list">
                <span class="nav-link nav-user mr-0" href="javascript:void(0);">
                    <span class="pro-user-name ml-1">
                        Olá, <b><?=$user->name?></b>!
                    </span>
                </span>
            </li>

            <li class="dropdown notification-list">
                <a href="<?=make_url('home')?>" class="nav-link waves-effect">
                    <i class="fe-log-out"></i>
                </a>
            </li>

        </ul>

        <!-- LOGO -->
        <div class="logo-box">
            <a href="<?=make_url('admin#home');?>" class="logo text-center">
                <span class="logo-lg">
                    <span class="logo-lg-text-dark">Painel Administrativo</span>
                </span>
                <span class="logo-sm">
                    <span class="logo-sm-text-dark">Admin</span>
                </span>
            </a>
        </div>
    </div> <!-- end container-fluid-->
</div>
<!-- end Topbar -->
