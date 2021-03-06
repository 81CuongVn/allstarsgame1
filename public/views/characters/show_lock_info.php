<?php
	if ($character->reward_lock) {
		$reward_lock	= HistoryModeSubgroup::find_first('reward_character_id=' . $character->id);

		if ($reward_lock) {
			$reward_lock	= $reward_lock->description()->name;
		} else {
			$reward_lock	= '[desconhecido]';
		}
	} else {
		$reward_lock	= '';
	}
?>
<div class="content-center">
	<?php echo $character->small_image() ?>
	<div class="clearfix"></div>
	<?php if ($reward_lock): ?>
		<?php if(!$character->treasure_lock && !$character->special_lock && !$character->map_lock  ){?>
			<?php echo t('characters.create.unlocks_reward', ['name' => $reward_lock]) ?>
		<?php }else if($character->treasure_lock && !$character->special_lock && !$character->map_lock){?>
			<?php echo t('characters.create.unlocks_reward2')?>
		<?php }else if(!$character->treasure_lock && !$character->special_lock && $character->map_lock){?>
			<?php echo t('characters.create.unlocks_reward4')?>	
		<?php }else if(!$character->treasure_lock && $character->special_lock && !$character->map_lock){?>
			<?php echo t('characters.create.unlocks_reward3')?>	
		<?php }?>		
	<?php endif ?>
	<br /><br />
	<?php if ($character->currency_lock): ?>
		<?php if ($player): ?>
			<a class="btn btn-primary unlock" data-method="1"><?php echo t('characters.create.unlocks_currency', ['amount' => $character->currency_lock, 'currency' => t('currencies.' . $player->character()->anime_id)]) ?></a>
		<?php else: ?>
			<p><?php echo t('characters.create.needs_unlock_player') ?></p>
		<?php endif ?>
	<?php endif ?>
	<br /><br />
	<?php if ($character->credits_lock): ?>
		<a class="btn btn-primary unlock" data-method="2"><?php echo t('characters.create.unlocks_credits', ['amount' => $character->credits_lock]) ?></a>
	<?php endif ?>
</div>