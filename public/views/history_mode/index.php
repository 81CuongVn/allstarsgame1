<?php echo partial('shared/title', array('title' => 'history_mode.index.title', 'place' => 'history_mode.index.title')) ?>
<?php
	echo partial('shared/info', [
		'id'		=> 1,
		'title'		=> 'history_mode.title',
		'message'	=> t('history_mode.description')
	]);
?>
<br />
<div id="history-mode-group-list">
	<?php foreach ($groups as $group): ?>
		<div class="group">
			<div class="technique-popover buff" data-source="#history-container-<?php echo $group->id ?>" data-title="<?php echo $group->description()->name ?>" data-trigger="click" data-placement="bottom">
				<?php $group->set_player($player) ?>
					
				<div class="<?php echo $group->unlocked() ? '' : 'efeito'?>">
					<?php echo $group->image() ?>
				</div>	
				<div class="clearfix"></div>
				<div class="name-anime"><?php echo $group->description()->name ?></div>
				<div class="clearfix"></div>	
				<div class="buttons">
				<?php if ($group->unlocked()): ?>
					<a class="btn btn-primary" href="<?php echo make_url('history_mode#show/' . $group->id) ?>"><?php echo t('history_mode.index.go_battles') ?></a>
				<?php else: ?>
					<a class="btn btn-primary unlock" data-group="<?php echo $group->id ?>" data-mode="1"><?php echo t('history_mode.index.unlock_currency', ['amount' => highamount($group->currency_cost), 'currency' => t('currencies.' . $player->character()->anime_id)]) ?></a>
					<a class="btn btn-warning unlock" data-group="<?php echo $group->id ?>" data-mode="2"><?php echo t('history_mode.index.unlock_credits', ['amount' => highamount($group->credits_cost)]) ?></a>
				<?php endif ?>
				</div>
				<div id="history-container-<?php echo $group->id ?>" class="technique-container">
					<div class="status-popover-content"><?php echo $group->description()->description ?></div>
				</div>
			</div>
		</div>
	<?php endforeach ?>
</div>