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
		<p>Equipamentos de <?=($profile ? $profile->name : '???');?></p>
		<span><a href="<?=make_url('/');?>">Página Principal</a> <b>&gt;&gt;</b> Equipamentos de <?=($profile ? $profile->name : '???');?></span>
	</div>
	<?php if ($seeEquipments) { ?>
		<div class="msg-container">
			<div class="msg_top"></div>
			<div class="msg_repete">
				<div class="msg" style="background: url(<?=image_url('msg/' . $player->character()->anime_id . '-4.png');?>); background-repeat: no-repeat;">
				</div>
				<div class="msgb" style="position:relative; margin-left: 231px; text-align: left; top: -37px">
					<b><?=$seeEquipments->item()->description()->name;?></b>
					<div class="content">
						<?=$seeEquipments->item()->description()->description;?><br /><br />
						<?=t('global.remaining_uses', [
							'remaining'	=> highamount($seeEquipments->quantity)
						]);?>
					</div>
				</div>
			</div>
			<div class="msg_bot"></div>
			<div class="msg_bot2"></div>
		</div><br />
	<?php } ?>
	<?php if ($seeEquipments && !$antSpy) { ?>
		<div id="position-container" class="anime-<?=$anime->id;?> position-container-<?=$anime->id;?>" style="background-image: url(<?=image_url('equipments/' . $anime->id . '/background.jpg');?>)">
			<?php foreach ($positions as $position) { ?>
				<?php
				$equipped	= $profile->get_equipment_at_slot($position->slot_name);
				if(!$equipped) {
					if (in_array($anime->id, [6])) {
						$background	= 'url(' . image_url('equipments/' . $anime->id . '/' . $profile->character_id . '/' . $position->slot_name . '.png') . ')';
					} else {
						$background	= 'url(' . image_url('equipments/' . $anime->id . '/' . $position->slot_name . '.png') . ')';
					}
				} else {
					$item		= $equipped->item();
					$background	= 'url(' . image_url($item->image(true)) . ')';
				}
				?>
				<div class="<?=($equipped ? "equipped" : "");?> is-profile slot slot-<?=$position->slot_name;?>" style="cursor: help; top: <?=$position->y;?>px; left: <?=$position->x;?>px; background-image: <?=$background;?>" data-id="<?=($equipped ? $equipped->id : 0)?>"></div>
				<?php if ($equipped) { ?>
					<?=$item->tooltip();?>
				<?php } ?>
			<?php } ?>
		</div>
	<?php } else { ?>
		<?php if (!$antSpy) { ?>
			<div class="alert alert-warning text-center">
				<b>Você não possui o item <u>Espionagem de Equipamentos</u>, necessario para espionar os equipamentos deste jogador! Clique <a href="<?=make_url('vips')?>">aqui</a> para adquirir.</b>
			</div>
		<?php } else { ?>
			<div class="alert alert-danger text-center">
				<b>Este jogador possui o item <u>Anti-Espionagem</u>, portanto no momento não pode ser espionado!</b>
			</div>
		<?php } ?>
	<?php } ?>
<?php } ?>
