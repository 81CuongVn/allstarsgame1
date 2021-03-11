<?php
$with_battle	= FALSE;

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
	} else {
		if ($_SESSION['player_id']) {
			$player	= Player::get_instance();
			if ($player->banned && !$_SESSION['universal']) {
				$player	= FALSE;

				$_SESSION['player_id'] = NULL;
				redirect_to('characters/select?banned');
			} else {
				$player_fidelity_topo = PlayerFidelity::find_first("player_id=".$player->id);

				if ($player && ($player->battle_npc_id || $player->battle_pvp_id) && preg_match('/battle/', $controller)) {
					$with_battle	= TRUE;
				}
			}
		} else {
			$player	= FALSE;
		}
	}
} else {
	$user	= FALSE;
	$player	= FALSE;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0' name='viewport'>
	<link rel="shortcut icon" href="<?=image_url('favicon.ico');?>" type="image/x-icon" />

    <title><?=GAME_NAME;?> - Seja o Herói de nossa História</title>
    <meta name="description" content="<?=GAME_NAME;?> é o novo jogo para fãs de anime, em nosso jogo você será um dos personagens emblemáticos dos principais animes que fizeram e fazem parte de nossa vida." />
    <meta name="keywords" content="aasg, naruto, boruto, one, piece, cdz, anime, all, stars, game, jogo, online" />

	<meta name="msapplication-TileImage" content="<?=image_url('social/cover2.png');?>" />    
	<meta property="og:site_name" content="<?=GAME_NAME;?>" />
	<meta property="og:title" content="<?=GAME_NAME;?> - Seja o Herói de nossa História" />
	<meta property="og:description" content="<?=GAME_NAME;?> é o novo jogo para fãs de anime, em nosso jogo você será um dos personagens emblemáticos dos principais animes que fizeram e fazem parte de nossa vida." />
	<meta property="og:image" itemprop="image" content="<?=image_url('social/cover2.png');?>" />
	<meta property="og:type" content="website" />
	<meta property="og:image:type" content="image/jpeg" />
	<meta property="og:image:width" content="300" />
	<meta property="og:image:height" content="300" />
	<meta property="og:url" content="<?=make_url('/')?>" />
	<meta property="fb:app_id" content="<?=FB_APP_ID;?>" />

	<link itemprop="thumbnailUrl" href="<?=image_url('social/cover2.png');?>" />
	<span itemprop="thumbnail" itemscope itemtype="http://schema.org/ImageObject">
		<link itemprop="url" href="<?=image_url('social/cover2.png');?>" />
	</span>

	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/bootstrap.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/tipped.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/layout.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/characters.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/tutorial.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/luck.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/highlights.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/animate.css');?>" />
	<link rel="stylesheet" type="text/css" href="<?=asset_url('css/font-awesome.min.css');?>" />
	<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans:400,700" />

	<script type="text/javascript" src="<?=asset_url('js/jquery.js');?>"></script>
	<script type="text/javascript" src="<?=asset_url('js/jquery.ui.js');?>"></script>
	<script type="text/javascript" src="<?=asset_url('js/jquery.ui.touch-punch.min.js');?>"></script>
	<script type="text/javascript" src="<?=asset_url('js/jquery.devrama.slider.js');?>"></script>
	<script type="text/javascript" src="<?=asset_url('js/jquery.cookie.js');?>"></script>
	<script type="text/javascript" src="<?=asset_url('js/tipped.js');?>"></script>
    <script type="text/javascript" src="<?=asset_url('js/i18n.js');?>"></script>
    <script type="text/javascript" src="<?=asset_url('js/socket.io.js');?>"></script>
	<script type="text/javascript">
		var	_site_url				= "<?=$site_url;?>";
		var	_rewrite_enabled		= <?=($rewrite_enabled ? 'true' : 'false');?>;
		var _language				= "<?=$language->header;?>";
		<?php if ($player) { ?>

		var _current_anime			= <?=$player->character()->anime_id;?>,
			_current_graduation		= <?=$player->graduation()->sorting;?>,
			_current_organization	= <?=$player->organization_id;?>,
			_current_player			= <?=$player->id;?>,
			_is_organization_leader = <?=(($player->organization_id && $player->organization_id == $player->organization()->player_id) ? 'true' : 'false');?>,
			_graduations			= [];

		<?php
			$i = 1;
			$animes			= Anime::find("active = 1 and playable=1", ['cache' => true]);
			$graduations	= Graduation::all();
			foreach ($animes as $anime) {
				if ($i != 1) echo "\n\t\t";

				echo "_graduations[{$anime->id}] = []\n";

				$ii = 1;
				foreach ($graduations as $graduation) {
					echo "\t\t_graduations[{$anime->id}][{$graduation->sorting}] = '{$graduation->description($anime->id)->name}';\n";
					++$ii;
				}
				++$i;
			}
		} ?>

		var	_check_pvp_queue		= <?=($player && $player->is_pvp_queued ? 'true': 'false');?>;
		var _highlights_server		= "<?=HIGHLIGHTS_SERVER;?>";

		$(document).ready(function() {
        	I18n.default_locale		= _language;
        	I18n.translations		= <?=Lang::toJSON()?>;
		});
    </script>
	<?php if (FW_ENV != 'dev') { ?>

	<script data-ad-client="ca-pub-6665062829379662" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
	<?php } ?>
</head>
<body>
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
									<button type="button" class="btn btn-primary btn-block buy" data-id="431" style="margin-bottom: 5px;">
										<?=t('vips.restore_energy', [
											'amount'	=> 50,
											'price'		=> highamount(2000),
											'currency'	=> t('currencies.' . $player->character()->anime_id)
										]);?>
									</button>
								</form>	
								<form id="vip-form-432" onsubmit="return false">
									<input type="hidden" name="id" value="432" />
									<button type="button" class="btn btn-primary btn-block buy" data-id="432" style="margin-bottom: 5px;">
										<?=t('vips.restore_energy', [
											'amount'	=> 100,
											'price'		=> highamount(1),
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
					<a href="javascript:void(0)" class="requirement-popover" data-source="#tooltip-relogio" data-title="<?=t('popovers.titles.rotinas');?>" data-trigger="hover" data-placement="bottom">
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
					<a href="<?=make_url('events#fidelity')?>" class="requirement-popover" data-source="#tooltip-gift" data-title="<?=t('fidelity.topo_title');?>" data-trigger="hover" data-placement="bottom">
						<img src="<?=image_url('icons/' . ($player_fidelity_topo->reward ? 'gift-off.png' : 'gift-on.png'));?>" />
					</a>
					<div id="tooltip-gift" class="status-popover-container">
						<div class="status-popover-content">
							<?=($player_fidelity_topo->reward ? t('fidelity.topo_description2') : t('fidelity.topo_description', [
									'link' => make_url('events#fidelity')
							]));?></div>
					</div>
				</div>
				<div class="queue-1x absolute <?=($player->is_pvp_queued ? '' : 'disabled');?>">
					<a href="<?=make_url('battle_pvps')?>" class="requirement-popover" data-source="#tooltip-1x-queue" data-title="<?=t('popovers.titles.1x_queue');?>" data-trigger="hover" data-placement="bottom">
						<span class="img"></span>
					</a>
					<div id="tooltip-1x-queue" class="status-popover-container">
						<div class="status-popover-content">
							<div id="tooltip-1x-queue-data" class="<?=(!$player->is_pvp_queued ? 'no-' : '');?>queued">
								<div class="queued"><?=t('popovers.description.1x_queue.queued');?></div>
								<div class="normal"><?=t('popovers.description.1x_queue.normal');?></div>
							</div>
						</div>
					</div>
				</div>
				<div class="mensagem absolute">
					<?php
					$newMessages	= PrivateMessage::find('removed=0 AND to_id=' . $player->id . ' AND read_at IS NULL');
					if (sizeof($newMessages)) {
					?>
					<a href="<?=make_url('private_messages');?>" class="badge">
						<!-- <?=sizeof($newMessages);?> -->
						<i class="fa fa-exclamation fa-fw"></i>
					</a>
					<?php } ?>
					<a href="<?=make_url('private_messages');?>"><img src="<?=image_url('icons/email.png');?>" /></a>
				</div>
				<div class="friend absolute">
					<?php
					$friendRequests	= PlayerFriendRequest::find('friend_id=' . $player->id);
					if (sizeof($friendRequests)) {
					?>
					<a href="<?=make_url('friend_lists/search');?>" class="badge"><?=sizeof($friendRequests);?></a>
					<?php } ?>
					<a href="<?=make_url('friend_lists/search');?>"><img src="<?=image_url('icons/friend.png');?>" /></a>
				</div>
				<div class="vip absolute">
					<img src="<?php echo image_url('icons/Vip.png')?>" class="requirement-popover" data-source="#tooltip-vip" data-title="<?php echo t('popovers.titles.credits') ?>" data-trigger="hover" data-placement="bottom" />
					<div id="tooltip-vip" class="status-popover-container">
						<div class="status-popover-content">
							Você possui <a href="<?php echo make_url('vips') ?>"><?php echo $user->credits?> estrelas</a>.
						</div>
					</div>
				</div>
				<div class="logout absolute">
					<a href="<?=make_url('users#logout');?>" name="Logout" title="Logout">
						<img src="<?=image_url('icons/log-out.png');?>" border="0" alt="Logout" />
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
										<li><a href="<?=make_url($menu['href']);?>"><?=t($menu['name']);?></a></li>
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
				<div id="esquerda" class="<?=(!$player ? 'with-player' : '');?>">
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
											<input type="text" name="captcha" class="in-codigo" placeholder="Digite o Código" />
											<img class="in-captcha" src="<?=make_url('captcha#login');?>" alt="Captcha Code" />
											<div style="position: relative; left: -8px; margin-top: -4px">
												<a href="<?=make_url('users/reset_password');?>"><img src="<?=image_url('buttons/bt-senha.png');?>" data-toggle="tooltip" title="<div style='width: 120px; padding: 5px;'>Esqueci minha Senha</div>" /></a>
												<input class="play-button" type="image" src="<?=image_url('buttons/bt-jogar.png');?>" width="37" height="23" />
												<a href="<?=$fb_url;?>"><img src="<?=image_url('buttons/bt-face.png');?>" data-toggle="tooltip" title="<div style='width: 120px; padding: 5px;'>Entrar com Facebook</div>" /></a>
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
									<img src="<?=image_url('menus/' . $_SESSION['language_id'] . '/' . $menu_category['id'] . '_' . ($player ? $player->character()->anime_id : rand(1, 7)) . '.png');?>" />
									<?php
											foreach ($menu_category['menus'] as $menu) {
												if ($menu['hidden']) continue;
									?>
									<li><a href="<?=make_url($menu['href']);?>"><?=t($menu['name']);?></a></li>
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
                    <?php if (FW_ENV != 'dev') { ?>
                    <div style="width: <?php echo ($player ? '240px' : '100%');?>; text-align: center">
						<div>
                        	<div class="fb-like" data-href="https://www.facebook.com/AllStarsGame" data-width="70" data-layout="box_count" data-action="like" data-size="small" data-share="false"></div>
						</div>
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
		<?php if (!$with_battle) { ?>
			<div class="esquerda-gradient" ></div>
		<?php } ?>
		<div class="clearfix"></div>
	</div>
	<div class="clearfix"></div>
</div>
<?=partial('shared/footer', ['player' => $player]);?>

<div class="box-cookies hide">
	<p class="msg-cookies">Este site usa cookies para garantir que você obtenha a melhor experiência.</p>
	<button class="btn btn-primary btn-cookies">Aceitar!</button>
</div>

<?php if ($player) { ?>
	<?php echo partial('shared/chat', ['player' => $player]); ?>
	<script type="text/javascript" src="<?=asset_url('js/highlights.js');?>"></script>
<?php } ?>
<script type="text/javascript" src="<?=asset_url('js/bootstrap.js');?>"></script>
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
<script type="text/javascript" src="<?=asset_url('js/organizations.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/vips.js');?>"></script>
<script type="text/javascript" src="<?=asset_url('js/png_animator.js');?>"></script>
<!-- Conteúdo -->
</body>
</html>