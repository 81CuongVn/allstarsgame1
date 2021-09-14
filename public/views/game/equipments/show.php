<?php if ($is_valid) { ?>
	<?php if (!sizeof($equipments)) { ?>
		<span class="branco"><?=t('equipments.show.none');?></span>
	<?php } else { ?>
		<div id="equipment-list">
			<?php
			foreach ($equipments as $equipment) {
				$item		= $equipment->item();
				$attributes	= $equipment->attributes();

				$destroy	= 0;
				switch ($equipment->rarity) {
					case "common":		$destroy = 20;	break;
					case "rare":		$destroy = 40;	break;
					case "epic":		$destroy = 80;	break;
					case "legendary":	$destroy = 160;	break;
				}
			?>
				<div class="equipment">
					<?php if ($attributes->is_new) { ?>
						<div class="badge pulsate_icons">
							<i class="fa fa-exclamation fa-fw"></i>
						</div>
						<?php
						$attributes->is_new	= 0;
						$attributes->save();
						?>
					<?php } ?>
					<img width="48" src="<?=image_url($item->image(true));?>" class="equipment-popover" data-placement="bottom" data-destroy="<?=$destroy;?>" data-price="<?=$item->equipment_sell_price();?>" data-slot="<?=$equipment->slot_name;?>" data-id="<?=$equipment->id;?>" data-embed="<?=$item->embed();?>" />
					<?=$item->tooltip();?>
				</div>
			<?php } ?>
		</div>
	<?php } ?>
<?php } else { ?>
	<?=t('equipments.show.invalid');?>
<?php } ?>
<div class="break"></div>
