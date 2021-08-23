<?php echo partial('shared/title', array('title' => 'vips.title', 'place' => 'vips.title')) ?>
<!-- AASG - Vips -->
<ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-6665062829379662"
     data-ad-slot="4540824433"></ins>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({});
</script><br />
<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
	<table width="725" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="80">&nbsp;</td>
		<td width="290" align="center">Nome / Descrição</td>
		<td width="215" align="center">Custo</td>
		<td width="140" align="center">Ação</td>
	</tr>
	</table>
</div>
<div class="item-vip-list">
	<?php
	$counter = 0;
	foreach ($vips as $vip) {
		if ($vip->id == 1745) {
			$player_guild = Guild::find_first('id='.$player->guild_id);
			if (!$player->guild_id || $player_guild->player_id != $player->id) {
				continue;
			}
		}
		$color	= $counter++ % 2 ? '091e30' : '173148';
		if ($vip->sorting == 0) {
			echo partial("item", [
				"item"				=> $vip,
				"player"			=> $player,
				"animes"			=> $animes,
				"player_vip_items"	=> $player_vip_items,
				"color"				=> $color,
				"factions"			=> $factions
			]);
		}
	}
	?>
</div>
