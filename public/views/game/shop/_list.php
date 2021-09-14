<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
	<table width="725" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="85">&nbsp;</td>
		<td align="center"><?php echo t('shop.header.name') ?></td>
		<td width="85" align="center"><?php echo t('shop.header.requirements') ?></td>
		<td width="85" align="center"><?php echo t('shop.header.inventory') ?></td>
		<td width="85" align="center"><?php echo t('shop.header.quantity') ?></td>
		<td width="85" align="center"><?php echo t('shop.header.price') ?></td>
		<td width="100" align="center"></td>
	</tr>
	</table>
</div>
<table width="725" id="shop-items-container">
	<?php $counter = 0; ?>
	<?php foreach ($items as $item): ?>
		<?php
			$color	= $counter++ % 2 ? '091e30' : '173148';
			extract($item->has_requirement($player));
		?>
		<tr bgcolor="<?php echo $color ?>">
			<td width="85" align="center">
				<img src="<?php echo image_url($item->image(true)) ?>" class="shop-item-popover" data-source="#shop-item-content-<?php echo $item->id ?>" data-title="<?php echo $item->description()->name ?>" data-trigger="hover" data-placement="right" />
				<div class="shop-item-container" id="shop-item-content-<?php echo $item->id ?>">
					<div style="min-width: 200px;">
						<?php echo $item->tooltip() ?>
					</div>
				</div>
			</td>
			<td align="left">
				<b class="amarelo" style="font-size:14px; position: relative; top: 5px;"><?php echo $item->description()->name ?></b><hr />
				<span><?php echo $item->description()->description ?></span>
				<br /><br />
			</td>
			<td width="85" align="center">
				<img src="<?php echo image_url('requer.png') ?>" class="requirement-popover" data-source="#requirement-content-<?php echo $item->id ?>" data-title="<?php echo t('popovers.titles.requirements') ?>" data-trigger="hover" data-placement="left" />
				<div class="requirement-container" id="requirement-content-<?php echo $item->id ?>"><?php echo $requirement_log ?></div>
			</td>
			<td width="85" align="center" id="shop-item-quantity-<?php echo $item->id ?>">
				<?php if ($player->has_consumable($item)): ?>
				 	x<?php echo highamount($player->get_item($item)->quantity) ?>
				 <?php else: ?>
				 	<?php echo t('shop.none') ?>
				 <?php endif ?>
			</td>
			<td width="85" align="center">
				<select style="width: 70px" id="shop-item-quantity-select-<?php echo $item->id ?>" class="quantity form-control input-sm" data-item="<?php echo $item->id ?>" data-price-currency="<?php echo $item->price_currency - percent($discount, $item->price_currency) ?>" data-price-credits="<?php echo $item->price_credits ?>">
					<?php for($f = 1; $f <= 20; $f++): ?>
						<option value="<?php echo $f ?>"><?php echo $f ?></option>
					<?php endfor; ?>
				</select>
			</td>
			<td width="85" align="center">
				<?php if ($item->price_currency): ?>
					<input type="radio" name="method_<?php echo $item->id ?>" value="1" />
					<span id="shop-item-currency-value-<?php echo $item->id ?>" data-currency="<?php echo t('currencies.' . $player->character()->anime_id) ?>">
						<?php echo t('currencies.' . $player->character()->anime_id) ?>
						<?php echo highamount($item->price_currency - percent($discount, $item->price_currency)) ?>
					</span>
				<?php endif ?>
				<?php if ($item->price_credits): ?>
					<input type="radio" name="method_<?php echo $item->id ?>" value="2" />
					<span id="shop-item-credits-value-<?php echo $item->id ?>" data-currency="<?php echo t('currencies.credits') ?>">
						<?php echo t('currencies.credits') ?>
						<?php echo highamount($item->price_credits) ?>
					</span>
				<?php endif ?>
			</td>
			<td width="100" align="center">
				<?php if (!$has_requirement): ?>
					<a class="btn btn-sm btn-primary disabled"><?php echo t('shop.buy') ?></a>
				<?php else: ?>
					<a class="btn btn-sm btn-primary buy" data-item="<?php echo $item->id ?>"><?php echo t('shop.buy') ?></a>
				<?php endif; ?>
			</td>
		</tr>
	<tr height="4"></tr>
	<?php endforeach ?>
</table>
