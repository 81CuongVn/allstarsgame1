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
		<p>Perfil de <?=($profile ? $profile->name : '???');?></p>
		<span><a href="<?=make_url('/');?>">Página Principal</a> <b>&gt;&gt;</b> Perfil de <?=($profile ? $profile->name : '???');?></span>
	</div>
	<?php if ($seeAttributes && !$antSpy) { ?>
		<div class="msg-container">
			<div class="msg_top"></div>
			<div class="msg_repete">
				<div class="msg" style="background: url(<?=image_url('msg/' . $player->character()->anime_id . '-4.png');?>); background-repeat: no-repeat;">
				</div>
				<div class="msgb" style="position:relative; margin-left: 231px; text-align: left; top: -37px">
					<b><?=$seeAttributes->item()->description()->name;?></b>
					<div class="content">
						<?=$seeAttributes->item()->description()->description;?><br /><br />
						<?=t('global.remaining_uses', [
							'remaining'	=> highamount($seeAttributes->quantity)
						]);?>
					</div>
				</div>
			</div>
			<div class="msg_bot"></div>
			<div class="msg_bot2"></div>
		</div><br />
	<?php } ?>
	<?php if ($antSpy) { ?>
		<div class="alert alert-danger text-center">
			<b>Este jogador possui o item <u>Anti-Espionagem</u>, portanto no momento não pode ser espionado!</b>
		</div>
	<?php } ?>
	<div style="width: 730px; position: relative;">
		<div style="position: relative; float: left; width:365px;">
			<div class="tutorial_formulas">
				<div class="titulo-home"><p>Fórmulas</p></div>
				<?php foreach ($formulas as $_ => $formula) { ?>
					<div class="bg_td">
						<div class="amarelo atr_float" style="width: 130px; text-align:left; padding-left:16px; text-align: center"><?=$formula;?></div>
						<div class="atr_float" style="width: 20px; text-align:left;margin-left: 6px;">
							<img src="<?=image_url('icons/' . $_ . '.png');?>" style="position: relative; top: -5px; left: 2px;" class="requirement-popover" data-source="#attribute-tooltip-<?=$_;?>" data-title="<?=t('formula.tooltip.title.' . $_);?>" data-trigger="hover" data-placement="bottom" />
							<div id="attribute-tooltip-<?=$_;?>" class="status-popover-container">
								<div class="status-popover-content"><?=t('formula.tooltip.description.' . $_);?></div>
							</div>
						</div>
						<div class="atr_float" style="margin-top: 7px; margin-left: 20px">
							<?=exp_bar(($seeAttributes && !$antSpy ? $profile->{$_}() : 0), $max, 175, ($seeAttributes && !$antSpy ? $profile->{$_}() : '???'));?>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>

	<div class="h-combates">
		<div style="width: 341px; text-align: center; padding-top: 12px"><b class="amarelo" style="font-size:13px">Resumo de Combate</b></div>
		<div style="width: 341px; text-align: center; padding-top: 22px; font-size: 12px !important; line-height: 15px;">
			<span class="verde"><?=t('characters.status.wins_npc');?>:</span> <?=highamount($profile->wins_npc);?> <br />
			<span class="verde"><?=t('characters.status.wins_pvp');?>:</span> <?=highamount($profile->wins_pvp);?> <br />
			<span class="vermelho"><?=t('characters.status.losses_npc');?>:</span> <?=highamount($profile->losses_npc);?> <br />
			<span class="vermelho"><?=t('characters.status.losses_pvp');?>:</span> <?=highamount($profile->losses_pvp);?> <br />
			<span><?=t('characters.status.draws_npc');?>:</span> <?=highamount($profile->draws_npc);?> <br />
			<span><?=t('characters.status.draws_pvp');?>:</span> <?=highamount($profile->draws_pvp);?>
		</div>
	</div>
	<div class="h-missoes tutorial_missoes">
		<div style="width: 341px; text-align: center; padding-top: 12px"><b class="amarelo" style="font-size:13px">Missões Completas</b></div>
		<div style="width: 341px; text-align: center; padding-top: 22px; font-size: 12px !important; line-height: 15px;">
			<span class="verde"><?=t('characters.status.time');?>:</span> <?=highamount($quest_counters->time_total);?><br />
			<span class="verde"><?=t('characters.status.especiais');?>:</span> <?=highamount($quest_counters->pvp_total);?><br />
			<span class="verde"><?=t('characters.status.daily');?>:</span> <?=highamount($quest_counters->daily_total);?><br />
			<span class="verde"><?=t('characters.status.pet');?>:</span> <?=highamount($quest_counters->pet_total);?><br />
			<span class="verde"><?=t('characters.status.account');?>:</span> <?=highamount($user_quest_counters->daily_total);?><br />
		</div>
	</div>
<?php } ?>
