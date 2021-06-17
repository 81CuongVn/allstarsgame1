<div class="technique-data" style="width: 280px; margin: 0;">
	<div class="type"><?php echo t('consumables.type') ?></div>
	<div class="clearfix"></div>
	<?php if ($item->for_life || $item->for_mana || $item->for_stamina) { ?>
		<!-- <hr /> -->
		<table width="100%" style="margin-top: 8px;">
			<?php if ($item->for_life) { ?>
				<tr>
					<td>
						<img src="<?php echo image_url('icons/for_life.png') ?>" />
						<?php echo t('consumables.for_life') ?>
					</td>
					<td><?php echo $item->for_life ?></td>
				</tr>
			<?php } ?>

			<?php if ($item->for_mana) { ?>
				<tr>
					<td>
						<img src="<?php echo image_url('icons/for_mana.png') ?>" />
						<?php echo t('consumables.for_mana', array('name' => t('formula.for_mana.' . $item->anime()->id))) ?>
					</td>
					<td><?php echo $item->for_mana ?></td>
				</tr>
			<?php } ?>

			<?php if ($item->for_stamina) { ?>
				<tr>
					<td>
						<img src="<?php echo image_url('icons/for_stamina.png') ?>" />
						<?php echo t('consumables.for_stamina') ?>
					</td>
					<td><?php echo $item->for_stamina ?></td>
				</tr>
			<?php } ?>
		</table>
	<?php } else { ?>
		<div class="description"><?php echo $description->description ?></div>
	<?php } ?>
</div>