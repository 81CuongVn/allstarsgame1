<?php echo partial('shared/title', array('title' => 'vips.title', 'place' => 'vips.title')) ?>
<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
	<table width="725" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="80">&nbsp;</td>
		<td width="290" align="center">Nome / Descrição</td>
		<td width="215" align="center">Custo</td>
		<td width="140" align="center">Status</td>
	</tr>
	</table>
</div>
<div class="item-vip-list">
	<?php 
		$counter = 0;
		foreach ($vips as $vip): 
		$color	= $counter++ % 2 ? '091e30' : '173148';
		if($vip->sorting==0){
	?>
		
		<?php echo partial("item", ["item" => $vip, "player" => $player, "animes" => $animes, "player_vip_items" => $player_vip_items, "color" => $color]) ?>
	<?php }
		endforeach 
	?>
</div>
