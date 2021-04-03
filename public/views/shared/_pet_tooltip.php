<?php ob_start() ?>
	<?php echo partial('shared/effect_tooltip', ['effects' => $effects, 'item' => $item, 'player' => $player, 'fixed_effect' => true]) ?>
<?php $tooltip_data	= ob_get_clean() ?>
<?php if ($battle_tooltip): ?>
	<div class="technique-data" style="width: 280px; margin: 0;">
		<?php echo $tooltip_data ?>
	</div>
<?php else: ?>
	<?php echo $tooltip_data ?>
<?php endif ?>