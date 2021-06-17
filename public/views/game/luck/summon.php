<?php echo partial('shared/title', array('title' => 'menus.invocacao', 'place' => 'menus.invocacao')) ?>
<?php
	echo partial('shared/info', [
		'id'		=> 1,
		'title'		=> 'summon.title',
		'message'	=> t('summon.description')
	]);
?>
<div id="luck-container" style="height: 627px">
	<div id="luck-buy">
		<div class="luck-money">
			<div class="summon-button" data-currency="1">
				<span><?php echo highamount($summon_currency) .' '. t('currencies.' . $player->character()->anime_id) ?></span>
			</div>
		</div>
		<div class="luck-result">
			
		</div>
		<div class="luck-credit">
			<div class="summon-button" data-currency="2">
				<span><?php echo  highamount($summon_credits) .' '. t('currencies.credits') ?></span>
			</div>
		</div>	
	</div>
	<div class="type-reward" style="left: 285px;">
		<select name="type_reward" id="type_reward" class="form-control input-sm" style="width: auto">
			<option value="character">Personagem</option>
			<option value="character_theme">Tema de Personagem</option>
		</select>
	</div>
	<div id="luck-stripes">
		<div id="luck-stripe-1" class="luck-stripe2 luck-stripe2-1"></div>
		<div id="luck-stripe-2" class="luck-stripe2 luck-stripe2-2"></div>
		<div id="luck-stripe-3" class="luck-stripe2 luck-stripe2-3"></div>
	</div>
	<div id="luck-mask2"></div>
	<div id="summon-button"><span><?php echo t('luck.index.summon') ?></span></div>
</div>