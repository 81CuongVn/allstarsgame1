<?php echo partial('shared/title', array('title' => 'challenges.title', 'place' => 'challenges.title')) ?>
<div class="msg-container">
	<div class="msg_top"></div>
	 <div class="msg_repete">
		<div class="msg" style="background:url(<?php echo image_url('msg/arena.png')?>); background-repeat: no-repeat;">
		</div>
		<div class="msgb" style="position:relative; margin-left: 231px; text-align: left; top: -37px">
			<b><?php echo t('challenges.title2') ?></b>
			<div class="content"><?php echo t('challenges.descriptions') ?></div>
		</div>
	</div>
	<div class="msg_bot"></div>	
	<div class="msg_bot2"></div>
</div>
<br />
<div id="challenge-list">
	<?php foreach ($challenges as $challenge): ?>
		<div class="group">
			<div class="technique-popover buff" data-source="#challenges-container-<?php echo $challenge->id ?>" data-title="<?php echo $challenge->description()->name ?>" data-trigger="click" data-placement="bottom">
			<?php $challenge->set_player($player) ?>
				
			<div class="<?php echo $challenge->unlocked() ? '' : 'efeito'?>">
				<?php echo $challenge->image() ?>
			</div>	
			<div class="clearfix"></div>
			<div class="name-anime"><?php echo $challenge->description()->name ?></div>
			<div class="clearfix"></div>	
            <div class="buttons">
			<?php if ($challenge->unlocked()): ?>
				<a class="btn btn-primary" href="<?php echo make_url('challenges#show/' . $challenge->id) ?>"><?php echo t('history_mode.index.go_battles') ?></a>
			<?php else: ?>
				<?php if(!$player->challenge_id):?>
					<?php if(sizeof($challenge->limit_by_day()) <= 1):?>
						<?php if($challenge->currency_cost):?>
							<a class="btn btn-primary unlock" data-challenge="<?php echo $challenge->id ?>" data-mode="1"><?php echo t('history_mode.index.unlock_currency', ['amount' => $challenge->currency_cost, 'currency' => t('currencies.' . $player->character()->anime_id)]) ?></a>
						<?php endif ?>
						<?php if($challenge->credits_cost):?>
							<a class="btn btn-warning unlock" data-challenge="<?php echo $challenge->id ?>" data-mode="2"><?php echo t('history_mode.index.unlock_credits', ['amount' => $challenge->credits_cost]) ?></a>
						<?php endif ?>
					<?php else: ?>
							<a class="btn btn-danger"><?php echo t('friends.f26')?></a>
					<?php endif ?>			
				<?php else: ?>
					<a class="btn btn-danger"><?php echo t('challenges.challenge_active_error') ?></a>
				<?php endif ?>	
			<?php endif ?>
            </div>
			<div id="challenges-container-<?php echo $challenge->id ?>" class="technique-container">
				<div class="status-popover-content"><?php echo $challenge->description()->description ?></div>
			</div>
			</div>
		</div>
	<?php endforeach ?>
</div>
