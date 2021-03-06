<?php if ($is_valid): ?>
	<?php if (!sizeof($equipments)): ?>
		<span class="branco"><?php echo t('equipments.show.none') ?></span>
	<?php else: ?>
		<div id="equipment-list">
			<?php foreach ($equipments as $equipment): 
				  $destroy = 0;	
			?>
				<?php
					$item		= $equipment->item();
					$attributes	= $equipment->attributes();
										
					switch($equipment->rarity){
						case "common":
							$destroy = 10;
						break;
						case "rare":
							$destroy = 20;
						break;
						case "legendary":
							$destroy = 40;
						break;	
					
					}
						
				?>
				<div class="equipment">
					<?php if ($attributes->is_new): ?>
						<div class="badge" style="margin: 0; margin-top: -13px; width: 48px;"><?php echo t('global.new') ?></div>
						<?php
							$attributes->is_new	= 0;
							$attributes->save();
						?>
					<?php endif ?>
					<img width="48" src="<?php echo image_url($item->image(true)) ?>" class="equipment-popover" data-placement="bottom" data-destroy="<?php echo $destroy?>" data-price="<?php echo $item->equipment_sell_price() ?>" data-slot="<?php echo $equipment->slot_name ?>" data-id="<?php echo $equipment->id ?>" data-embed="<?php echo $item->embed() ?>" />
					<?php echo $item->tooltip() ?>
				</div>
			<?php endforeach ?>
		</div>
	<?php endif ?>
<?php else: ?>
	<?php echo t('equipments.show.invalid') ?>
<?php endif ?>
<div class="break"></div>