<?php
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
			//$class	= $item->formula()->damage ? "attack" : "defense";
		}

		if ($has_technique && $type == 'source') {
			$class	= '';
		}
	}

	if ($type == 'drop') {
		$class	.= ' dropzone';
	}

	$locked	= $item ? $item->locked && !$player->has_unlocked_item($item->id): false;
?>
<div class="item <?php echo $class ?> tutorial-<?php echo isset($slot) ? $slot : ""?>" <?php echo $type == 'source' ? 'id="technique-dropsource-' . $item->id . '"' : 'data-slot="' . $slot . '"' ?>>
	<?php if (($type == 'drop' && $item) || ($type == 'source' && !$has_technique)): ?>
		<div class="item-content <?php echo $locked ? 'locked' : '' ?>" data-item="<?php echo $item->parent_id ? $item->parent_id : $item->id   ?>">
			<?php if ($locked): ?>
				<span class="lock glyphicon glyphicon-lock"></span>
			<?php endif ?>
			<img src="<?php echo image_url($item->image(true)) ?>" class="technique-popover item-image" data-source="#technique-content-<?php echo $item->id ?>" data-title="<?php echo $item->description()->name ?>" data-trigger="hover" data-placement="bottom" />
			<div class="technique-container" id="technique-content-<?php echo $item->id ?>">
				<?php echo $item->technique_tooltip() ?>
			</div>
		</div>
	<?php endif ?>
</div>
