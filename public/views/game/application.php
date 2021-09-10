<?php
$user			= false;
$player			= false;
$article		= false;
$with_battle	= false;
$is_profile		= false;

$language = Language::find($_SESSION['language_id']);
if (!$language) {
	$_SESSION['language_id'] = 1;
	$language = Language::find($_SESSION['language_id']);
}

if ($_SESSION['user_id']) {
	$user	= User::get_instance();
	if ($_SESSION['player_id']) {
		$player	= Player::get_instance();

		// Tras informações sobre a fidelidade
		$player_fidelity_topo = PlayerFidelity::find_first("player_id=".$player->id);

		// Verifica se está em batalha
		if ($player && ($player->battle_npc_id || $player->battle_pvp_id) && preg_match('/battle/', $controller)) {
			$with_battle	= true;
		}

		// Tras o nome dos ataques do anime/tema
		$equipments		= [];
		$techniques		= $player->character_theme()->attacks();
		foreach ($techniques as $technique) {
			$equipments[$technique->id]	= $technique->description()->name;
		}
	}
}

// Ta vendo um pefil?
if (preg_match('/profile/', $controller)) {
	$is_profile		= true;
}

// Verifica se o link é de noticia
if (preg_match('/read_news/', $action)) {
	$article_id = $params[0];
	$article = SiteNew::find_first('id = ' . $article_id);
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0' name='viewport'>
	<link rel="shortcut icon" href="<?=image_url('favicon.ico');?>" type="image/x-icon" />

    <title><?=GAME_NAME;?> - Seja o Herói de nossa História</title>
    <meta name="description" content="<?=GAME_NAME;?> é o novo jogo para fãs de anime, em nosso jogo você será um dos personagens emblemáticos dos principais animes que fizeram e fazem parte de nossa vida." />
    <meta name="keywords" content="aasg, naruto, boruto, one, piece, cdz, anime, all, stars, game, jogo, online" />
	<?php if (!preg_match('/read_news/', $action)) { ?>

	<meta property="og:url" content="<?=make_url('/')?>" />
	<meta property="og:type" content="website" />
	<meta property="og:title" content="<?=GAME_NAME;?> - Seja o Herói de nossa História" />
	<meta property="og:description" content="<?=GAME_NAME;?> é o novo jogo para fãs de anime, em nosso jogo você será um dos personagens emblemáticos dos principais animes que fizeram e fazem parte de nossa vida." />
	<?php } else { ?>

	<meta property="og:url" content="<?=make_url('home#read_news/' . $article->id)?>" />
	<meta property="og:title" content="<?=$article->title;?>" />
	<meta property="og:description" content="<?=str_limit(strip_tags($article->description), 100, '');?>" />
	<meta property="og:type" content="article" />
	<meta property="article:author" content="<?=$article->user()->name;?>" />
	<meta property="article:section" content="<?=$article->type;?>" />
	<meta property="article:published_time" content="<?=$article->created_at;?>" />
	<?php } ?>

	<meta property="og:image" itemprop="image" content="<?=image_url('social/cover.jpg');?>" />
	<meta property="og:locale" content="<?=str_replace('-', '_', $language->header);?>" />
	<meta property="fb:app_id" content="<?=FB_APP_ID;?>" />

	<!-- PWA -->
	<meta name="theme-color" content="#06101a" />
	<link rel="manifest" href="/manifest.json" />
	<link ref="apple-touch-icon" href="/icon-192x192.png" />
	<link ref="canonical" href="<?=$site_url;?>/" />

	<!-- CSS -->
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/bootstrap.min.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/bootstrap-theme.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/select2.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/select2-bootstrap.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/animate.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/anivers.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/inventory.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/battle.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/chat.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/quests.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/equipments.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/typeahead.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/techniques.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/history_mode.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/events.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/tournaments.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/layout.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/characters.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/tutorial.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/luck.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/highlights.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/font-awesome.min.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/jquery.bracket.min.css');?>" />
	<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans:400,700" />

	<!-- JS -->
	<script type="text/javascript" src="<?=asset_url('js/jquery.js');?>"></script>
	<script type="text/javascript" src="<?=asset_url('js/jquery.ui.js');?>"></script>
	<script type="text/javascript" src="<?=asset_url('js/jquery.ui.touch-punch.min.js');?>"></script>
	<script type="text/javascript" src="<?=asset_url('js/jquery.devrama.slider.js');?>"></script>
	<script type="text/javascript" src="<?=asset_url('js/jquery.cookie.js');?>"></script>
	<script type="text/javascript" src="<?=asset_url('js/jquery.bracket.min.js');?>"></script>
    <script type="text/javascript" src="<?=asset_url('js/i18n.js');?>"></script>
    <script type="text/javascript" src="<?=asset_url('js/socket.io.js');?>"></script>
	<script type="text/javascript">
		var	_site_url				= "<?=$site_url;?>";
		var	_site_version			= "<?=GAME_VERSION;?>";
		var	_rewrite_enabled		= <?=($rewrite_enabled ? 'true' : 'false');?>;
		var _language				= "<?=$language->header;?>";
		<?php if ($player) { ?>

		var _current_anime			= <?=$player->character()->anime_id;?>,
			_current_graduation		= <?=$player->graduation()->sorting;?>,
			_current_guild			= <?=$player->guild_id;?>,
			_current_player			= <?=$player->id;?>,
			_is_guild_leader		= <?=(($player->guild_id && $player->guild_id == $player->guild()->player_id) ? 'true' : 'false');?>,
			_equipments_names		= <?=json_encode($equipments);?>,
			_graduations			= [];
		<?php } ?>

		var	_check_pvp_queue		= <?=($player && $player->is_pvp_queued ? 'true': 'false');?>;
		var _highlights_server		= "<?=HIGHLIGHTS_SERVER;?>";

		$(document).ready(function() {
        	I18n.default_locale		= _language;
        	I18n.translations		= <?=Lang::toJSON()?>;
		});
    </script>
	<style type="text/css">
		.grecaptcha-badge { z-index: 1; }
	</style>
	<?php if (FW_ENV != 'dev') { ?>
		<script data-ad-client="ca-pub-6665062829379662" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
	<?php } ?>
</head>
<body>
<script type="text/javascript">
	if ('serviceWorker' in navigator) {
		navigator.serviceWorker.register('/sw.js')
			.then(function(registration) {
				console.log('Registration successful, scope is:', registration.scope);
			})
			.catch(function(error) {
				console.log('Service worker registration failed, error:', error);
			});
	}
</script>
<div id="fb-root"></div>
<!-- Topo -->
<?php if (!$_SESSION['player_id']) { ?>
<div id="background-topo">
	<div id="logo">
		<a href="<?=make_url();?>">
			<img src="<?=image_url('logo.png');?>" border="0" />
		</a>
	</div>
</div>
<?php } else { ?>
	<div id="background-topo2" style="background-image: url(<?=image_url($player->character_theme()->header_image(true));?>)">
		<div class="bg" style="background-image: url(<?=image_url($player->character_theme()->header_image(true));?>)"></div>
		<div class="info">
			<?=top_exp_bar($player, $user);?>
		</div>
		<div class="menu">
			<?php if ($_SESSION['universal'] && $_SESSION['orig_user_id']) { ?>
				<a style="position: absolute; right: 160px; bottom: 135px" class="btn btn-xs btn-primary" href="<?=make_url('support#revert');?>">
					Reverter para usuário original
				</a>
			<?php } ?>
			<div class="values">
				<div class="life absolute"><span class="c"><?=highamount($player->for_life());?></span></div>
				<div class="mana absolute"><span class="c"><?=highamount($player->for_mana());?></span></div>
				<?php
					$staminaPercent = floor(($player->for_stamina() / $player->for_stamina(true)) * 100);
					if ($staminaPercent <= 33)								$staminaColor = "vermelho";
					else if ($staminaPercent > 34 && $staminaPercent <= 66)	$staminaColor = "laranja";
					else													$staminaColor = "verde";
				?>
				<div style="cursor: pointer;" class="stamina absolute requirement-popover" data-source="#tooltip-stamina" data-title="Recuperação de Stamina" data-trigger="hover" data-placement="bottom">
					<div id="tooltip-stamina" class="status-popover-container">
						<div class="status-popover-content">
							<div class="item-vip-list">
								<form id="vip-form-431" onsubmit="return false">
									<input type="hidden" name="id" value="431" />
									<button type="button" class="btn btn-primary btn-sm btn-block buy" data-id="431" style="margin-bottom: 5px;">
										<?=t('vips.restore_energy', [
											'amount'	=> 50,
											'price'		=> highamount(2000),
											'currency'	=> t('currencies.' . $player->character()->anime_id)
										]);?>
									</button>
								</form>
								<form id="vip-form-432" onsubmit="return false">
									<input type="hidden" name="id" value="432" />
									<button type="button" class="btn btn-primary btn-sm btn-block buy" data-id="432">
										<?=t('vips.restore_energy', [
											'amount'	=> 100,
											'price'		=> highamount(4),
											'currency'	=> t('currencies.credits')
										]);?>
									</button>
								</form>
							</div>
						</div>
					</div>
					<span class="c <?=$staminaColor;?>"><?=highamount($player->for_stamina());?></span>/<span class="m"><?=$player->for_stamina(true);?></span>
				</div>
				<div class="currency absolute"><?=highamount($player->currency);?></div>
				<div class="relogio absolute">
					<a href="javascript:void(0)" class="requirement-popover" data-source="#tooltip-relogio" data-title="<?=t('popovers.titles.routines');?>" data-trigger="hover" data-placement="bottom">
						<img src="<?=image_url('icons/relogio.png');?>" />
					</a>
					<div id="tooltip-relogio" class="status-popover-container">
						<div class="status-popover-content">
							<?=t('popovers.description.routines', [
								'mana' => t('formula.for_mana.' . $player->character_theme()->anime()->id)
							]);?>
						</div>
					</div>
				</div>
				<div class="gift absolute">
					<?php if (!$player_fidelity_topo->reward) { ?>
						<a href="<?=make_url('events#fidelity')?>" class="badge <?=(!$player_fidelity_topo->reward ? 'pulsate_icons' : '');?>">
							<i class="fa fa-exclamation fa-fw"></i>
						</a>
					<?php } ?>
					<a href="<?=make_url('events#fidelity')?>" class="requirement-popover" data-source="#tooltip-gift" data-title="<?=t('fidelity.topo_title');?>" data-trigger="hover" data-placement="bottom">
						<img src="<?=image_url('icons/' . ($player_fidelity_topo->reward ? 'gift-off.png' : 'gift-on.png'));?>" />
					</a>
					<div id="tooltip-gift" class="status-popover-container">
						<div class="status-popover-content">
							<?=($player_fidelity_topo->reward ? t('fidelity.topo_description2') : t('fidelity.topo_description', [
									'link' => make_url('events#fidelity')
							]));?>
						</div>
					</div>
				</div>
				<div class="queue absolute">
					<?php $rankedOpen = Ranked::isOpen(); ?>
					<?php if ($rankedOpen) { ?>
						<a href="<?=make_url('battle_pvps')?>" class="badge <?=($rankedOpen ? 'pulsate_icons' : '');?>">
							<i class="fa fa-exclamation fa-fw"></i>
						</a>
					<?php } ?>
					<a href="<?=make_url('battle_pvps')?>" class="requirement-popover" data-source="#tooltip-queue" data-title="<?=t('popovers.titles.queue' . ($rankedOpen ? '_ranked' : ''));?>" data-trigger="hover" data-placement="bottom">
						<img src="<?=image_url('icons/queue-' . ($player->is_pvp_queued ? 'on' : 'off') . '.png');?>" />
					</a>
					<div id="tooltip-queue" class="status-popover-container">
						<div id="tooltip-queue-data" class="status-popover-content">
							<?=t('popovers.description.queue.' . ($player->is_pvp_queued ? 'queued' : 'normal'));?>
							<?php if (is_menu_accessible(Menu::find(54), $player)) { ?>
								<hr />
								<div align="center">
									<?php if ($player->is_pvp_queued) { ?>
										<a href="javascript:;" class="btn btn-sm btn-block btn-danger">Sair da fila</a>
									<?php } else { ?>
										<a href="javascript:;" class="btn btn-sm btn-block btn-primary">Entrar na fila</a>
									<?php } ?>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
				<div class="mensagem absolute">
					<?php
					$newMessages	= sizeof(PrivateMessage::find('removed=0 AND to_id=' . $player->id . ' AND read_at IS NULL'));
					if ($newMessages) {
					?>
						<a href="<?=make_url('private_messages');?>" class="badge <?=($newMessages ? 'pulsate_icons' : '');?>">
							<i class="fa fa-exclamation fa-fw"></i>
						</a>
					<?php } ?>
					<a href="<?=make_url('private_messages');?>"><img src="<?=image_url('icons/email.png');?>" /></a>
				</div>
				<div class="vip absolute">
					<a href="<?=make_url('vips');?>">
						<img src="<?=image_url('icons/vip-on.png');?>" class="requirement-popover" data-source="#tooltip-vip" data-title="<?=t('popovers.titles.credits');?>" data-trigger="hover" data-placement="bottom" />
					</a>
					<div id="tooltip-vip" class="status-popover-container">
						<div class="status-popover-content">
							Você possui <b><?=highamount($user->credits);?></b> estrelas.
						</div>
					</div>
				</div>
				<div class="logout absolute">
					<a href="<?=make_url('users#logout');?>" name="Logout" title="Logout">
						<img src="<?=image_url('icons/logout.png');?>" border="0" alt="Logout" />
					</a>
				</div>
			</div>
			<div class="menu-content">
				<?php global $raw_menu_data; ?>
				<ul>
					<?php foreach ($raw_menu_data as $menu_category) { ?>
						<li class="hoverable">
							<img src="<?=image_url('menu-icons/' . $menu_category['id'] . (!sizeof($menu_category['menus']) ? '-D' : '') . '.png');?>">
							<span><?=t($menu_category['name']);?></span>
							<?php if (sizeof($menu_category['menus'])) { ?>
								<ul>
									<?php foreach ($menu_category['menus'] as $menu) { ?>
										<li><a href="<?=$menu['href'];?>"<?=($menu['external'] ? ' target="_blank"' : '');?>><?=t($menu['name']);?></a></li>
									<?php } ?>
								</ul>
							<?php } ?>
						</li>
					<?php } ?>
					<li id="inventory-trigger" data-text="<?=t('global.wait');?>">
						<img src="<?=image_url('menu-icons/10.png');?>">
						<span><?=t('menus.inventory');?></span>
						<ul id="inventory-container"></ul>
					</li>
				</ul>
			</div>
		</div>
		<div class="cloud"></div>
	</div>
<?php } ?>
<!-- Topo -->

<!-- Conteúdo -->
<div id="conteudo" class="<?=($player ? 'with-player' : '');?> <?=($with_battle ? 'with-battle' : '');?>">
	<div id="pagina">
		<div id="colunas">
			<?php if (!$player || !$with_battle) { ?>
				<div id="esquerda" class="<?=($player ? 'with-player' : '');?>">
					<?php if ($player) { ?>
						<?php if (!$player->map_id && !$is_profile) { ?>
							<?=partial('shared/left_character', [
								'user'		=> $user,
								'player'	=> $player
							]);?>
						<?php } else { ?>
							<div style="height: 680px;"></div>
						<?php } ?>
						<div style="clear:both; float: left"></div>
					<?php } else { ?>
						<div id="menu">
							<?php if (!$_SESSION['loggedin']) { ?>
								<div id="login" style="background-image:url('<?=image_url('bg-login.png');?>');">
									<div id="form-login">
										<form method="post" onsubmit="return false">
											<input type="email" name="email" placeholder="Digite seu Email" class="in-login" />
											<input type="password" name="password" placeholder="Digite sua Senha" class="in-senha" />
											<!-- <input type="text" name="captcha" class="in-codigo" placeholder="Digite o Código" /> -->
											<!-- <img class="in-captcha" src="<?=make_url('captcha#login');?>" alt="Captcha Code" /> -->
											<div style="width: 114px; margin: 0 auto; margin-top: 28px;">
												<a href="<?=make_url('users/reset_password');?>" style="float: left; margin: 0 2px;">
													<img src="<?=image_url('buttons/bt-senha.png');?>" data-toggle="tooltip" title="<?=make_tooltip('Esqueci minha Senha', 125);?>" />
												</a>
												<button type="submit" class="play-button g-recaptcha" data-sitekey="<?=$recaptcha['site'];?>" data-callback="doLogin"></button>
												<a href="<?=$fb_url;?>" style="float: left; margin: 0 2px;">
													<img src="<?=image_url('buttons/bt-face.png');?>" data-toggle="tooltip" title="<?=make_tooltip('Entrar com Facebook', 125);?>" />
												</a>
												<div class="break"></div>
											</div>
										</form>
									</div>
								</div>
							<?php } else { ?>
								<?php if ($_SESSION['player_id']) { ?>
									<div id="login" style="background-image:url('<?=image_url('bg-login-' . $player->character()->anime_id . '.png');?>');">
									</div>
								<?php } else { ?>
									<div id="login" style="background-image:url('<?=image_url('bg-login-0.png');?>');">
										<div id="form-login">
											<div style="left: -7px; position: relative; top: 7px; min-height: 48px;">
												<div class="fb-like" data-href="http://www.facebook.com/<?=FB_PAGE_USER;?>" data-send="false" data-layout="box_count" data-width="70" data-show-faces="false"></div>
											</div>
											<div style="position: relative; top: 25px; left: -7px;">
												<b><span style="color: #ede4af"><?=highamount($user->credits)?></span><br /> Estrelas</b>
											</div>
										</div>
									</div>
								<?php } ?>
							<?php } ?>
							<div id="menu-conteudo">
								<div id="menu-topo"></div>
								<div id="menu-repete">
									<?php
									global $menu_data;
									foreach ($menu_data as $menu_category) {
										if (sizeof($menu_category['menus'])) {
									?>
									<img src="<?=image_url('menus/' . $_SESSION['language_id'] . '/' . $menu_category['id'] . '_' . ($player ? $player->character()->anime_id : rand(1, 6)) . '.png');?>" />
									<?php
											foreach ($menu_category['menus'] as $menu) {
												if ($menu['hidden']) continue;
									?>
									<li><a href="<?=$menu['href'];?>"<?=($menu['external'] ? ' target="_blank"' : '');?>><?=t($menu['name']);?></a></li>
									<?php
											}
										}
									}
									?>
									<div class="clearfix"></div>
								</div>
								<div id="menu-fim"></div>
							</div>
						</div>
					<?php } ?>
					<?php if (FW_ENV == 'devs') { ?><br />
						<div style="width: <?=($_SESSION['player_id'] ? '240px' : '100%')?>;">
							<script id="_wauae2">var _wau = _wau || []; _wau.push(["dynamic", "gq7qmwiq8v", "ae2", "c4302bffffff", "small"]);</script>
							<script async src="//waust.at/d.js"></script>
						</div>
					<?php } ?>
				</div>
			<?php } ?>
			<div id="direita" class="<?=($player ? 'with-player' : '');?>">
				@yield
				<div class="clearfix"></div>
			</div>
			<div class="clearfix"></div>
		</div>
		<?php if (!$with_battle && $player) { ?>
			<div class="esquerda-gradient" ></div>
		<?php } ?>
		<div class="clearfix"></div>
	</div>
	<div class="clearfix"></div>
</div>
<?=partial('shared/footer', ['player' => $player]);?>


<?php if (FW_ENV != 'dev') { ?>
	<?php /*<div id="l-banner" style="position: absolute; height: auto; width: 160px; top: -200px; right: -180px; z-index: 1000;">
		<div style="position: fixed">
			<!-- AASG - Lateral -->
			<ins class="adsbygoogle"
				style="display:inline-block;width:300px;height:600px"
				data-ad-client="ca-pub-6665062829379662"
				data-ad-slot="3399133151"></ins>
			<script>
				(adsbygoogle = window.adsbygoogle || []).push({});
			</script>
		</div>
	</div>*/ ?>
<?php } ?>

<?php if (FW_ENV != 'dev') { ?>
	<div style="display: none;">
		<script id="_wau38g">var _wau = _wau || []; _wau.push(["dynamic", "j0ycq84tlk", "38g", "c4302bffffff", "small"]);</script>
		<script async src="//waust.at/d.js"></script>
	</div>
<?php } ?>

<div class="box-cookies hide">
	<p class="msg-cookies">Este site usa cookies para garantir que você obtenha a melhor experiência.</p>
	<button class="btn btn-primary btn-cookies">Aceitar!</button>
</div>

<?php if ($player) { ?>
	<?=partial('shared/chat', ['player' => $player]);?>
	<script type="text/javascript" src="<?=asset_url('js/highlights.js');?>"></script>
<?php } ?>

<script type="text/javascript" src="<?=asset_url('js/bootstrap.min.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/select2.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/tutorial.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/typeahead.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/bootbox.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/global.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/users.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/characters.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/friends.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/graduations.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/achievement.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/techniques.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/trainings.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/shop.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/luck.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/reset_password.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/talents.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/inventory.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/battles.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/battle_npcs.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/battle_pvps.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/hospital.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/rankings.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/home.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/support.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/quests.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/equipments.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/private_messages.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/pets.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/events.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/history_mode.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/challenge.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/maps.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/guilds.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/vips.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/png_animator.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/ranked.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/tournaments.js');?>"></script>
<?php if (FW_ENV != 'dev') { ?>
	<script type="text/javascript" src="//www.google.com/recaptcha/api.js" async defer></script>
	<script async defer crossorigin="anonymous"
		src="https://connect.facebook.net/pt_BR/sdk.js#xfbml=1
				&version=v10.0&appId=<?=FB_APP_ID;?>&autoLogAppEvents=1"
		nonce="z3ba4zPG">
	</script>
	<script type="text/javascript" charset="utf-8">
		// Place this code snippet near the footer of your page before the close of the /body tag
		// LEGAL NOTICE: The content of this website and all associated program code are protected under the Digital Millennium Copyright Act. Intentionally circumventing this code may constitute a violation of the DMCA.

		eval(function(p,a,c,k,e,d){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--){d[e(c)]=k[c]||e(c)}k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}(';z Q=\'\',2c=\'1V\';1O(z i=0;i<12;i++)Q+=2c.X(G.K(G.O()*2c.I));z 2D=6,2R=6e,2Q=6d,2G=6b,2m=D(t){z i=!1,o=D(){B(q.1k){q.38(\'3a\',e);H.38(\'1U\',e)}S{q.36(\'32\',e);H.36(\'27\',e)}},e=D(){B(!i&&(q.1k||5Y.2Z===\'1U\'||q.34===\'3e\')){i=!0;o();t()}};B(q.34===\'3e\'){t()}S B(q.1k){q.1k(\'3a\',e);H.1k(\'1U\',e)}S{q.35(\'32\',e);H.35(\'27\',e);z n=!1;2O{n=H.6k==6j&&q.1X}2A(r){};B(n&&n.2Y){(D a(){B(i)J;2O{n.2Y(\'14\')}2A(e){J 5q(a,50)};i=!0;o();t()})()}}};H[\'\'+Q+\'\']=(D(){z t={t$:\'1V+/=\',5r:D(e){z a=\'\',d,n,i,c,s,l,o,r=0;e=t.e$(e);1c(r<e.I){d=e.1a(r++);n=e.1a(r++);i=e.1a(r++);c=d>>2;s=(d&3)<<4|n>>4;l=(n&15)<<2|i>>6;o=i&63;B(2x(n)){l=o=64}S B(2x(i)){o=64};a=a+11.t$.X(c)+11.t$.X(s)+11.t$.X(l)+11.t$.X(o)};J a},13:D(e){z n=\'\',d,l,c,s,r,o,a,i=0;e=e.1p(/[^A-5x-5y-9\\+\\/\\=]/g,\'\');1c(i<e.I){s=11.t$.1F(e.X(i++));r=11.t$.1F(e.X(i++));o=11.t$.1F(e.X(i++));a=11.t$.1F(e.X(i++));d=s<<2|r>>4;l=(r&15)<<4|o>>2;c=(o&3)<<6|a;n=n+R.U(d);B(o!=64){n=n+R.U(l)};B(a!=64){n=n+R.U(c)}};n=t.n$(n);J n},e$:D(t){t=t.1p(/;/g,\';\');z n=\'\';1O(z i=0;i<t.I;i++){z e=t.1a(i);B(e<1n){n+=R.U(e)}S B(e>6R&&e<72){n+=R.U(e>>6|78);n+=R.U(e&63|1n)}S{n+=R.U(e>>12|2P);n+=R.U(e>>6&63|1n);n+=R.U(e&63|1n)}};J n},n$:D(t){z i=\'\',e=0,n=76=1z=0;1c(e<t.I){n=t.1a(e);B(n<1n){i+=R.U(n);e++}S B(n>74&&n<2P){1z=t.1a(e+1);i+=R.U((n&31)<<6|1z&63);e+=2}S{1z=t.1a(e+1);2F=t.1a(e+2);i+=R.U((n&15)<<12|(1z&63)<<6|2F&63);e+=3}};J i}};z a=[\'57==\',\'48\',\'4b=\',\'3u\',\'3i\',\'3j=\',\'4V=\',\'4W=\',\'4X\',\'4Y\',\'52=\',\'5k=\',\'5j\',\'71\',\'5o=\',\'3L\',\'3M=\',\'3N=\',\'3O=\',\'3Q=\',\'3S=\',\'3T=\',\'3K==\',\'42==\',\'44==\',\'47==\',\'3I=\',\'3n\',\'3t\',\'3B\',\'55\',\'5l\',\'5i\',\'5a==\',\'59=\',\'4M=\',\'4o=\',\'4p==\',\'4q=\',\'4r\',\'4s=\',\'4I=\',\'4H==\',\'4G=\',\'4C==\',\'4A==\',\'3r=\',\'4Q=\',\'4y\',\'4z==\',\'4B==\',\'4D\',\'4x==\',\'4E=\'],b=G.K(G.O()*a.I),w=t.13(a[b]),Y=w,M=1,W=\'#4J\',r=\'#4K\',g=\'#4L\',f=\'#4F\',L=\'\',y=\'4v 26-4g(a)!\',p=\'4u 4t 4n&4m; 4l&2j; 4k 4j 2i 2o 21&23;24... 2k 26, 4i 4h 2o 21&23;24 n&4w;?\',v=\'4N, 56 58 21&23;24, n&5b;o 5c 5d 5m 5e 5g 5h...\',s=\'2k 26, j&2j; 5f o 2i!\',i=0,u=0,n=\'4P.54\',l=0,A=e()+\'.2X\';D h(t){B(t)t=t.1H(t.I-15);z i=q.2T(\'53\');1O(z n=i.I;n--;){z e=R(i[n].1J);B(e)e=e.1H(e.I-15);B(e===t)J!0};J!1};D m(t){B(t)t=t.1H(t.I-15);z e=q.51;x=0;1c(x<e.I){1g=e[x].1v;B(1g)1g=1g.1H(1g.I-15);B(1g===t)J!0;x++};J!1};D e(t){z n=\'\',i=\'1V\';t=t||30;1O(z e=0;e<t;e++)n+=i.X(G.K(G.O()*i.I));J n};D o(i){z o=[\'4U\',\'4T==\',\'4S\',\'4R\',\'2h\',\'4e==\',\'4O=\',\'4f==\',\'49=\',\'4d==\',\'3G==\',\'3F==\',\'3E\',\'3D\',\'3C\',\'2h\'],r=[\'2B=\',\'3A==\',\'3z==\',\'3y==\',\'3x=\',\'3w\',\'3v=\',\'3H=\',\'2B=\',\'3s\',\'3q==\',\'3p\',\'3o==\',\'3m==\',\'3l==\',\'3k=\'];x=0;1G=[];1c(x<i){c=o[G.K(G.O()*o.I)];d=r[G.K(G.O()*r.I)];c=t.13(c);d=t.13(d);z a=G.K(G.O()*2)+1;B(a==1){n=\'//\'+c+\'/\'+d}S{n=\'//\'+c+\'/\'+e(G.K(G.O()*20)+4)+\'.2X\'};1G[x]=2a 29();1G[x].28=D(){z t=1;1c(t<7){t++}};1G[x].1J=n;x++}};D F(t){};J{2u:D(t,r){B(3J q.N==\'3Y\'){J};z i=\'0.1\',r=Y,e=q.1b(\'1o\');e.16=r;e.k.1i=\'1N\';e.k.14=\'-1h\';e.k.10=\'-1h\';e.k.1e=\'2e\';e.k.V=\'4c\';z d=q.N.2V,a=G.K(d.I/2);B(a>15){z n=q.1b(\'2d\');n.k.1i=\'1N\';n.k.1e=\'1A\';n.k.V=\'1A\';n.k.10=\'-1h\';n.k.14=\'-1h\';q.N.4a(n,q.N.2V[a]);n.1d(e);z o=q.1b(\'1o\');o.16=\'2U\';o.k.1i=\'1N\';o.k.14=\'-1h\';o.k.10=\'-1h\';q.N.1d(o)}S{e.16=\'2U\';q.N.1d(e)};l=46(D(){B(e){t((e.1W==0),i);t((e.1Y==0),i);t((e.1E==\'2n\'),i);t((e.1P==\'2K\'),i);t((e.1S==0),i)}S{t(!0,i)}},2b)},1R:D(e,c){B((e)&&(i==0)){i=1;H[\'\'+Q+\'\'].1y();H[\'\'+Q+\'\'].1R=D(){J}}S{z v=t.13(\'45\'),u=q.43(v);B((u)&&(i==0)){B((2R%3)==0){z l=\'41=\';l=t.13(l);B(h(l)){B(u.1L.1p(/\\s/g,\'\').I==0){i=1;H[\'\'+Q+\'\'].1y()}}}};z b=!1;B(i==0){B((2Q%3)==0){B(!H[\'\'+Q+\'\'].2J){z d=[\'3Z==\',\'3X==\',\'3W=\',\'3V=\',\'3U=\'],m=d.I,r=d[G.K(G.O()*m)],a=r;1c(r==a){a=d[G.K(G.O()*m)]};r=t.13(r);a=t.13(a);o(G.K(G.O()*2)+1);z n=2a 29(),s=2a 29();n.28=D(){o(G.K(G.O()*2)+1);s.1J=a;o(G.K(G.O()*2)+1)};s.28=D(){i=1;o(G.K(G.O()*3)+1);H[\'\'+Q+\'\'].1y()};n.1J=r;B((2G%3)==0){n.27=D(){B((n.V<8)&&(n.V>0)){H[\'\'+Q+\'\'].1y()}}};o(G.K(G.O()*3)+1);H[\'\'+Q+\'\'].2J=!0};H[\'\'+Q+\'\'].1R=D(){J}}}}},1y:D(){B(u==1){z Z=2M.6W(\'2E\');B(Z>0){J!0}S{2M.6X(\'2E\',(G.O()+1)*2b)}};z h=\'6Y==\';h=t.13(h);B(!m(h)){z c=q.1b(\'6Z\');c.1Z(\'70\',\'6U\');c.1Z(\'2Z\',\'1m/73\');c.1Z(\'1v\',h);q.2T(\'75\')[0].1d(c)};77(l);q.N.1L=\'\';q.N.k.17+=\'T:1A !19\';q.N.k.17+=\'1x:1A !19\';z A=q.1X.1Y||H.3b||q.N.1Y,b=H.6S||q.N.1W||q.1X.1W,a=q.1b(\'1o\'),M=e();a.16=M;a.k.1i=\'2z\';a.k.14=\'0\';a.k.10=\'0\';a.k.V=A+\'1w\';a.k.1e=b+\'1w\';a.k.3h=W;a.k.1T=\'6D\';q.N.1d(a);z d=\'<a 1v="6E://6F.6G"><2p 16="2r" V="2C" 1e="40"><2s 16="2t" V="2C" 1e="40" 6H:1v="6I:2s/6C;6J,6L+6M+6N+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+6P+6Q+79/7a/7b/7c/7u/7v+/7w/7x+7y/7z+7A/7B/7C/7D/7E/7F/7G+7H/7t+7s+7r+7q+7p+7o/7n+7m/7l+7k/7j+7i+7h+7g+7f/7e+7d/6T/6A/5X+6z+5H/5I+5J+5K+5L+E+5M/5G/5N/5P/5Q/5R/+5S/5T++5U/5O/5E+5p/5D+5C+5B==">;</2p></a>\';d=d.1p(\'2r\',e());d=d.1p(\'2t\',e());z o=q.1b(\'1o\');o.1L=d;o.k.1i=\'1N\';o.k.1D=\'1K\';o.k.14=\'1K\';o.k.V=\'5A\';o.k.1e=\'5z\';o.k.1T=\'3c\';o.k.1S=\'.6\';o.k.2y=\'2w\';o.1k(\'5w\',D(){n=n.5v(\'\').5u().5t(\'\');H.2I.1v=\'//\'+n});q.1Q(M).1d(o);z i=q.1b(\'1o\'),F=e();i.16=F;i.k.1i=\'2z\';i.k.10=b/7+\'1w\';i.k.5V=A-5F+\'1w\';i.k.5W=b/3.5+\'1w\';i.k.3h=\'#6m\';i.k.1T=\'3c\';i.k.17+=\'P-1u: "6o 6p", 1q, 1t, 1s-1r !19\';i.k.17+=\'6q-1e: 6r !19\';i.k.17+=\'P-1j: 6t !19\';i.k.17+=\'1m-1C: 1B !19\';i.k.17+=\'1x: 6u !19\';i.k.1E+=\'2H\';i.k.37=\'1K\';i.k.6v=\'1K\';i.k.6w=\'2W\';q.N.1d(i);i.k.6x=\'1A 6s 6i -6a 6h(0,0,0,0.3)\';i.k.1P=\'2l\';z x=30,Y=22,w=18,L=18;B((H.3b<3g)||(61.V<3g)){i.k.33=\'50%\';i.k.17+=\'P-1j: 66 !19\';i.k.37=\'68;\';o.k.33=\'65%\';z x=22,Y=18,w=12,L=12};i.1L=\'<3d k="1l:#69;P-1j:\'+x+\'1I;1l:\'+r+\';P-1u:1q, 1t, 1s-1r;P-1M:6c;T-10:1f;T-1D:1f;1m-1C:1B;">\'+y+\'</3d><3f k="P-1j:\'+Y+\'1I;P-1M:6f;P-1u:1q, 1t, 1s-1r;1l:\'+r+\';T-10:1f;T-1D:1f;1m-1C:1B;">\'+p+\'</3f><6g k=" 1E: 2H;T-10: 0.39;T-1D: 0.39;T-14: 2g;T-2N: 2g; 2q:67 62 #6y; V: 25%;1m-1C:1B;"><p k="P-1u:1q, 1t, 1s-1r;P-1M:2v;P-1j:\'+w+\'1I;1l:\'+r+\';1m-1C:1B;">\'+v+\'</p><p k="T-10:6n;"><2d 6l="11.k.1S=.9;" 5s="11.k.1S=1;"  16="\'+e()+\'" k="2y:2w;P-1j:\'+L+\'1I;P-1u:1q, 1t, 1s-1r; P-1M:2v;2q-6O:2W;1x:1f;6K-1l:\'+g+\';1l:\'+f+\';1x-14:2e;1x-2N:2e;V:60%;T:2g;T-10:1f;T-1D:1f;" 6V="H.2I.6B();">\'+s+\'</2d></p>\'}}})();H.2S=D(t,e){z n=5n.3P,i=H.3R,a=n(),o,r=D(){n()-a<e?o||i(r):t()};i(r);J{4Z:D(){o=1}}};z 2L;B(q.N){q.N.k.1P=\'2l\'};2m(D(){B(q.1Q(\'2f\')){q.1Q(\'2f\').k.1P=\'2n\';q.1Q(\'2f\').k.1E=\'2K\'};2L=H.2S(D(){H[\'\'+Q+\'\'].2u(H[\'\'+Q+\'\'].1R,H[\'\'+Q+\'\'].5Z)},2D*2b)});',62,478,'||||||||||||||||||||style||||||document|||||||||var||if|vr6|function|||Math|window|length|return|floor|||body|random|font|FHFKxibLnvis|String|else|margin|fromCharCode|width||charAt|||top|this||decode|left||id|cssText||important|charCodeAt|createElement|while|appendChild|height|10px|thisurl|5000px|position|size|addEventListener|color|text|128|DIV|replace|Helvetica|serif|sans|geneva|family|href|px|padding|LxrriOzjzN|c2|0px|center|align|bottom|display|indexOf|spimg|substr|pt|src|30px|innerHTML|weight|absolute|for|visibility|getElementById|SmROjjKvDC|opacity|zIndex|load|ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789|clientHeight|documentElement|clientWidth|setAttribute||an||uacute|ncios||bem|onload|onerror|Image|new|1000|JdojesNrnL|div|60px|babasbmsgx|auto|cGFydG5lcmFkcy55c20ueWFob28uY29t|bloqueador|aacute|Tudo|visible|BocsdlCNHf|hidden|de|svg|border|FILLVECTID1|image|FILLVECTID2|fdgiwjuYXc|300|pointer|isNaN|cursor|fixed|catch|ZmF2aWNvbi5pY28|160|gpZxGhmgrM|babn|c3|eOIkXeurcr|block|location|ranAlready|none|kaLgKBvpCR|sessionStorage|right|try|224|PJIAQtbmIE|dnPyDaAazI|qNuplbxyhO|getElementsByTagName|banner_ad|childNodes|15px|jpg|doScroll|type|||onreadystatechange|zoom|readyState|attachEvent|detachEvent|marginLeft|removeEventListener|5em|DOMContentLoaded|innerWidth|10000|h3|complete|h1|640|backgroundColor|YWQtaW1n|YWQtaW5uZXI|YWR2ZXJ0aXNlbWVudC0zNDMyMy5qcGc|d2lkZV9za3lzY3JhcGVyLmpwZw|bGFyZ2VfYmFubmVyLmdpZg|RGl2QWQx|YmFubmVyX2FkLmdpZg|ZmF2aWNvbjEuaWNv|c3F1YXJlLWFkLnBuZw|YWRzZXJ2ZXI|YWQtbGFyZ2UucG5n|RGl2QWQy|YWQtaGVhZGVy|YWRjbGllbnQtMDAyMTQ3LWhvc3QxLWJhbm5lci1hZC5qcGc|MTM2N19hZC1jbGllbnRJRDI0NjQuanBn|c2t5c2NyYXBlci5qcGc|NzIweDkwLmpwZw|NDY4eDYwLmpwZw|YmFubmVyLmpwZw|RGl2QWQz|YXMuaW5ib3guY29t|YWRzYXR0LmVzcG4uc3RhcndhdmUuY29t|YWRzYXR0LmFiY25ld3Muc3RhcndhdmUuY29t|YWRzLnp5bmdhLmNvbQ|YWRzLnlhaG9vLmNvbQ|Q0ROLTMzNC0xMDktMTM3eC1hZC1iYW5uZXI|RGl2QWQ|typeof|QWRzX2dvb2dsZV8wMQ|QWRBcmVh|QWRGcmFtZTE|QWRGcmFtZTI|QWRGcmFtZTM|now|QWRGcmFtZTQ|requestAnimationFrame|QWRMYXllcjE|QWRMYXllcjI|Ly93d3cuZG91YmxlY2xpY2tieWdvb2dsZS5jb20vZmF2aWNvbi5pY28|Ly9hZHMudHdpdHRlci5jb20vZmF2aWNvbi5pY28|Ly9hZHZlcnRpc2luZy55YWhvby5jb20vZmF2aWNvbi5pY28|Ly93d3cuZ3N0YXRpYy5jb20vYWR4L2RvdWJsZWNsaWNrLmljbw|undefined|Ly93d3cuZ29vZ2xlLmNvbS9hZHNlbnNlL3N0YXJ0L2ltYWdlcy9mYXZpY29uLmljbw||Ly9wYWdlYWQyLmdvb2dsZXN5bmRpY2F0aW9uLmNvbS9wYWdlYWQvanMvYWRzYnlnb29nbGUuanM|QWRzX2dvb2dsZV8wMg|querySelector|QWRzX2dvb2dsZV8wMw|aW5zLmFkc2J5Z29vZ2xl|setInterval|QWRzX2dvb2dsZV8wNA|YWRCYW5uZXJXcmFw|Y2FzLmNsaWNrYWJpbGl0eS5jb20|insertBefore|YWQtZnJhbWU|468px|cHJvbW90ZS5wYWlyLmNvbQ|YS5saXZlc3BvcnRtZWRpYS5ldQ|YWR2ZXJ0aXNpbmcuYW9sLmNvbQ|vindo|gosta|quem|um|usando|est|ecirc|voc|QWRDb250YWluZXI|Z2xpbmtzd3JhcHBlcg|YWRUZWFzZXI|YmFubmVyX2Fk|YWRCYW5uZXI|que|Vi|Seja|eacute|b3V0YnJhaW4tcGFpZA|YWRzbG90|cG9wdXBhZA|YWRfY2hhbm5lbA|YWRzZW5zZQ|IGFkX2JveA|Z29vZ2xlX2Fk|c3BvbnNvcmVkX2xpbms|FFFFFF|YmFubmVyYWQ|YWRBZA|YWRiYW5uZXI|EEEEEE|777777|adb8ff|QWRCb3gxNjA|Mas|YWdvZGEubmV0L2Jhbm5lcnM|moc|YmFubmVyaWQ|YWQuZm94bmV0d29ya3MuY29t|anVpY3lhZHMuY29t|YWQubWFpbC5ydQ|YWRuLmViYXkuY29t|YWQtbGFiZWw|YWQtbGI|YWQtZm9vdGVy|YWQtY29udGFpbmVy|clear||styleSheets|YWQtY29udGFpbmVyLTE|script|kcolbdakcolb|RGl2QWRB|sem|YWQtbGVmdA|estes|QWREaXY|QWRJbWFnZQ|atilde|conseguimos|nos|por|desativei|muito|tempo|RGl2QWRD|QWQzMDB4MTQ1|YWQtY29udGFpbmVyLTI|RGl2QWRC|manter|Date|QWQ3Mjh4OTA|dEflqX6gzC4hd1jSgz0ujmPkygDjvNYDsU0ZggjKBqLPrQLfDUQIzxMBtSOucRwLzrdQ2DFO0NDdnsYq0yoJyEB0FHTBHefyxcyUy8jflH7sHszSfgath4hYwcD3M29I5DMzdBNO2IFcC5y6HSduof4G5dQNMWd4cDcjNNeNGmb02|setTimeout|encode|onmouseout|join|reverse|split|click|Za|z0|40px|160px|gkJocgFtzfMzwAAAABJRU5ErkJggg|3eUeuATRaNMs0zfml|Uv0LfPzlsBELZ|uJylU|120|SRWhNsmOazvKzQYcE0hV5nDkuQQKfUgm4HmqA2yuPxfMU1m4zLRTMAqLhN6BHCeEXMDo2NsY8MdCeBB6JydMlps3uGxZefy7EO1vyPvhOxL7TPWjVUVvZkNJ|bTplhb|E5HlQS6SHvVSU0V|j9xJVBEEbWEXFVZQNX9|1HX6ghkAR9E5crTgM|0t6qjIlZbzSpemi|MjA3XJUKy|CGf7SAP2V6AjTOUa8IzD3ckqe2ENGulWGfx9VKIBB72JM1lAuLKB3taONCBn3PY0II5cFrLr7cCp|Kq8b7m0RpwasnR|UIWrdVPEp7zHy7oWXiUgmR3kdujbZI73kghTaoaEKMOh8up2M8BVceotd|BNyENiFGe5CxgZyIT6KVyGO2s5J5ce|14XO7cR5WV1QBedt3c|QhZLYLN54|e8xr8n5lpXyn|u3T9AbDjXwIMXfxmsarwK9wUBB5Kj8y2dCw|minWidth|minHeight|x0z6tauQYvPxwT0VM1lH9Adt5Lp|event|TWVJPoEbvQ||screen|solid||||18pt|1px|45px|999|8px|109|200|142|277|500|hr|rgba|24px|null|frameElement|onmouseover|fff|35px|Arial|Black|line|normal|14px|16pt|12px|marginRight|borderRadius|boxShadow|CCC|F2Q|pyQLiBu8WDYgxEZMbeEqIiSM8r|reload|png|9999|http|blockadblock|com|xlink|data|base64|background|iVBORw0KGgoAAAANSUhEUgAAAKAAAAAoCAMAAABO8gGqAAAB|1BMVEXr6|sAAADr6|radius|sAAADMAAAsKysKCgokJCRycnIEBATq6uoUFBTMzMzr6urjqqoSEhIGBgaxsbHcd3dYWFg0NDTmw8PZY2M5OTkfHx|enp7TNTUoJyfm5ualpaV5eXkODg7k5OTaamoqKSnc3NzZ2dmHh4dra2tHR0fVQUFAQEDPExPNBQXo6Ohvb28ICAjp19fS0tLnzc29vb25ubm1tbWWlpaNjY3dfX1oaGhUVFRMTEwaGhoXFxfq5ubh4eHe3t7Hx8fgk5PfjY3eg4OBgYF|127|innerHeight|kmLbKmsE|stylesheet|onclick|getItem|setItem|Ly95dWkueWFob29hcGlzLmNvbS8zLjE4LjEvYnVpbGQvY3NzcmVzZXQvY3NzcmVzZXQtbWluLmNzcw|link|rel|QWQzMDB4MjUw|2048|css|191|head|c1|clearInterval|192|fn5EREQ9PT3SKSnV1dXks7OsrKypqambmpqRkZFdXV1RUVHRISHQHR309PTq4eHp3NzPz8|Ly8vKysrDw8O4uLjkt7fhnJzgl5d7e3tkZGTYVlZPT08vLi7OCwu|v792dnbbdHTZYWHZXl7YWlpZWVnVRkYnJib8|PzNzc3myMjlurrjsLDhoaHdf3|szSdAtKtwkRRNnCIiDzNzc0RO|uI70wOsgFWUQCfZC1UI0Ettoh66D|UADVgvxHBzP9LUufqQDtV|UimAyng9UePurpvM8WmAdsvi6gNwBMhPrPqemoXywZs8qL9JZybhqF6LZBZJNANmYsOSaBTkSqcpnCFEkntYjtREFlATEtgxdDQlffhS3ddDAzfbbHYPUDGJpGT|h0GsOCs9UwP2xo6|QcWrURHJSLrbBNAxZTHbgSCsHXJkmBxisMvErFVcgE|I1TpO7CnBZO|iqKjoRAEDlZ4soLhxSgcy6ghgOy7EeC2PI4DHb7pO7mRwTByv5hGxF|BKpxaqlAOvCqBjzTFAp2NFudJ5paelS5TbwtBlAvNgEdeEGI6O6JUt42NhuvzZvjXTHxwiaBXUIMnAKa5Pq9SL3gn1KAOEkgHVWBIMU14DBF2OH3KOfQpG2oSQpKYAEdK0MGcDg1xbdOWy|0nga14QJ3GOWqDmOwJgRoSme8OOhAQqiUhPMbUGksCj5Lta4CbeFhX9NN0Tpny|KmSx|uWD20LsNIDdQut4LXA|YbUMNVjqGySwrRUGsLu6|1FMzZIGQR3HWJ4F1TqWtOaADq0Z9itVZrg1S6JLi7B1MAtUCX1xNB0Y0oL9hpK4|CXRTTQawVogbKeDEs2hs4MtJcNVTY2KgclwH2vYODFTa4FQ|qdWy60K14k|RUIrwGk|aa2thYWHXUFDUPDzUOTno0dHipqbceHjaZ2dCQkLSLy|v7|b29vlvb2xn5|ejIzabW26SkqgMDA7HByRAADoM7kjAAAAInRSTlM6ACT4xhkPtY5iNiAI9PLv6drSpqGYclpM5bengkQ8NDAnsGiGMwAABetJREFUWMPN2GdTE1EYhmFQ7L339rwngV2IiRJNIGAg1SQkFAHpgnQpKnZBAXvvvXf9mb5nsxuTqDN|cIa9Z8IkGYa9OGXPJDm5RnMX5pim7YtTLB24btUKmKnZeWsWpgHnzIP5UucvNoDrl8GUrVyUBM4xqQ|ISwIz5vfQyDF3X|MgzNFaCVyHVIONbx1EDrtCzt6zMEGzFzFwFZJ19jpJy2qx5BcmyBM|oGKmW8DAFeDOxfOJM4DcnTYrtT7dhZltTW7OXHB1ClEWkPO0JmgEM1pebs5CcA2UCTS6QyHMaEtyc3LAlWcDjZReyLpKZS9uT02086vu0tJa|Lnx0tILMKp3uvxI61iYH33Qq3M24k|VOPel7RIdeIBkdo|HY9WAzpZLSSCNQrZbGO1n4V4h9uDP7RTiIIyaFQoirfxCftiht4sK8KeKqPh34D2S7TsROHRiyMrAxrtNms9H5Qaw9ObU1H4Wdv8z0J8obvOo|wd4KAnkmbaePspA|0idvgbrDeBhcK|EuJ0GtLUjVftvwEYqmaR66JX9Apap6cCyKhiV'.split('|'),0,{}));
	</script>
<?php } ?>
<?php
// if ($player) {
// 	$redis = new Redis();
// 	$redis->pconnect(REDIS_SERVER);
// 	$redis->auth(REDIS_PASS);
// 	$redis->select(0);

// 	$have_queue	= FALSE;
// 	$queues		= $redis->lRange("aasg_od_invites", 0, -1);
// 	foreach ($queues as $queue) {
// 		$targets = array_unique(
// 			array_merge(
// 				$redis->lRange("od_targets_" . $queue, 0, -1),
// 				$redis->lRange("od_accepts_" . $queue, 0, -1)
// 			)
// 		);

// 		if (in_array($player->id, $targets)) {
// 			$have_queue				= true;
// 			$guild_dungeon	= $redis->get("od_id_" . $queue);
// 			break;
// 		}
// 	}
// 	if ($have_queue) {
// 		echo '<script type="text/javascript">
// 			createInviteModal(' . $guild_dungeon . ');
// 		</script>';
// 	}
// }
?>
<!-- Conteúdo -->
<script type="text/javascript">
	$(document).ready(function() {
		$('.select2').select2({
			theme: "bootstrap",
		});
	});
</script>
</body>
</html>
