<?php
$language = Language::find($_SESSION['language_id']);
if (!$language) {
	$_SESSION['language_id'] = 1;
	$language = Language::find($_SESSION['language_id']);
}

if ($_SESSION['user_id']) {
	$user	= User::get_instance();
	if ($user->banned && !$_SESSION['universal']) {
		$user	= FALSE;
		redirect_to('users/logout?banned');
	} elseif (!$user->admin) {
		if (!$_SESSION['player_id']) {
			redirect_to('home');
		} else {
			redirect_to('characters/status');
		}
	}
} else {
	redirect_to('home');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title><?=GAME_NAME;?> - Painel Administrativo</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
	<meta content="Coderthemes" name="author" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />

	<!-- App favicon -->
	<link rel="shortcut icon" href="<?=image_url('favicon.ico');?>" />

	<!-- Plugins css -->
	<link href="<?=asset_url('admin/libs/icomoon/css/icomoon.css');?>" rel="stylesheet" type="text/css" />
	<link href="<?=asset_url('admin/libs/summernote/summernote-bs4.css');?>" rel="stylesheet" type="text/css" />
	<link href="<?=asset_url('admin/libs/select2/select2.min.css');?>" rel="stylesheet" type="text/css" />
	<link href="<?=asset_url('admin/libs/sweetalert2/sweetalert2.min.css');?>" rel="stylesheet" type="text/css" />

	<!-- App css -->
	<link href="<?=asset_url('admin/css/bootstrap.css');?>" rel="stylesheet" type="text/css" />
	<link href="<?=asset_url('admin/css/icons.css');?>" rel="stylesheet" type="text/css" />
	<link href="<?=asset_url('admin/css/app.css');?>" rel="stylesheet" type="text/css" />

	<!-- Vendor js -->
	<script src="<?=asset_url('admin/js/vendor.min.js');?>"></script>

	<!-- Custom js -->
	<script src="<?=asset_url('admin/js/global.js');?>"></script>

	<!-- Plugins js -->
	<script src="<?=asset_url('admin/libs/morris-js/morris.min.js');?>"></script>
	<script src="<?=asset_url('admin/libs/raphael/raphael.min.js');?>"></script>
	<script src="<?=asset_url('admin/libs/summernote/summernote-bs4.min.js');?>"></script>
	<script src="<?=asset_url('admin/libs/summernote/lang/summernote-pt-BR.js');?>"></script>
	<script src="<?=asset_url('admin/libs/select2/select2.min.js');?>"></script>
	<script src="<?=asset_url('admin/libs/sweetalert2/sweetalert2.min.js');?>"></script>

	<script type="text/javascript">
		var	_site_url				= "<?=$site_url;?>";
		var	_rewrite_enabled		= <?=($rewrite_enabled ? 'true' : 'false');?>;
		var _language				= "<?=$language->header;?>";
	</script>
</head>
<body class="boxed-layout center-menu">
<!-- Navigation Bar-->
<header id="topnav">
	<?=partial('layout/topbar', [ 'user' => $user ]);?>

	<?=partial('layout/horizontal-nav', [ 'user' => $user ]);?>
</header>
<!-- End Navigation Bar-->

<div class="wrapper">
	<div class="container-fluid">
		@yield
	</div><!-- end container -->
</div><!-- end wrapper -->

<?=partial('layout/footer');?>

<?=partial('layout/right-sidebar');?>

<!-- App js -->
<script src="<?=asset_url('admin/js/app.min.js');?>"></script>

<script type="text/javascript">
	$(document).ready(() => {
		$('[data-toggle=tooltip]').tooltip({ html: true });
	});
</script>
</body>
</html>
