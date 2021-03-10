<?php foreach ($themes as $theme): ?>
	<a style="cursor: pointer;" class="character-uniques" data-id="<?php echo $theme->id?>" data-theme-code="<?php echo $theme->theme_code?>" data-character-id="<?php echo $theme->character_id?>" style="float: left;"><?php echo $theme->small_image2() ?></a>
<?php endforeach ?>
<br /><br />
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
<?php
	if(!$character->unlocked($user)){
?>		
	<?php if ($reward_lock): ?>
		<?php if(!$character->treasure_lock && !$character->special_lock && !$character->map_lock  ){?>
			<b class="laranja" style="font-size:14px"><?php echo t('characters.create.unlocks_reward', ['name' => $reward_lock]) ?></b>
		<?php }else if($character->treasure_lock && !$character->special_lock && !$character->map_lock){?>
			<b class="laranja" style="font-size:14px"><?php echo t('characters.create.unlocks_reward2')?></b>
		<?php }else if(!$character->treasure_lock && !$character->special_lock && $character->map_lock){?>
			<b class="laranja" style="font-size:14px"><?php echo t('characters.create.unlocks_reward4')?></b>
		<?php }else if(!$character->treasure_lock && $character->special_lock && !$character->map_lock){?>
			<b class="laranja" style="font-size:14px"><?php echo t('characters.create.unlocks_reward3')?></b>	
		<?php }?>		
	<?php endif ?>
	<?php if ($character->currency_lock): ?>
		<?php if ($player): ?>
			<a class="btn btn-primary unlock" data-method="1" data-id="<?php echo $character->id?>"><?php echo t('characters.create.unlocks_currency', ['amount' => highamount($character->currency_lock), 'currency' => t('currencies.' . $player->character()->anime_id)]) ?></a>
		<?php else: ?>
			<b class="laranja" style="font-size:14px"><?php echo t('characters.create.needs_unlock_player') ?></b>
		<?php endif ?>
	<?php endif ?>
	<br /><br />
	<?php if ($character->credits_lock): ?>
		<a class="btn btn-primary unlock" data-method="2" data-id="<?php echo $character->id?>"><?php echo t('characters.create.unlocks_credits', ['amount' => highamount($character->credits_lock)]) ?></a>
	<?php endif ?>	
<?php
	}
?>
		