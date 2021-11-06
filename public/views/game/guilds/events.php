<?php echo partial('shared/title', array('title' => 'guilds.events.title', 'place' => 'guilds.events.title')) ?>
<?php if (FW_ENV != 'dev') { ?>
	<!-- AASG - Guild -->
	<ins class="adsbygoogle"
		style="display:inline-block;width:728px;height:90px"
		data-ad-client="ca-pub-6665062829379662"
		data-ad-slot="7693601385"></ins>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
	</script><br />
<?php } ?>
<?php
	echo partial('shared/info', array(
		'id'		=> 1,
		'title'		=> 'guilds.events.title2',
		'message'	=> t('guilds.events.description2')
	));
?>
<div id="guild-event-list">
	<?php foreach ($events as $event) { ?>
		<?php $unlocked = $event->unlocked($player->guild_id, $event->id, $player->id); ?>
		<div class="group">
			<div class="technique-popover buff" data-source="#challenges-container-<?=$event->id;?>" data-title="<?=$event->description()->name;?>" data-trigger="click" data-placement="bottom">
			<div class="<?=($unlocked ? '' : 'efeito');?>">
				<?=$event->image();?>
			</div>
			<div class="clearfix"></div>
			<div class="name-anime"><?=$event->description()->name;?></div>
			<div class="clearfix"></div>
            <div class="buttons">
				<?php if ($unlocked) { ?>
					<a class="btn btn-success invite" data-event="<?=$event->id;?>">Começar Dungeon</a>
				<?php } else { ?>
					<?php if ($event->currency) { ?>
						<a class="btn btn-primary unlock" data-event="<?=$event->id;?>" data-mode="3">
							<?=t('history_mode.index.unlock_treasure', [
								'amount'	=> highamount($event->treasure)
							]);?>
						</a>
					<?php } ?>
					<?php if ($event->credits) { ?>
						<a class="btn btn-warning unlock" data-event="<?=$event->id;?>" data-mode="2">
							<?=t('history_mode.index.unlock_credits', [
								'amount'	=> $event->credits
							]);?>
						</a>
					<?php } ?>
				<?php } ?>
            </div>
			<div id="challenges-container-<?=$event->id;?>" class="technique-container">
				<b class="amarelo" style="font-size:14px">Requerimentos:</b><br />
				<i class="fa fa-arrow-right fa-fw cinza"></i> <?=highamount($event->players_required);?> Membros<br />
				<i class="fa fa-arrow-right fa-fw cinza"></i> Terminar em até <?=format_time($event->require_time)['string'];?><br /><br />

				<b class="amarelo" style="font-size:14px">Condições de Vitória:</b><br />
				<?php if ($event->require_npc) { ?>
					<i class="fa fa-arrow-right fa-fw cinza"></i> Derrotar <?=highamount($event->require_npc);?> NPCs<br />
				<?php } ?>
				<?php if ($event->require_boss) { ?>
					<i class="fa fa-arrow-right fa-fw cinza"></i> Derrotar <?=highamount($event->require_boss);?> Boss<br />
				<?php } ?>

				<?php if ($rewards = $event->reward()) { ?>
					<br /><b class="amarelo" style="font-size:14px">Recompensas:</b><br />
					<?php if ($rewards->exp) { ?>
						<i class="fa fa-arrow-right fa-fw cinza"></i> <?=highamount($rewards->exp);?> Exp<br />
					<?php } ?>
					<?php if ($rewards->currency) { ?>
						<i class="fa fa-arrow-right fa-fw cinza"></i> <?=highamount($rewards->currency);?> <?=t('currencies.' . $player->character()->anime_id);?><br />
					<?php } ?>
					<?php if ($rewards->credits) { ?>
						<i class="fa fa-arrow-right fa-fw cinza"></i> <?=highamount($rewards->credits);?> <?=t('treasure.show.credits');?><br />
					<?php } ?>
					<?php if ($rewards->equipment && $rewards->equipment == 1) { ?>
						<i class="fa fa-arrow-right fa-fw cinza"></i> <?=t('treasure.show.equipment1');?><br />
					<?php } ?>
					<?php if ($rewards->equipment && $rewards->equipment == 2) { ?>
						<i class="fa fa-arrow-right fa-fw cinza"></i> <?=t('treasure.show.equipment2');?><br />
					<?php } ?>
					<?php if ($rewards->equipment && $rewards->equipment == 3) { ?>
						<i class="fa fa-arrow-right fa-fw cinza"></i> <?=t('treasure.show.equipment3');?><br />
					<?php } ?>
					<?php if ($rewards->equipment && $rewards->equipment == 4) { ?>
						<i class="fa fa-arrow-right fa-fw cinza"></i> <?=t('treasure.show.equipment4');?><br />
					<?php } ?>
					<?php if ($rewards->equipment && $rewards->equipment == 5) { ?>
						<i class="fa fa-arrow-right fa-fw cinza"></i> <?=t('treasure.show.equipment5');?><br />
					<?php } ?>
					<?php if ($rewards->pets && $rewards->item_id) { ?>
						<i class="fa fa-arrow-right fa-fw cinza"></i> <?=t('treasure.show.pet');?> "<?=Item::find($rewards->item_id)->description()->name;?>"<br />
					<?php } ?>
					<?php if (!$rewards->pets && $rewards->item_id) { ?>
						<i class="fa fa-arrow-right fa-fw cinza"></i> <?=highamount($rewards->quantity);?>x "<?=Item::find($rewards->item_id)->description()->name;?>"<br />
					<?php } ?>
					<?php if ($rewards->character_theme_id) { ?>
						<i class="fa fa-arrow-right fa-fw cinza"></i> <?=t('treasure.show.theme');?> "<?=CharacterTheme::find($rewards->character_theme_id)->description()->name;?>"<br />
					<?php } ?>
					<?php if ($rewards->character_id) { ?>
						<i class="fa fa-arrow-right fa-fw cinza"></i> <?=t('treasure.show.character');?> "<?=Character::find($rewards->character_id)->description()->name;?>"<br />
					<?php } ?>
					<?php if ($rewards->headline_id) { ?>
						<i class="fa fa-arrow-right fa-fw cinza"></i> <?=t('treasure.show.headline');?> "<?=Headline::find($rewards->headline_id)->description()->name;?>"<br />
					<?php } ?>
				<?php } else { ?>
					Nenhuma recompensa
				<?php } ?>
			</div>
			</div>
		</div>
	<?php } ?>
</div>
