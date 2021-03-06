<?php echo partial('shared/title', array('title' => 'shop.food.title', 'place' => 'shop.food.title')) ?>
<?php echo partial('shop/list', array('player' => $player, 'items' => $items, 'discount' => $discount)) ?>
