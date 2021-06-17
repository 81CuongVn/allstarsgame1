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
    <link rel="stylesheet" type="text/css" href="<?=asset_url('css/beta.css');?>" />
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
<div id="beta-container">
    @yield
</div>
<?=partial('shared/footer', ['player' => $player]);?>
<script type="text/javascript" src="<?php echo asset_url('js/i18n.js') ?>"></script>
<script type="text/javascript" src="<?php echo asset_url('js/bootstrap.js') ?>"></script>
<script type="text/javascript" src="<?php echo asset_url('js/bootbox.js') ?>"></script>
<script type="text/javascript" src="<?php echo asset_url('js/global.js') ?>"></script>
<script type="text/javascript" src="<?php echo asset_url('js/beta.js') ?>"></script>
</body>
</html>