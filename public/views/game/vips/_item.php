<?php
	if($player->has_item(1715)){
		$item1715 = PlayerItem::find_first("player_id=".$player->id." AND item_id=1715");
	}
?>
<form id="vip-form-<?php echo $item->id ?>" onsubmit="return false">
	<input type="hidden" name="id" value="<?php echo $item->id ?>" />
	<table width="725">
		<tr bgcolor="<?php echo $color ?>" height="60">
			<td width="80" class="text-center">
				<img src="<?php echo image_url($item->image(true)) ?>"  data-title="<?php echo $item->description()->name ?>" data-trigger="hover" data-placement="bottom" />
			</td>
			<td width="290" class="text-justify">
				<span class="amarelo" style="font-size:14px"><?php echo $item->description()->name ?></span><br /><?php echo $item->description()->description ?>
			</td>
			<td width="215" align="center">
				<span style="margin: 2px 0; display: block;">
					<?php
						if($item->id != 1709 && $item->id != 1715 && $item->id != 1718 && $item->id != 2112){
							switch ($item->get_buy_mode_for($player->id)) {
								case 0:
									echo "<span class='verde'>". t("vips.buy_modes.free"). "</span>";
									break;
								case 1:
									echo "<span class='laranja'>". t("vips.buy_modes.currency", ["price" => highamount($item->price_currency), "currency" => t('currencies.' . $player->character()->anime_id)]) . "</span>";
									break;
								default:
									echo "<span class='laranja'>". t("vips.buy_modes.vip", ["price" => highamount($item->price_credits)]) . "</span>";
									break;
							}
						}else{
							echo "<span class='laranja'>". t("vips.buy_modes.vip", ["price" => highamount($item->price_credits)]) . "</span>";
						}
					?>
				</span>
				<?php if ($item->id == 429): ?>
					<select name="character_id" class="form-control input-sm select2" style="width:170px">
						<?php foreach ($animes as $anime): ?>
							<optgroup label="<?php echo $anime->description()->name ?>">
							<?php foreach ($anime->characters() as $character): ?>
								<?php if ($character->id == $player->character_id) { continue; } ?>
								<option value="<?php echo $character->id ?>"><?php echo $character->description()->name ?></option>
							<?php endforeach ?>
							</optgroup>
						<?php endforeach ?>
					</select>
				<?php endif ?>
				<?php if ($item->id == 1864): ?>
					<?php if($player_vip_items):?>
					<select name="character_id" class="form-control input-sm" style="width:170px">
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
					<input type="text" name="name" class="form-control input-sm" style="width:170px" />
				<?php endif ?>
				<?php if ($item->id == 1745): ?>
					<input type="text" name="name_guild" class="form-control input-sm" style="width:170px" />
				<?php endif ?>
				<?php if ($item->id == 1746): ?>
					<select name="faction" class="form-control input-sm" style="width:170px">
						<?php foreach($factions as $faction) {
							if ($faction->id != $player->faction_id) {
								echo '<option value="' . $faction->id . '">' . $faction->description()->name . '</option>';
							}
						} ?>
					</select>
				<?php endif ?>
				<?php
					if ($player->has_item(1715) && $item->id == 1715) {
				?>
					<a class="btn btn-sm btn-<?php echo $player->no_talent ? "danger":"success"?> no-talent" data-id="<?php echo $item->id ?>"><?php echo $player->no_talent ? "Desativar":"Ativar"?> ( <?php echo $item1715->quantity?> Restantes )</a>
				<?php
					}
				?>
			</td>
			<td width="140" class="text-center">
				<a class="btn btn-sm btn-primary buy" data-id="<?php echo $item->id ?>"><?php echo t("vips.buy_now") ?></a>
			</td>
		</tr>
		<tr height="4"></tr>
	</table>
</form>
