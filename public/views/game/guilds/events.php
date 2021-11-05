<?php echo partial('shared/title', array('title' => 'battles.npc.title', 'place' => 'battles.npc.title')) ?>
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
		'title'		=> 'ranked.liga',
		'message'	=> t('battles.ranked.description2')
	));
?>
<div id="guild-event-list">
	<?php foreach ($events as $event) { ?>
		<?php $unlocked = $event->unlocked($player->guild_id, $event->id, $player->id); ?>
		<div class="group">
			<div class="technique-popover buff" data-source="#challenges-container-<?=$event->id;?>" data-title="Recompensas" data-trigger="click" data-placement="bottom">
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
						<a class="btn btn-primary unlock" data-event="<?=$event->id;?>" data-mode="1">
							<?=t('history_mode.index.unlock_currency', [
								'amount'	=> highamount($event->currency),
								'currency'	=> t('currencies.' . $player->character()->anime_id)
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
				<?php if ($rewards = $event->reward()) { ?>
					<?php if ($rewards->exp) { ?>
						<?=highamount($rewards->exp);?> Exp<br />
					<?php } ?>
					<?php if ($rewards->currency) { ?>
						<?=highamount($rewards->currency);?> <?=t('currencies.' . $player->character()->anime_id);?><br />
					<?php } ?>
					<?php if ($rewards->credits) { ?>
						<?=highamount($rewards->credits);?> <?=t('treasure.show.credits');?><br />
					<?php } ?>
					<?php if ($rewards->equipment && $rewards->equipment == 1) { ?>
						<?=t('treasure.show.equipment1');?><br />
					<?php } ?>
					<?php if ($rewards->equipment && $rewards->equipment == 2) { ?>
						<?=t('treasure.show.equipment2');?><br />
					<?php } ?>
					<?php if ($rewards->equipment && $rewards->equipment == 3) { ?>
						<?=t('treasure.show.equipment3');?><br />
					<?php } ?>
					<?php if ($rewards->equipment && $rewards->equipment == 4) { ?>
						<?=t('treasure.show.equipment4');?><br />
					<?php } ?>
					<?php if ($rewards->equipment && $rewards->equipment == 5) { ?>
						<?=t('treasure.show.equipment5');?><br />
					<?php } ?>
					<?php if ($rewards->pets && $rewards->item_id) { ?>
						<?=t('treasure.show.pet');?> "<?=Item::find($rewards->item_id)->description()->name;?>"<br />
					<?php } ?>
					<?php if (!$rewards->pets && $rewards->item_id) { ?>
						<?=highamount($rewards->quantity);?>x "<?=Item::find($rewards->item_id)->description()->name;?>"<br />
					<?php } ?>
					<?php if ($rewards->character_theme_id) { ?>
						<?=t('treasure.show.theme');?> "<?=CharacterTheme::find($rewards->character_theme_id)->description()->name;?>"<br />
					<?php } ?>
					<?php if ($rewards->character_id) { ?>
						<?=t('treasure.show.character');?> "<?=Character::find($rewards->character_id)->description()->name;?>"<br />
					<?php } ?>
					<?php if ($rewards->headline_id) { ?>
						<?=t('treasure.show.headline');?> "<?=Headline::find($rewards->headline_id)->description()->name;?>"<br />
					<?php } ?>
				<?php } else { ?>
					Nenhuma recompensa
				<?php } ?>
			</div>
			</div>
		</div>
	<?php } ?>
</div>
