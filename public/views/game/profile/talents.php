<?=partial('profile/left', [
	'player'	=> $profile
]);?>
<?php if (!$profile) { ?>
	<?=partial('shared/title', ['title' => 'profile.unknow.title', 'place' => 'profile.unknow.title']);?>
	<?=partial('shared/info', [
		'id'		=> 2,
		'title'		=> 'profile.unknow.title',
		'message'	=> t('profile.unknow.description')
	]);?>
<?php } else { ?>
	<div class="titulo-secao">
		<p>Talentos de <?=$profile->name;?></p>
		<span><a href="<?=make_url('/');?>">Página Principal</a> <b>&gt;&gt;</b> Talentos de <?=$profile->name;?></span>
	</div>

	<?php if ($seeTalents) { ?>
		<div class="msg-container">
			<div class="msg_top"></div>
			<div class="msg_repete">
				<div class="msg" style="background: url(<?=image_url('msg/' . $player->character()->anime_id . '-4.png');?>); background-repeat: no-repeat;">
				</div>
				<div class="msgb" style="position:relative; margin-left: 231px; text-align: left; top: -37px">
					<b><?=$seeTalents->item()->description()->name;?></b>
					<div class="content">
						<?=$seeTalents->item()->description()->description;?><br /><br />
						<?=t('global.remaining_uses', [
							'remaining'	=> highamount($seeTalents->quantity)
						]);?>
					</div>
				</div>
			</div>
			<div class="msg_bot"></div>
			<div class="msg_bot2"></div>
		</div><br />
	<?php } ?>
	<?php if ($seeTalents && !$antSpy) { ?>
		<?php foreach ($list as $level => $items) { ?>
			<div class="talents tutorial-<?=$level;?>">
				<div class="level <?=($profile_user->level >= $level ? 'on' : '');?>">
					<p><?=$level;?></p>
				</div>
				<?php foreach ($items as $item) { ?>
					<div class="item <?=($profile->has_item($item) ? 'on' : '');?>" style="cursor: inherit;">
						<div class="image">
							<img style="cursor: help;" src="<?=image_url($item->image(true));?>" class="technique-popover" data-source="#talent-content-<?=$item->id;?>" data-title="<?=$item->description()->name;?>" data-trigger="hover" data-placement="bottom" />
							<div class="technique-container" id="talent-content-<?=$item->id;?>">
								<?=$item->tooltip();?>
							</div>
						</div>
						<div class="description">
							<p><?=$item->description()->name;?></p>
						</div>
					</div>
				<?php } ?>
			</div>
		<?php } ?>
	<?php } else { ?>
		<?php if (!$antSpy) { ?>
			<div class="alert alert-warning text-center">
				<b>Você não possui o item <u>Espionagem de Talentos</u>, necessario para espionar os talentos deste jogador! Clique <a href="<?=make_url('vips')?>">aqui</a> para adquirir.</b>
			</div>
		<?php } else { ?>
			<div class="alert alert-danger text-center">
				<b>Este jogador possui o item <u>Anti-Espionagem</u>, portanto no momento não pode ser espionado!</b>
			</div>
		<?php } ?>
	<?php } ?>
<?php } ?>
