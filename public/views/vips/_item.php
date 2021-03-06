<?php
	if($player->has_item(1715)){
		$item1715 = PlayerItem::find_first("player_id=".$player->id." AND item_id=1715");
	}
?>
<form id="vip-form-<?php echo $item->id ?>" onsubmit="return false">
	<input type="hidden" name="id" value="<?php echo $item->id ?>" />
	<table width="725" border="0" cellpadding="0" cellspacing="0">
		<tr bgcolor="<?php echo $color ?>" height="60">
			<td width="80" align="center">
				<img src="<?php echo image_url($item->image(true)) ?>"  data-title="<?php echo $item->description()->name ?>" data-trigger="hover" data-placement="bottom" />
			</td>
			<td width="290" align="justify">
				<span class="amarelo" style="font-size:14px"><?php echo $item->description()->name ?></span><br /><?php echo $item->description()->description ?>
			</td>
			<td width="215" align="center">
				<span style="margin: 2px 0; display: block;">
					<?php
						if($item->id != 1709 && $item->id != 1715 && $item->id != 1718 && $item->id != 1746 && $item->id != 2112){
							switch ($item->get_buy_mode_for($player->id)) {
								case 0:
									echo "<span class='verde'>". t("vips.buy_modes.free"). "</span>";
			
									break;
								
								case 1:
									echo "<span class='laranja'>". t("vips.buy_modes.currency", ["price" => $item->price_currency, "currency" => t('currencies.' . $player->character()->anime_id)]) . "</span>";
									
									break;
			
								default:
									echo "<span class='laranja'>". t("vips.buy_modes.vip", ["price" => $item->price_vip]) . "</span>";
									
									break;
							}
						}else{
							echo "<span class='laranja'>". t("vips.buy_modes.vip", ["price" => $item->price_vip]) . "</span>";
						}
					?>
				</span>
				<?php if ($item->id == 429): ?>
					<select name="character_id" class="form-control" style="width:140px">
						<?php foreach ($animes as $anime): ?>
							<optgroup label="<?php echo $anime->description()->name ?>">
							<?php foreach ($anime->characters($_SESSION['universal'] ? '' : ' AND active=1') as $character): ?>
								<?php if ($character->id == $player->character_id) { continue; } ?>
								<option value="<?php echo $character->id ?>"><?php echo $character->description()->name ?></option>
							<?php endforeach ?>
							</optgroup>
						<?php endforeach ?>
					</select>
				<?php endif ?>
				<?php if ($item->id == 1864): ?>
					<?php if($player_vip_items):?>
					<select name="character_id" class="form-control" style="width:140px">
						<?php foreach ($player_vip_items as $player_vip_item): ?>
							<?php $character = $player_vip_item->characters($player_vip_item->character_id); ?>
								<?php if ($character->id == $player->character_id) { continue; } ?>
								<option value="<?php echo $character->id ?>"><?php echo $character->description()->name ?></option>
						<?php endforeach ?>
					</select>
					<?php else: ?>
						<?php echo t("vips.no_memory") ?>
					<?php endif ?>
				<?php endif ?>
	
				<?php if ($item->id == 430): ?>
					<input type="text" name="name" class="form-control" style="width:140px" />
				<?php endif ?>
				<?php if ($item->id == 1745): ?>
					<input type="text" name="name_organization" class="form-control" style="width:140px" />
				<?php endif ?>
				<?php /*if ($item->id == 1746): ?>
					<select name="faction" class="form-control" style="width:140px">
						<?php if($player->faction_id != 1){?>
						<option value="1">Heroi</option>
						<?php }else{?>
						<option value="2">Vilão</option>
						<?php }?>
					</select>
				<?php endif*/ ?>
				<?php 
					if ($player->has_item(1715) && $item->id == 1715) {
				?>		
					<a class="btn btn-<?php echo $player->no_talent ? "danger":"success"?> no-talent" data-id="<?php echo $item->id ?>"><?php echo $player->no_talent ? "Desativar":"Ativar"?> ( <?php echo $item1715->quantity?> Restantes )</a>
				<?php
					}		
				?>			
			</td>
			<td width="140" align="center">
				<a class="btn btn-primary buy" data-id="<?php echo $item->id ?>"><?php echo t("vips.buy_now") ?></a>
			</td>
		</tr>
		<tr height="4"></tr>	
	</table>
</form>
