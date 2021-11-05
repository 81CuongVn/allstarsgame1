<?php
	$heads	= [];
	$values	= [];
?>
<div class="technique-data fix-lines" style="width: 330px;">
	<div class="type <?php echo $type_class ?>">
		<?php echo $type ?> -
		<span class="<?php echo $unique_class ?>"><?php echo $unique ?></span><span class="verde"><?php echo $item->parent_id ? " -  Encantado" : ""?></span>
	</div>
	<?php if ($battle_tooltip && $formula->is_player_item && $item->item_type_id == 7): ?>
		<div class="quantity">x<?php echo $player_item->quantity ?></div>
	<?php endif ?>
	<div class="clearfix"></div>
	<?php if (!$battle_tooltip): ?>
		<!-- <div class="description"><?php echo $description->description ?></div> -->
	<?php endif ?>
	<div class="clearfix"></div>
	<hr />
	<table border="0" width="100%">
		<tr><td colspan="10" class="text-only"><?php echo t('techniques.combat_values') ?><br /><br /></td></tr>
		<?php if (!$item->is_buff): ?>
			<?php if ($item->is_defensive): ?>
				<?php ob_start() ?>
					<img src="<?php echo image_url('icons/for_def.png') ?>" />
				<?php $heads[]	= ob_get_clean() ?>
				<?php ob_start() ?>
					<?php if ($battle_tooltip): ?>
						<span style="color: <?php echo $formula->color_types->defense ?>"><?=floor($formula->defense + $player->for_def());?></span>
					<?php else: ?>
						<?php echo $formula->defense ?>
					<?php endif ?>
				<?php $values[]	= ob_get_clean() ?>
			<?php else: ?>
				<?php ob_start() ?>
					<img src="<?php echo image_url('icons/for_atk.png') ?>" />
				<?php $heads[]	= ob_get_clean() ?>
				<?php ob_start() ?>
					<?php if ($battle_tooltip): ?>
						<span style="color: <?php echo $formula->color_types->damage ?>"><?=floor($formula->damage + $player->for_atk());?></span>
					<?php else: ?>
						<?php echo $formula->damage ?>
					<?php endif ?>
				<?php $values[]	= ob_get_clean() ?>
			<?php endif ?>
		<?php endif; ?>
		<?php if ($formula->consume_mana || (!$formula->consume_mana && $formula->color_types->consume_mana)): ?>
			<?php ob_start() ?>
				<img src="<?php echo image_url('icons/for_mana.png') ?>" />
			<?php $heads[]	= ob_get_clean() ?>
			<?php ob_start() ?>
				<span style="color: <?php echo $formula->color_types->consume_mana ?>"><?php echo $formula->consume_mana ?></span>
			<?php $values[]	= ob_get_clean() ?>
		<?php endif ?>
		<?php if ($formula->cooldown): ?>
			<?php ob_start() ?>
				<img src="<?php echo image_url('icons/cooldown.png') ?>" />
			<?php $heads[]	= ob_get_clean() ?>
			<?php ob_start() ?>
				<?php echo $formula->cooldown ?>
			<?php $values[]	= ob_get_clean() ?>
		<?php endif ?>

		<?php if ($item->is_buff): ?>
			<?php if ($formula->duration): ?>
				<?php ob_start() ?>
					<img src="<?php echo image_url('icons/duration.png') ?>" />
				<?php $heads[]	= ob_get_clean() ?>
				<?php ob_start() ?>
					<?php echo $formula->duration ?>
				<?php $values[]	= ob_get_clean() ?>
			<?php endif ?>
		<?php endif ?>

		<?php if ($item->item_type_id == 1): ?>
			<?php ob_start() ?>
				<img src="<?php echo image_url('icons/for_acer.png') ?>" />
			<?php $heads[]	= ob_get_clean() ?>
			<?php ob_start() ?>
				<span style="color: <?php echo $formula->color_types->hit_chance ?>"><?php echo $formula->hit_chance ?></span>
			<?php $values[]	= ob_get_clean() ?>
		<?php endif ?>

		<?php if (!$item->is_buff): ?>
			<?php ob_start() ?>
				<img src="<?php echo image_url('icons/for_velatk.png') ?>" />
			<?php $heads[]	= ob_get_clean() ?>
			<?php ob_start() ?>
				<span style="color: <?php echo $formula->color_types->attack_speed ?>"><?php echo $formula->attack_speed ?></span>
			<?php $values[]	= ob_get_clean() ?>
		<?php endif; ?>
		<tr>
			<?php foreach ($heads as $key => $value): ?>
				<td align="center">
					<?php echo $value; ?>
				</td>
			<?php endforeach ?>
		</tr>
		<tr>
			<?php foreach ($heads as $key => $value): ?>
				<td align="center" class="attribute-value">
					<?php echo $values[$key] ?>
				</td>
			<?php endforeach ?>
		</tr>
	</table>
	<?php if ($item->item_effect_ids): ?>
		<hr />
		<?php echo partial('shared/effect_tooltip', ['effects' => $item->effects(), 'item' => $item, 'player' => $player]) ?>
	<?php endif ?>
	<div class="popover-type-container">
		<div class="type">
			<?php $type	= $item->attack_type() ?>
			<?php if ($type): ?>
				<div class="weak">
					<?php foreach ($item->get_weakness() as $weakness): ?>
						<div style="color: #ff3333 !important"><?php echo $weakness->description()->description ?> <span class="fa fa-chevron-down fa-fw"></span></div>
					<?php endforeach ?>
				</div>
				<div class="strong">
					<?php foreach ($item->get_strenght() as $strenght): ?>
						<div style="color: #00b008 !important"><span class="fa fa-chevron-up fa-fw"></span> <?php echo $strenght->description()->description ?></div>
					<?php endforeach ?>
				</div>
				<?php echo $type->description()->description ?>
			<?php else: ?>
				Neutro
			<?php endif ?>
		</div>
	</div>
</div>
