<?php
if (!sizeof($player_items))
    echo '<div style="text-align: center;">' . t('characters.inventory.empty') . '</div>';
else {
    $i = 1;
    foreach ($types as $type) {
        $have	= FALSE;
        $items	= [];

        foreach ($player_items as $player_item) {
            $item           = $player_item->item();
            if ($item->item_type_id == 12 || $item->item_type_id == 13)
                $item_type  = 10;
            else
                $item_type  = $item->item_type_id;

            if ($item_type != $type->id)
                continue;

            $have   		= TRUE;
            $items[]    	= $item;
        }

        if (!$have)
            continue;
        ?>
        <h3 <?=($i == 2 ? 'style="margin-top: 10px;"' : '')?>><?=($type->id != 10 ? t('item_types.' . $type->id) : "Especiais");?></h3>
        <div class="clearfix"></div>
        <?php foreach ($items as $item) { ?>
            <div class="item" data-consumable="<?=(in_array($item->item_type_id, $consumables) ? 1 : 0);?>" data-id="<?=$item->id;?>" data-quantity="<?=$player_item->quantity;?>">
                <img src="<?=image_url($item->image(true));?>" class="inventory-item-popover" data-source="#inventory-item-content-<?=$item->id;?>" data-title="<?=$item->description()->name;?>" data-trigger="hover" data-placement="left" />
                <div class="inventory-item-container" id="inventory-item-content-<?=$item->id;?>">
                    <?=$item->tooltip();?>
                </div>
                <span class="quantity"><?=$item->player_item()->quantity;?></span>
            </div>
        <?php } ?>
        <div class="clearfix"></div>
        <?php
        ++$i;
    }
}
?>