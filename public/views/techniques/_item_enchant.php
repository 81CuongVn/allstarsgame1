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
<div class="item enchant-box <?=$class;?>" data-item="<?=($item->parent_id ? $item->parent_id : $item->id);?>">
    <div class="item-content change_golpe_enchant <?=($locked ? 'locked' : '');?>" data-item="<?=($item->parent_id ? $item->parent_id : $item->id);?>">
        <?php if ($locked) { ?>
            <span class="lock glyphicon glyphicon-lock"></span>
        <?php } ?>
        <img src="<?=image_url($item->image(true));?>" class="technique-popover item-image <?=(!$equipped || $equipped->id != $item->id ? 'opacity' : '')?>" data-source="#technique-content-<?=$item->id;?>" data-title="<?=$item->description()->name;?>" data-trigger="hover" data-placement="bottom" />
        <div class="technique-container" id="technique-content-<?=$item->id;?>">
            <?=$item->technique_tooltip();?>
        </div>
    </div>
</div>
