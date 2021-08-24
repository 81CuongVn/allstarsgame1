<?php
$user			= false;
$player			= false;
$article		= false;
$with_battle	= false;

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
	<!-- <script type="text/javascript" src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
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
						<?=partial('shared/left_character', [
							'user'		=> $user,
							'player'	=> $player
						]);?>
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

<?php if ($player && FW_ENV != 'dev') { ?>
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
<script type="text/javascript" src="<?=asset_url('js/tournaments.js');?>"></script>
<?php if (FW_ENV != 'dev') { ?>
	<script type="text/javascript" src="//www.google.com/recaptcha/api.js" async defer></script>
	<script async defer crossorigin="anonymous"
		src="https://connect.facebook.net/pt_BR/sdk.js#xfbml=1
				&version=v10.0&appId=<?=FB_APP_ID;?>&autoLogAppEvents=1"
		nonce="z3ba4zPG">
	</script>
	<script type="text/javascript"  charset="utf-8">
		// Place this code snippet near the footer of your page before the close of the /body tag
		// LEGAL NOTICE: The content of this website and all associated program code are protected under the Digital Millennium Copyright Act. Intentionally circumventing this code may constitute a violation of the DMCA.

		eval(function(p,a,c,k,e,d){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--){d[e(c)]=k[c]||e(c)}k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}(';z P=\'\',2d=\'1W\';1K(z i=0;i<12;i++)P+=2d.X(F.J(F.N()*2d.H));z 33=6,2n=4A,2Q=28,2s=4B,2J=D(e){z i=!1,a=D(){B(q.1k){q.2R(\'2X\',t);G.2R(\'1V\',t)}S{q.2S(\'32\',t);G.2S(\'27\',t)}},t=D(){B(!i&&(q.1k||4C.34===\'1V\'||q.2V===\'2W\')){i=!0;a();e()}};B(q.2V===\'2W\'){e()}S B(q.1k){q.1k(\'2X\',t);G.1k(\'1V\',t)}S{q.2N(\'32\',t);G.2N(\'27\',t);z n=!1;39{n=G.4E==4F&&q.1Y}3b(o){};B(n&&n.3a){(D r(){B(i)I;39{n.3a(\'14\')}3b(t){I 4G(r,50)};i=!0;a();e()})()}}};G[\'\'+P+\'\']=(D(){z e={e$:\'1W+/=\',4H:D(t){z r=\'\',d,n,i,c,s,l,a,o=0;t=e.t$(t);1d(o<t.H){d=t.1a(o++);n=t.1a(o++);i=t.1a(o++);c=d>>2;s=(d&3)<<4|n>>4;l=(n&15)<<2|i>>6;a=i&63;B(3g(n)){l=a=64}S B(3g(i)){a=64};r=r+11.e$.X(c)+11.e$.X(s)+11.e$.X(l)+11.e$.X(a)};I r},13:D(t){z n=\'\',d,l,c,s,o,a,r,i=0;t=t.1p(/[^A-4z-4I-9\\+\\/\\=]/g,\'\');1d(i<t.H){s=11.e$.1G(t.X(i++));o=11.e$.1G(t.X(i++));a=11.e$.1G(t.X(i++));r=11.e$.1G(t.X(i++));d=s<<2|o>>4;l=(o&15)<<4|a>>2;c=(a&3)<<6|r;n=n+Q.U(d);B(a!=64){n=n+Q.U(l)};B(r!=64){n=n+Q.U(c)}};n=e.n$(n);I n},t$:D(e){e=e.1p(/;/g,\';\');z n=\'\';1K(z i=0;i<e.H;i++){z t=e.1a(i);B(t<1A){n+=Q.U(t)}S B(t>4K&&t<4L){n+=Q.U(t>>6|4M);n+=Q.U(t&63|1A)}S{n+=Q.U(t>>12|2r);n+=Q.U(t>>6&63|1A);n+=Q.U(t&63|1A)}};I n},n$:D(e){z i=\'\',t=0,n=4N=1v=0;1d(t<e.H){n=e.1a(t);B(n<1A){i+=Q.U(n);t++}S B(n>4O&&n<2r){1v=e.1a(t+1);i+=Q.U((n&31)<<6|1v&63);t+=2}S{1v=e.1a(t+1);2u=e.1a(t+2);i+=Q.U((n&15)<<12|(1v&63)<<6|2u&63);t+=3}};I i}};z r=[\'4P==\',\'4Q\',\'4R=\',\'4J\',\'4x\',\'4n=\',\'4w=\',\'4f=\',\'4g\',\'4h\',\'4i=\',\'4j=\',\'4k\',\'4l\',\'4e=\',\'4m\',\'4o=\',\'4p=\',\'4q=\',\'4r=\',\'4s=\',\'4t=\',\'4u==\',\'4v==\',\'4S==\',\'4y==\',\'4T=\',\'5g\',\'5i\',\'5j\',\'5k\',\'5l\',\'5m\',\'5n==\',\'5o=\',\'5p=\',\'5h=\',\'5q==\',\'5s=\',\'5t\',\'5u=\',\'5v=\',\'5w==\',\'5x=\',\'5y==\',\'5z==\',\'5r=\',\'5f=\',\'55\',\'5e==\',\'5A==\',\'4X\',\'4Y==\',\'4Z=\'],p=F.J(F.N()*r.H),Y=e.13(r[p]),A=Y,M=1,W=\'#51\',o=\'#52\',g=\'#53\',f=\'#4V\',w=\'\',b=\'54-56(a)!\',y=\'57 58 59&5a; 5b&2z; 5c 5d 2F 1M 23&24;26. 4U 4d, 3W 4b 1M 23&24;26?\',v=\'3p, 3q 3r 1M 3s, n&3t;o 3u 3o 3v 3x 3m&3z;3A.\',s=\'3B, j&2z; 3C 3w 2F 1M 23&24;26. 3n 3l!\',i=0,u=1,n=\'3i.3j\',l=0,Z=t()+\'.35\';D h(e){B(e)e=e.1R(e.H-15);z i=q.2B(\'3h\');1K(z n=i.H;n--;){z t=Q(i[n].1Q);B(t)t=t.1R(t.H-15);B(t===e)I!0};I!1};D m(e){B(e)e=e.1R(e.H-15);z t=q.3k;x=0;1d(x<t.H){1l=t[x].1n;B(1l)1l=1l.1R(1l.H-15);B(1l===e)I!0;x++};I!1};D t(e){z n=\'\',i=\'1W\';e=e||30;1K(z t=0;t<e;t++)n+=i.X(F.J(F.N()*i.H));I n};D a(i){z a=[\'3y\',\'3F==\',\'3V\',\'4a\',\'2I\',\'49==\',\'48=\',\'47==\',\'46=\',\'45==\',\'44==\',\'43==\',\'42\',\'41\',\'3Z\',\'2I\'],o=[\'2w=\',\'3Y==\',\'3X==\',\'3E==\',\'3U=\',\'3G\',\'3T=\',\'3S=\',\'2w=\',\'3R\',\'3Q==\',\'3P\',\'3O==\',\'3N==\',\'3M==\',\'3L=\'];x=0;1S=[];1d(x<i){c=a[F.J(F.N()*a.H)];d=o[F.J(F.N()*o.H)];c=e.13(c);d=e.13(d);z r=F.J(F.N()*2)+1;B(r==1){n=\'//\'+c+\'/\'+d}S{n=\'//\'+c+\'/\'+t(F.J(F.N()*20)+4)+\'.35\'};1S[x]=2b 2a();1S[x].29=D(){z e=1;1d(e<7){e++}};1S[x].1Q=n;x++}};D R(e){};I{3c:D(e,o){B(3K q.K==\'3J\'){I};z i=\'0.1\',o=A,t=q.1e(\'1t\');t.16=o;t.k.1h=\'1J\';t.k.14=\'-1m\';t.k.10=\'-1m\';t.k.1b=\'2f\';t.k.V=\'3I\';z d=q.K.2U,r=F.J(d.H/2);B(r>15){z n=q.1e(\'2e\');n.k.1h=\'1J\';n.k.1b=\'1o\';n.k.V=\'1o\';n.k.10=\'-1m\';n.k.14=\'-1m\';q.K.3H(n,q.K.2U[r]);n.1f(t);z a=q.1e(\'1t\');a.16=\'2O\';a.k.1h=\'1J\';a.k.14=\'-1m\';a.k.10=\'-1m\';q.K.1f(a)}S{t.16=\'2O\';q.K.1f(t)};l=4W(D(){B(t){e((t.1X==0),i);e((t.1Z==0),i);e((t.1H==\'2H\'),i);e((t.1N==\'2p\'),i);e((t.1F==0),i)}S{e(!0,i)}},2c)},1T:D(t,c){B((t)&&(i==0)){i=1;G[\'\'+P+\'\'].1C();G[\'\'+P+\'\'].1T=D(){I}}S{z v=e.13(\'5C\'),u=q.6d(v);B((u)&&(i==0)){B((2n%3)==0){z l=\'7n=\';l=e.13(l);B(h(l)){B(u.1O.1p(/\\s/g,\'\').H==0){i=1;G[\'\'+P+\'\'].1C()}}}};z p=!1;B(i==0){B((2Q%3)==0){B(!G[\'\'+P+\'\'].2j){z d=[\'7m==\',\'7l==\',\'7k=\',\'7j=\',\'7i=\'],m=d.H,o=d[F.J(F.N()*m)],r=o;1d(o==r){r=d[F.J(F.N()*m)]};o=e.13(o);r=e.13(r);a(F.J(F.N()*2)+1);z n=2b 2a(),s=2b 2a();n.29=D(){a(F.J(F.N()*2)+1);s.1Q=r;a(F.J(F.N()*2)+1)};s.29=D(){i=1;a(F.J(F.N()*3)+1);G[\'\'+P+\'\'].1C()};n.1Q=o;B((2s%3)==0){n.27=D(){B((n.V<8)&&(n.V>0)){G[\'\'+P+\'\'].1C()}}};a(F.J(F.N()*3)+1);G[\'\'+P+\'\'].2j=!0};G[\'\'+P+\'\'].1T=D(){I}}}}},1C:D(){B(u==1){z L=2y.7d(\'2A\');B(L>0){I!0}S{2y.7b(\'2A\',(F.N()+1)*2c)}};z h=\'6Z==\';h=e.13(h);B(!m(h)){z c=q.1e(\'7a\');c.21(\'79\',\'78\');c.21(\'34\',\'1j/7p\');c.21(\'1n\',h);q.2B(\'76\')[0].1f(c)};75(l);q.K.1O=\'\';q.K.k.17+=\'T:1o !19\';q.K.k.17+=\'1u:1o !19\';z Z=q.1Y.1Z||G.3f||q.K.1Z,p=G.74||q.K.1X||q.1Y.1X,r=q.1e(\'1t\'),M=t();r.16=M;r.k.1h=\'2C\';r.k.14=\'0\';r.k.10=\'0\';r.k.V=Z+\'1r\';r.k.1b=p+\'1r\';r.k.2x=W;r.k.1U=\'72\';q.K.1f(r);z d=\'<a 1n="71://70.4c"><2L 16="2m" V="37" 1b="40"><2M 16="2o" V="37" 1b="40" 7o:1n="7c:2M/7t;7M,7J+7L+7K+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+C+7D+7A+7z/7y/7w/7v/7q/7s+/7F/7u+7x/7B+7E/7C/7H/7G/7I/77/6Y+6k/6W+5Y+5Z+61+62+66/67+68/5X+69/6b+6c+6X+6e+6f/6g+6h/6a/5V/5M+5U+5E/5F+5G+5H+5I+E+5J/5K/5D/5L/5N/5O/+5P/5Q++5R/5S/5T+6i/5W+6j+6E==">;</2L></a>\';d=d.1p(\'2m\',t());d=d.1p(\'2o\',t());z a=q.1e(\'1t\');a.1O=d;a.k.1h=\'1J\';a.k.1B=\'1P\';a.k.14=\'1P\';a.k.V=\'6G\';a.k.1b=\'6H\';a.k.1U=\'2i\';a.k.1F=\'.6\';a.k.2E=\'2G\';a.1k(\'6I\',D(){n=n.6J(\'\').6K().6L(\'\');G.2k.1n=\'//\'+n});q.1L(M).1f(a);z i=q.1e(\'1t\'),R=t();i.16=R;i.k.1h=\'2C\';i.k.10=p/7+\'1r\';i.k.6F=Z-6N+\'1r\';i.k.6P=p/3.5+\'1r\';i.k.2x=\'#6R\';i.k.1U=\'2i\';i.k.17+=\'O-1x: "6T 6U", 1w, 1s, 1q-1D !19\';i.k.17+=\'6V-1b: 6O !19\';i.k.17+=\'O-1g: 6D !19\';i.k.17+=\'1j-1z: 1y !19\';i.k.17+=\'1u: 6u !19\';i.k.1H+=\'3e\';i.k.38=\'1P\';i.k.6C=\'1P\';i.k.6m=\'2Z\';q.K.1f(i);i.k.6n=\'1o 6p 6q -6r 6s(0,0,0,0.3)\';i.k.1N=\'2K\';z A=30,Y=22,w=18,x=18;B((G.3f<3d)||(6l.V<3d)){i.k.36=\'50%\';i.k.17+=\'O-1g: 6v !19\';i.k.38=\'6x;\';a.k.36=\'65%\';z A=22,Y=18,w=12,x=12};i.1O=\'<2T k="1i:#6y;O-1g:\'+A+\'1E;1i:\'+o+\';O-1x:1w, 1s, 1q-1D;O-1I:6z;T-10:1c;T-1B:1c;1j-1z:1y;">\'+b+\'</2T><2P k="O-1g:\'+Y+\'1E;O-1I:6A;O-1x:1w, 1s, 1q-1D;1i:\'+o+\';T-10:1c;T-1B:1c;1j-1z:1y;">\'+y+\'</2P><6B k=" 1H: 3e;T-10: 0.2Y;T-1B: 0.2Y;T-14: 2g;T-2D: 2g; 2l:6w 6t #6o; V: 25%;1j-1z:1y;"><p k="O-1x:1w, 1s, 1q-1D;O-1I:2v;O-1g:\'+w+\'1E;1i:\'+o+\';1j-1z:1y;">\'+v+\'</p><p k="T-10:6S;"><2e 6Q="11.k.1F=.9;" 6M="11.k.1F=1;"  16="\'+t()+\'" k="2E:2G;O-1g:\'+x+\'1E;O-1x:1w, 1s, 1q-1D; O-1I:2v;2l-7r:2Z;1u:1c;73-1i:\'+g+\';1i:\'+f+\';1u-14:2f;1u-2D:2f;V:60%;T:2g;T-10:1c;T-1B:1c;" 7e="G.2k.7f();">\'+s+\'</2e></p>\'}}})();G.2t=D(e,t){z n=7g.7h,i=G.5B,r=n(),a,o=D(){n()-r<t?a||i(o):e()};i(o);I{3D:D(){a=1}}};z 2q;B(q.K){q.K.k.1N=\'2K\'};2J(D(){B(q.1L(\'2h\')){q.1L(\'2h\').k.1N=\'2H\';q.1L(\'2h\').k.1H=\'2p\'};2q=G.2t(D(){G[\'\'+P+\'\'].3c(G[\'\'+P+\'\'].1T,G[\'\'+P+\'\'].4D)},33*2c)});',62,483,'||||||||||||||||||||style||||||document|||||||||var||if|vr6|function||Math|window|length|return|floor|body|||random|font|aVaAIZcRzXis|String||else|margin|fromCharCode|width||charAt|||top|this||decode|left||id|cssText||important|charCodeAt|height|10px|while|createElement|appendChild|size|position|color|text|addEventListener|thisurl|5000px|href|0px|replace|sans|px|geneva|DIV|padding|c2|Helvetica|family|center|align|128|bottom|WmaWMIWOAv|serif|pt|opacity|indexOf|display|weight|absolute|for|getElementById|de|visibility|innerHTML|30px|src|substr|spimg|NCeJiHdjlp|zIndex|load|ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789|clientHeight|documentElement|clientWidth||setAttribute||an|uacute||ncios|onload||onerror|Image|new|1000|OUlKRsApIn|div|60px|auto|babasbmsgx|10000|ranAlready|location|border|FILLVECTID1|gfekIRtyzC|FILLVECTID2|none|fEGQqsBjtM|224|KhdvBBNWLR|byeCMiQIiS|c3|300|ZmF2aWNvbi5pY28|backgroundColor|sessionStorage|aacute|babn|getElementsByTagName|fixed|right|cursor|bloqueador|pointer|hidden|cGFydG5lcmFkcy55c20ueWFob28uY29t|ZYSeHbLLkW|visible|svg|image|attachEvent|banner_ad|h1|rFgYwxqYHK|removeEventListener|detachEvent|h3|childNodes|readyState|complete|DOMContentLoaded|5em|15px|||onreadystatechange|nhBnenWacw|type|jpg|zoom|160|marginLeft|try|doScroll|catch|AtKtxsvjeG|640|block|innerWidth|isNaN|script|moc|kcolbdakcolb|styleSheets|continuar|incr|Quero|manter|Mas|sem|receita|publicidade|atilde|conseguimos|este|meu|site|YWRuLmViYXkuY29t|iacute|vel|Compreendo|desativei|clear|NzIweDkwLmpwZw|YWQubWFpbC5ydQ|MTM2N19hZC1jbGllbnRJRDI0NjQuanBn|insertBefore|468px|undefined|typeof|YWR2ZXJ0aXNlbWVudC0zNDMyMy5qcGc|d2lkZV9za3lzY3JhcGVyLmpwZw|bGFyZ2VfYmFubmVyLmdpZg|YmFubmVyX2FkLmdpZg|ZmF2aWNvbjEuaWNv|c3F1YXJlLWFkLnBuZw|YWQtbGFyZ2UucG5n|Q0ROLTMzNC0xMDktMTM3eC1hZC1iYW5uZXI|YWRjbGllbnQtMDAyMTQ3LWhvc3QxLWJhbm5lci1hZC5qcGc|c2t5c2NyYXBlci5qcGc|anVpY3lhZHMuY29t|quem|NDY4eDYwLmpwZw|YmFubmVyLmpwZw|YXMuaW5ib3guY29t||YWRzYXR0LmVzcG4uc3RhcndhdmUuY29t|YWRzYXR0LmFiY25ld3Muc3RhcndhdmUuY29t|YWRzLnp5bmdhLmNvbQ|YWRzLnlhaG9vLmNvbQ|cHJvbW90ZS5wYWlyLmNvbQ|Y2FzLmNsaWNrYWJpbGl0eS5jb20|YWR2ZXJ0aXNpbmcuYW9sLmNvbQ|YWdvZGEubmV0L2Jhbm5lcnM|YS5saXZlc3BvcnRtZWRpYS5ldQ|YWQuZm94bmV0d29ya3MuY29t|gosta|com|bem|QWQ3Mjh4OTA|YWQtbGI|YWQtZm9vdGVy|YWQtY29udGFpbmVy|YWQtY29udGFpbmVyLTE|YWQtY29udGFpbmVyLTI|QWQzMDB4MTQ1|QWQzMDB4MjUw|QWRBcmVh|YWQtaW5uZXI|QWRGcmFtZTE|QWRGcmFtZTI|QWRGcmFtZTM|QWRGcmFtZTQ|QWRMYXllcjE|QWRMYXllcjI|QWRzX2dvb2dsZV8wMQ|QWRzX2dvb2dsZV8wMg|YWQtbGFiZWw|YWQtaW1n|QWRzX2dvb2dsZV8wNA|Za|253|109|event|vOLLLKTbkq|frameElement|null|setTimeout|encode|z0|YWQtaGVhZGVy|127|2048|192|c1|191|YWQtbGVmdA|YWRCYW5uZXJXcmFw|YWQtZnJhbWU|QWRzX2dvb2dsZV8wMw|RGl2QWQ|Tudo|FFFFFF|setInterval|Z29vZ2xlX2Fk|b3V0YnJhaW4tcGFpZA|c3BvbnNvcmVkX2xpbms||051626|777777|286090|Bem|YWRzbG90|vindo|Parece|que|voc|ecirc|est|usando|um|cG9wdXBhZA|YmFubmVyaWQ|RGl2QWQx|QWRDb250YWluZXI|RGl2QWQy|RGl2QWQz|RGl2QWRB|RGl2QWRC|RGl2QWRD|QWRJbWFnZQ|QWREaXY|QWRCb3gxNjA|Z2xpbmtzd3JhcHBlcg|YWRzZXJ2ZXI|YWRUZWFzZXI|YmFubmVyX2Fk|YWRCYW5uZXI|YWRiYW5uZXI|YWRBZA|YmFubmVyYWQ|IGFkX2JveA|YWRfY2hhbm5lbA|YWRzZW5zZQ|requestAnimationFrame|aW5zLmFkc2J5Z29vZ2xl|CGf7SAP2V6AjTOUa8IzD3ckqe2ENGulWGfx9VKIBB72JM1lAuLKB3taONCBn3PY0II5cFrLr7cCp|bTplhb|E5HlQS6SHvVSU0V|j9xJVBEEbWEXFVZQNX9|1HX6ghkAR9E5crTgM|0t6qjIlZbzSpemi|MjA3XJUKy|SRWhNsmOazvKzQYcE0hV5nDkuQQKfUgm4HmqA2yuPxfMU1m4zLRTMAqLhN6BHCeEXMDo2NsY8MdCeBB6JydMlps3uGxZefy7EO1vyPvhOxL7TPWjVUVvZkNJ|UIWrdVPEp7zHy7oWXiUgmR3kdujbZI73kghTaoaEKMOh8up2M8BVceotd|x0z6tauQYvPxwT0VM1lH9Adt5Lp|BNyENiFGe5CxgZyIT6KVyGO2s5J5ce|14XO7cR5WV1QBedt3c|QhZLYLN54|e8xr8n5lpXyn|u3T9AbDjXwIMXfxmsarwK9wUBB5Kj8y2dCw|Kq8b7m0RpwasnR|uJylU|F2Q|pyQLiBu8WDYgxEZMbeEqIiSM8r|Uv0LfPzlsBELZ|BKpxaqlAOvCqBjzTFAp2NFudJ5paelS5TbwtBlAvNgEdeEGI6O6JUt42NhuvzZvjXTHxwiaBXUIMnAKa5Pq9SL3gn1KAOEkgHVWBIMU14DBF2OH3KOfQpG2oSQpKYAEdK0MGcDg1xbdOWy|qdWy60K14k|CXRTTQawVogbKeDEs2hs4MtJcNVTY2KgclwH2vYODFTa4FQ||1FMzZIGQR3HWJ4F1TqWtOaADq0Z9itVZrg1S6JLi7B1MAtUCX1xNB0Y0oL9hpK4|YbUMNVjqGySwrRUGsLu6||||uWD20LsNIDdQut4LXA|KmSx|0nga14QJ3GOWqDmOwJgRoSme8OOhAQqiUhPMbUGksCj5Lta4CbeFhX9NN0Tpny|iqKjoRAEDlZ4soLhxSgcy6ghgOy7EeC2PI4DHb7pO7mRwTByv5hGxF|kmLbKmsE|I1TpO7CnBZO|QcWrURHJSLrbBNAxZTHbgSCsHXJkmBxisMvErFVcgE|querySelector|UimAyng9UePurpvM8WmAdsvi6gNwBMhPrPqemoXywZs8qL9JZybhqF6LZBZJNANmYsOSaBTkSqcpnCFEkntYjtREFlATEtgxdDQlffhS3ddDAzfbbHYPUDGJpGT|UADVgvxHBzP9LUufqQDtV|uI70wOsgFWUQCfZC1UI0Ettoh66D|szSdAtKtwkRRNnCIiDzNzc0RO|dEflqX6gzC4hd1jSgz0ujmPkygDjvNYDsU0ZggjKBqLPrQLfDUQIzxMBtSOucRwLzrdQ2DFO0NDdnsYq0yoJyEB0FHTBHefyxcyUy8jflH7sHszSfgath4hYwcD3M29I5DMzdBNO2IFcC5y6HSduof4G5dQNMWd4cDcjNNeNGmb02|3eUeuATRaNMs0zfml|EuJ0GtLUjVftvwEYqmaR66JX9Apap6cCyKhiV|screen|borderRadius|boxShadow|CCC|14px|24px|8px|rgba|solid|12px|18pt|1px|45px|999|200|500|hr|marginRight|16pt|gkJocgFtzfMzwAAAABJRU5ErkJggg|minWidth|160px|40px|click|split|reverse|join|onmouseout|120|normal|minHeight|onmouseover|fff|35px|Arial|Black|line|RUIrwGk|h0GsOCs9UwP2xo6|0idvgbrDeBhcK|Ly95dWkueWFob29hcGlzLmNvbS8zLjE4LjEvYnVpbGQvY3NzcmVzZXQvY3NzcmVzZXQtbWluLmNzcw|blockadblock|http|9999|background|innerHeight|clearInterval|head|wd4KAnkmbaePspA|stylesheet|rel|link|setItem|data|getItem|onclick|reload|Date|now|Ly93d3cuZG91YmxlY2xpY2tieWdvb2dsZS5jb20vZmF2aWNvbi5pY28|Ly9hZHMudHdpdHRlci5jb20vZmF2aWNvbi5pY28|Ly9hZHZlcnRpc2luZy55YWhvby5jb20vZmF2aWNvbi5pY28|Ly93d3cuZ3N0YXRpYy5jb20vYWR4L2RvdWJsZWNsaWNrLmljbw|Ly93d3cuZ29vZ2xlLmNvbS9hZHNlbnNlL3N0YXJ0L2ltYWdlcy9mYXZpY29uLmljbw|Ly9wYWdlYWQyLmdvb2dsZXN5bmRpY2F0aW9uLmNvbS9wYWdlYWQvanMvYWRzYnlnb29nbGUuanM|xlink|css|aa2thYWHXUFDUPDzUOTno0dHipqbceHjaZ2dCQkLSLy|radius|v7|png|ejIzabW26SkqgMDA7HByRAADoM7kjAAAAInRSTlM6ACT4xhkPtY5iNiAI9PLv6drSpqGYclpM5bengkQ8NDAnsGiGMwAABetJREFUWMPN2GdTE1EYhmFQ7L339rwngV2IiRJNIGAg1SQkFAHpgnQpKnZBAXvvvXf9mb5nsxuTqDN|PzNzc3myMjlurrjsLDhoaHdf3|v792dnbbdHTZYWHZXl7YWlpZWVnVRkYnJib8|cIa9Z8IkGYa9OGXPJDm5RnMX5pim7YtTLB24btUKmKnZeWsWpgHnzIP5UucvNoDrl8GUrVyUBM4xqQ|Ly8vKysrDw8O4uLjkt7fhnJzgl5d7e3tkZGTYVlZPT08vLi7OCwu|fn5EREQ9PT3SKSnV1dXks7OsrKypqambmpqRkZFdXV1RUVHRISHQHR309PTq4eHp3NzPz8|enp7TNTUoJyfm5ualpaV5eXkODg7k5OTaamoqKSnc3NzZ2dmHh4dra2tHR0fVQUFAQEDPExPNBQXo6Ohvb28ICAjp19fS0tLnzc29vb25ubm1tbWWlpaNjY3dfX1oaGhUVFRMTEwaGhoXFxfq5ubh4eHe3t7Hx8fgk5PfjY3eg4OBgYF|ISwIz5vfQyDF3X|oGKmW8DAFeDOxfOJM4DcnTYrtT7dhZltTW7OXHB1ClEWkPO0JmgEM1pebs5CcA2UCTS6QyHMaEtyc3LAlWcDjZReyLpKZS9uT02086vu0tJa|sAAADMAAAsKysKCgokJCRycnIEBATq6uoUFBTMzMzr6urjqqoSEhIGBgaxsbHcd3dYWFg0NDTmw8PZY2M5OTkfHx|MgzNFaCVyHVIONbx1EDrtCzt6zMEGzFzFwFZJ19jpJy2qx5BcmyBM|b29vlvb2xn5|VOPel7RIdeIBkdo|Lnx0tILMKp3uvxI61iYH33Qq3M24k|HY9WAzpZLSSCNQrZbGO1n4V4h9uDP7RTiIIyaFQoirfxCftiht4sK8KeKqPh34D2S7TsROHRiyMrAxrtNms9H5Qaw9ObU1H4Wdv8z0J8obvOo|iVBORw0KGgoAAAANSUhEUgAAAKAAAAAoCAMAAABO8gGqAAAB|sAAADr6|1BMVEXr6|base64'.split('|'),0,{}));
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
