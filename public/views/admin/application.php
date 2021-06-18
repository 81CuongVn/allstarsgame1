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
	<link rel="stylesheet" type="text/css" href="<?=asset_url('admin/libs/icomoon/css/icomoon.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('admin/libs/summernote/summernote-bs4.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('admin/libs/select2/select2.min.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('admin/libs/sweetalert2/sweetalert2.min.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('admin/libs/datatables/dataTables.bootstrap4.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('admin/libs/datatables/responsive.bootstrap4.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('admin/libs/datatables/buttons.bootstrap4.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('admin/libs/datatables/select.bootstrap4.css');?>" />

	<!-- App css -->
	<link rel="stylesheet" type="text/css" href="<?=asset_url('admin/css/bootstrap.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('admin/css/icons.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('admin/css/app.css');?>" />

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
	<script src="<?=asset_url('admin/libs/datatables/jquery.dataTables.min.js');?>"></script>
	<script src="<?=asset_url('admin/libs/datatables/dataTables.bootstrap4.js');?>"></script>
	<script src="<?=asset_url('admin/libs/datatables/dataTables.responsive.min.js');?>"></script>
	<script src="<?=asset_url('admin/libs/datatables/responsive.bootstrap4.min.js');?>"></script>
	<script src="<?=asset_url('admin/libs/datatables/dataTables.buttons.min.js');?>"></script>
	<script src="<?=asset_url('admin/libs/datatables/buttons.bootstrap4.min.js');?>"></script>
	<script src="<?=asset_url('admin/libs/datatables/buttons.html5.min.js');?>"></script>
	<script src="<?=asset_url('admin/libs/datatables/buttons.flash.min.js');?>"></script>
	<script src="<?=asset_url('admin/libs/datatables/buttons.print.min.js');?>"></script>
	<script src="<?=asset_url('admin/libs/datatables/dataTables.keyTable.min.js');?>"></script>
	<script src="<?=asset_url('admin/libs/datatables/dataTables.select.min.js');?>"></script>
	<script src="<?=asset_url('admin/libs/pdfmake/pdfmake.min.js');?>"></script>
	<script src="<?=asset_url('admin/libs/pdfmake/vfs_fonts.js');?>"></script>

	<script type="text/javascript">
		var	_site_url				= "<?=$site_url;?>";
		var	_rewrite_enabled		= <?=($rewrite_enabled ? 'true' : 'false');?>;
		var _language				= "<?=$language->header;?>";
	</script>
</head>
<body class="boxed-layouts center-menu">
<!-- Pre-loader -->
<!-- <div id="preloader">
	<div id="status">
		<div class="spinner">Aguarde...</div>
	</div>
</div> -->
<!-- End Preloader-->
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

<!-- App js -->
<script src="<?=asset_url('admin/js/app.min.js');?>"></script>

<script type="text/javascript">
	$(document).ready(() => {
		$.extend($.fn.dataTable.defaults, {
			bLengthChange: true,
			stateSave: true,
			language: {
				sEmptyTable: "Nenhum registro encontrado",
				sInfo: "Exibindo _START_ até _END_ de _TOTAL_ registros",
				sInfoEmpty: "Exibindo 0 até 0 de 0 registros",
				sInfoFiltered: "(Filtrados de _MAX_ registros)",
				sInfoPostFix: "",
				sInfoThousands: ".",
				sLengthMenu: "Exibir _MENU_ resultados.",
				sLoadingRecords: "Carregando...",
				sProcessing: '<i class="fa fa-spinner fa-pulse fa-4x fa-fw"></i>',
				sZeroRecords: "Nenhum registro encontrado",
				sSearch: "Buscar: ",
				oPaginate: {
					sNext: '<i class="mdi mdi-chevron-right">',
					sPrevious: '<i class="mdi mdi-chevron-left">',
					sFirst: "Primeira",
					sLast: "Última"
				},
				oAria: {
					sSortAscending: ": Ordenar colunas de forma ascendente",
					sSortDescending: ": Ordenar colunas de forma descendente"
				}
			},
			drawCallback: () => {
				$('.dataTables_paginate > .pagination').addClass('pagination-rounded');
			}
		});

		$('a[data-toggle="tab"]').click(function (e) {
			e.preventDefault();
			$(this).tab('show');
		});

		$('a[data-toggle="tab"]').on("shown.bs.tab", function (e) {
			var id = $(e.target).attr("href");
			localStorage.setItem('selectedTab', id)
		});

		var selectedTab = localStorage.getItem('selectedTab');
		if (selectedTab != null) {
			$('a[data-toggle="tab"][href="' + selectedTab + '"]').tab('show');
		}

		$(".data").DataTable();
		$('[data-toggle=tooltip]').tooltip({ html: true });
		$('[data-toggle="select2"]').select2();
	});
</script>
</body>
</html>
