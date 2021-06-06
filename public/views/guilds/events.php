<?php echo partial('shared/title', array('title' => 'battles.npc.title', 'place' => 'battles.npc.title')) ?>
<?php
	echo partial('shared/info', array(
		'id'		=> 1,
		'title'		=> 'ranked.liga',
		'message'	=> t('battles.ranked.description2')
	));
?>
<div id="guild-event-list">
	<?php foreach ($events as $event): ?>
		<?php $unlocked = $event->unlocked($player->guild_id, $event->id, $player->id) ?>
		<div class="group">
			<div class="technique-popover buff" data-source="#challenges-container-<?php echo $event->id ?>" data-title="<?php echo $event->description()->name ?>" data-trigger="click" data-placement="bottom">
			<div class="<?php echo $unlocked ? '' : 'efeito'?>">
				<?php echo $event->image() ?>
			</div>
			<div class="clearfix"></div>
			<div class="name-anime"><?php echo $event->description()->name ?></div>
			<div class="clearfix"></div>
            <div class="buttons">
				<?php if ($unlocked): ?>
					<a class="btn btn-success invite" data-event="<?php echo $event->id ?>">Começar Dungeon</a>
				<?php else: ?>
					<?php if($event->currency):?>
						<a class="btn btn-primary unlock" data-event="<?php echo $event->id ?>" data-mode="1"><?php echo t('history_mode.index.unlock_currency', ['amount' => $event->currency, 'currency' => t('currencies.' . $player->character()->anime_id)]) ?></a>
					<?php endif ?>
					<?php if($event->credits):?>
						<a class="btn btn-warning unlock" data-event="<?php echo $event->id ?>" data-mode="2"><?php echo t('history_mode.index.unlock_credits', ['amount' => $event->credits]) ?></a>
					<?php endif ?>
				<?php endif ?>
            </div>
			<div id="challenges-container-<?php echo $event->id ?>" class="technique-container">
				<?php echo "Descrição aqui!" ?>
			</div>
			</div>
		</div>
	<?php endforeach ?>
</div>
