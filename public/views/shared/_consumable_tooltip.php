<div class="technique-data">
	<div class="type"><?php echo t('consumables.type') ?></div>
	<div class="clearfix"></div>
	<div class="description"><?php echo $description->description ?></div>
	<hr />
	<table border="0" width="100%">
		<?php if ($item->for_life): ?>
			<tr>
				<td class="col-lg-7">
					<img src="<?php echo image_url('icons/for_life.png') ?>" />
					<?php echo t('consumables.for_life') ?>
				</td>
				<td class="col-lg-5"><?php echo $item->for_life ?></td>
			</tr>
		<?php endif ?>

		<?php if ($item->for_mana): ?>
			<tr>
				<td class="col-lg-7">
					<img src="<?php echo image_url('icons/for_mana.png') ?>" />
					<?php echo t('consumables.for_mana', array('name' => t('formula.for_mana.' . $item->anime()->id))) ?>
				</td>
				<td class="col-lg-5"><?php echo $item->for_mana ?></td>
			</tr>
		<?php endif ?>

		<?php if ($item->for_stamina): ?>
			<tr>
				<td class="col-lg-7">
					<img src="<?php echo image_url('icons/for_stamina.png') ?>" />
					<?php echo t('consumables.for_stamina') ?>
				</td>
				<td class="col-lg-5"><?php echo $item->for_stamina ?></td>
			</tr>
		<?php endif ?>
	</table>
</div>