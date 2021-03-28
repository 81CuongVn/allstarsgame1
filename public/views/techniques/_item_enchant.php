<?php
	$class			= "";
	$has_technique	= $item && $player->has_technique($item);

	if ($item) {
        if ($item->is_buff) {
            $class	= "buff";
        } else {
            if ($item->formula()->damage && $item->formula()->generic) {
                $class = "attack";	
            } elseif ($item->formula()->damage && !$item->formula()->generic) {
                $class = "unique";
            } else {
                $class = "defense";	
            }
        }
    }

	$locked	= $item->locked && !$player->has_unlocked_item($item->id);
?>
<div class="item enchant-box <?php echo $class ?> " data-item="<?php echo $item->parent_id ? $item->parent_id : $item->id   ?>">
    <div class="item-content change_golpe_enchant <?php echo $locked ? 'locked' : '' ?>" data-item="<?php echo $item->parent_id ? $item->parent_id : $item->id   ?>">
        <?php if ($locked): ?>
            <span class="lock glyphicon glyphicon-lock"></span>
        <?php endif ?>
        <img src="<?php echo image_url($item->image(true)) ?>" class="technique-popover item-image <?=($enchant && $equipped->id != $item->id ? 'opacity' : '')?>" data-source="#technique-content-<?php echo $item->id ?>" data-title="<?php echo $item->description()->name ?>" data-trigger="hover" data-placement="bottom" />
        <div class="technique-container" id="technique-content-<?php echo $item->id ?>">
            <?php echo $item->technique_tooltip() ?>
        </div>
    </div>
</div>
