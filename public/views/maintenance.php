<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0' name='viewport'>
	<link rel="shortcut icon" href="<?=image_url('favicon.ico');?>" type="image/x-icon" />

	<title><?=GAME_NAME;?> - Seja o Herói de nossa História</title>
	<meta name="description" content="<?=GAME_NAME;?> é o novo jogo para fãs de anime, em nosso jogo você será um dos personagens emblemáticos dos principais animes que fizeram e fazem parte de nossa vida." />
	<meta name="keywords" content="aasg, naruto, boruto, one, piece, cdz, anime, all, stars, game, jogo, online" />

	<meta property="og:title" content="<?=GAME_NAME;?> - Seja o Herói de nossa História" />
	<meta property="og:site_name" content="<?=GAME_NAME;?>" />
	<meta property="og:url" content="<?=make_url();?>" />
	<meta property="og:description" content="<?=GAME_NAME;?> é o novo jogo para fãs de anime, em nosso jogo você será um dos personagens emblemáticos dos principais animes que fizeram e fazem parte de nossa vida." />
	<meta property="og:type" content="website" />
	<meta property="og:image" content="<?=image_url('social/cover2.png');?>" />

	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/bootstrap.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/tipped.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/layout.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/characters.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/tutorial.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/luck.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/highlights.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/maintenance.css');?>" />
	<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans:400,700" />

	<script type="text/javascript" src="<?=asset_url('js/jquery.js');?>"></script>
	<script type="text/javascript" src="<?=asset_url('js/jquery.ui.js');?>"></script>
	<script type="text/javascript" src="<?=asset_url('js/jquery.devrama.slider.js');?>"></script>
	<script type="text/javascript" src="<?=asset_url('js/jquery.cookie.js');?>"></script>
	<script type="text/javascript" src="<?=asset_url('js/tipped.js');?>"></script>

	<script type="text/javascript">
		var	_site_url			= "<?=$site_url;?>";
		var	_rewrite_enabled	= <?=($rewrite_enabled ? 'true' : 'false');?>;
	</script>
</head>
<body>
<div id="background-topo">
	<div id="logo">
		<a href="<?php echo make_url() ?>">
			<img src="<?php echo image_url('logo.png') ?>" border="0" />
		</a>
	</div>
</div>
<div id="maintenance-content" style="min-height: 350px">
	<?php if (ROUND_END <= date('Y-m-d H:i:s')): ?>
		<h1>Encerramento de Round</h1>
		<h2 style="line-height:18px">
			Parabéns aventureiros, chegamos ao final de mais um round!
			No momento o <b><?php echo GAME_NAME; ?></b> encontra-se fechado para que possamos,
			salvar o hall da fama e iniciar os preparativos do próximo round.<br />
			Fiquem atentos na nossa página do facebook e instagram para receber novidades.
			<br /><br />
			Agradescemos a compeensão!
		</h2>
	<?php else: ?>
		<h1>Em Manutenção</h1>
		<h2 style="line-height:18px">
			Está é uma breve pausa para uma manutenção que esta sendo realizada neste exato momento no jogo.<br />
			Está manutenção irá sanar alguns pequenos bugs que ocorrem durante a fila de PvP e missões de mascote.
		</h2>
	<?php endif ?>
	<?php if (IS_BETA): ?>
		<a href="<?php echo make_url('users#beta') ?>" class="btn btn-primary btn-lg">Cadastre-se para o beta!</a>
		<br />
		<br />
		<hr />
		<!-- REMOVER QUANDO TERMINAR O BETA -->
		<form method="post" id="beta-login-form" class="form form-horizontal" onSubmit="return false">
			<h3>Se você já recebeu seu email de liberação do beta, basta digitar suas informações de login abaixo!</h3>
			<br />
			<div class="form-group">
				<label class="control-label col-md-2"><?php echo t('users.join.labels.email') ?></label>
				<div class="col-md-10">
					<input type="text" class="form-control" placeholder="<?php echo t('users.join.placeholders.email') ?>" name="email" />
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-2"><?php echo t('users.join.labels.password') ?></label>
				<div class="col-md-10">
					<input type="password" name="password" class="form-control" placeholder="<?php echo t('users.join.placeholders.password') ?>" name="password" />
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-2"><?php echo t('users.join.labels.captcha') ?></label>
				<div class="row col-md-10" style="margin-left: 1px">
					<div style="float: left; text-align: center">
						<img id="join-captcha-image" src="<?php echo make_url('captcha/beta_login') ?>" data-image="<?php echo make_url('captcha/beta_login') ?>">
						<br />
						<a id="join-captcha-image-refresh" href="javascript:;"><?php echo t('users.join.labels.captcha_refresh') ?></a>
					</div>
					<div class="col-md-4">
						<input type="text" style="margin-top: 7px" class="form-control" placeholder="<?php echo t('users.join.placeholders.captcha') ?>" name="captcha" />
					</div>
				</div>
			</div>
			<a href="javascript:;" id="beta-login-button" class="btn btn-primary btn-lg">Entrar no jogo!</a>
		</form>
		<!-- REMOVER QUANDO TERMINAR O BETA -->
	<?php endif ?>
</div>
<!-- <?=partial('shared/footer');?> -->
<?php if (IS_BETA) { ?>
	<script type="text/javascript" src="<?php echo asset_url('js/i18n.js') ?>"></script>
	<script type="text/javascript" src="<?php echo asset_url('js/bootstrap.js') ?>"></script>
	<script type="text/javascript" src="<?php echo asset_url('js/bootbox.js') ?>"></script>
	<script type="text/javascript" src="<?php echo asset_url('js/global.js') ?>"></script>
	<script type="text/javascript" src="<?php echo asset_url('js/beta.js') ?>"></script>
<?php } ?>
</body>
</html>