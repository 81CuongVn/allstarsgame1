<?php echo partial('shared/title', array('title' => 'shop.weapons.title', 'place' => 'shop.weapons.title')) ?>
<?php echo partial('shop/list', array('player' => $player, 'items' => $items, 'discount' => $discount)) ?>
