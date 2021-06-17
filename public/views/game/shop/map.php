<?php echo partial('shared/title', array('title' => 'menus.shop_map', 'place' => 'menus.shop_map')) ?>
<?php
foreach($player_items as $player_item){
	switch($player_item->item_id){
		case 1721:
			$item_1721 = "<br /><br />Você tem <span class='laranja'>". $player_item->quantity . "x Pergaminho(s)</span>";
		break;
		case 1851:
			$item_1851 = "<br />Você tem <span class='laranja'>". $player_item->quantity . "x Material(ais)</span>";
		break;
	}
}
?>	
<?php
	echo partial('shared/info', [
		'id'		=> 1,
		'title'		=> 'map.store.title',
		'message'	=> t('map.store.description') . (isset($item_1721) ? $item_1721 : "<br /><br />Você tem <span class='laranja'>0x Pergaminho(s)</span>") . (isset($item_1851) ? $item_1851 : "<br />Você tem <span class='laranja'>0x Material(ais)</span>")
		
	]);
?>	
<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
	<table width="725" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="85">&nbsp;</td>
		<td align="center"><?php echo t('shop.header.name') ?></td>
		<td width="120" align="center"><?php echo t('shop.header.quantity') ?></td>
		<td width="120" align="center"><?php echo t('shop.header.price') ?></td>
		<td width="120" align="center"></td>
	</tr>
	</table>
</div>
<table width="725" id="shop-map-container">
	<?php $counter = 0; ?>
	<?php foreach ($items as $item): ?>
		<?php
			$map_store = MapStore::find_first("item_id=".$item->id." AND is_store=1");
			$color	= $counter++ % 2 ? '091e30' : '173148';
		?>
		<tr bgcolor="<?php echo $color ?>">
			<td width="85" align="center">
				<img src="<?php echo image_url($item->image(true)) ?>" class="shop-item-popover" data-source="#shop-item-content-<?php echo $item->id ?>" data-title="<?php echo $item->description()->name ?>" data-trigger="hover" data-placement="right" />
				<div class="shop-item-container" id="shop-item-content-<?php echo $item->id ?>">
					<?php echo $item->tooltip() ?>
				</div>
			</td>
			<td align="left">
				<b class="amarelo" style="font-size:14px; position: relative; top: 5px;"><?php echo $item->description()->name ?></b><hr />
				<span><?php echo $item->description()->description ?></span>
				<br /><br />
			</td>
			<td width="120" align="center">
				<b style="font-size:16px">x<?php echo highamount($map_store->quantity); ?></b>
			</td>
			<td width="120" align="center">
				<?php 
					switch($map_store->anime_id){
						case 1:
							$type = "Pergaminho";
						break;
						case 2:
							$type = "Alma";
						break;
						case 9:
							$type = "Material";
						break;
					}
				?>
				<b style="font-size:16px"><?php echo "x". highamount($map_store->map_item_total); ?></b>
				<img style="position: relative; top: -3px;" src="<?php echo image_url('maps/'.$map_store->anime_id.'.png') ?>" width="24"/><br />
				<?php echo $type?>
			</td>
			<td width="120" align="center">
				<a class="btn btn-sm btn-primary buy" data-item="<?php echo $map_store->item_id ?>"><?php echo t('shop.buy') ?></a>
			</td>
		</tr>
	<tr height="4"></tr>
	<?php endforeach ?>
</table>