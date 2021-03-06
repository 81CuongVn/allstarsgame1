<?php
	foreach($items as $item){
		$heads	= [];
		$values	= [];
		$item->set_player($player);
		$item->formula(true);
		
		$class			= "";
		$has_technique	= $item && $player->has_technique($item);
	
		if ($item) {
			if ($item->is_buff) {
				$class	= "buff";
			} else {
				if($item->formula()->damage && $item->formula()->generic){
					$class = "attack";	
				}else if($item->formula()->damage && !$item->formula()->generic){
					$class = "unique";
				}else{
					$class = "defense";	
				}
			}
		}
	
		$locked	= $item->locked && !$player->has_unlocked_item($item->id);
?>
	<div class="ability-speciality-box enchant-box" data-item="<?php echo $item->id?>" style="width: 237px !important; height: 275px !important">
		<div>
			<div class="image">
				<img src="<?php echo image_url($item->image(true)) ?>" />
				<?php if ($locked): ?>
					<span class="lock glyphicon glyphicon-lock"></span>
				<?php endif ?>
			</div>
			<div class="name type <?php echo $class?>" style="height: 15px !important;">
				<?php echo $item->description()->name ?>
			</div>
			<div class="description" style="height: 75px !important;">
			<?php echo $item->description()->description ?><br />
			</div>
			<div class="details">
				<table border="0" width="100%">
					<tr><td colspan="10" class="text-only"><?php echo t('techniques.combat_values') ?><br /><br /></td></tr>
					<?php if (!$item->is_buff): ?>
						<?php if ($item->is_defensive): ?>
							<?php ob_start() ?>
								<img src="<?php echo image_url('icons/for_def.png') ?>" />
							<?php $heads[]	= ob_get_clean() ?>
							<?php ob_start() ?>
								<?php echo $item->formula()->defense ?>
							<?php $values[]	= ob_get_clean() ?>
						<?php else: ?>
							<?php ob_start() ?>
								<img src="<?php echo image_url('icons/for_atk.png') ?>" />
							<?php $heads[]	= ob_get_clean() ?>
							<?php ob_start() ?>
								<?php echo $item->formula()->damage ?>
							<?php $values[]	= ob_get_clean() ?>
						<?php endif ?>
					<?php endif; ?>
					<?php if ($item->formula()->consume_mana): ?>
						<?php ob_start() ?>
							<img src="<?php echo image_url('icons/for_mana.png') ?>" />
						<?php $heads[]	= ob_get_clean() ?>
						<?php ob_start() ?>
							<span><?php echo $item->formula()->consume_mana ?></span>
						<?php $values[]	= ob_get_clean() ?>
					<?php endif ?>
					<?php if ($item->formula()->cooldown): ?>
						<?php ob_start() ?>
							<img src="<?php echo image_url('icons/esp.png') ?>" />
						<?php $heads[]	= ob_get_clean() ?>
						<?php ob_start() ?>
							<?php echo $item->formula()->cooldown ?>
						<?php $values[]	= ob_get_clean() ?>
					<?php endif ?>
			
					<?php if ($item->is_buff): ?>
						<?php if ($item->formula()->duration): ?>
							<?php ob_start() ?>
								<img src="<?php echo image_url('icons/dur.png') ?>" />
							<?php $heads[]	= ob_get_clean() ?>
							<?php ob_start() ?>
								<?php echo $item->formula()->duration ?>
							<?php $values[]	= ob_get_clean() ?>
						<?php endif ?>
					<?php endif ?>
			
					<?php if ($item->item_type_id == 1): ?>
						<?php ob_start() ?>
							<img src="<?php echo image_url('icons/for_acer.png') ?>" />
						<?php $heads[]	= ob_get_clean() ?>
						<?php ob_start() ?>
							<span><?php echo $item->formula()->hit_chance ?></span>
						<?php $values[]	= ob_get_clean() ?>
					<?php endif ?>
			
					<?php if (!$item->is_buff): ?>
						<?php ob_start() ?>
							<img src="<?php echo image_url('icons/for_velatk.png') ?>" />
						<?php $heads[]	= ob_get_clean() ?>
						<?php ob_start() ?>
							<span><?php echo $item->formula()->attack_speed ?></span>
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
			</div>
		</div>
	</div>
<?php }?>	
