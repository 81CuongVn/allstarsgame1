<?php if ($rooms || $player) { ?>
<div class="barra-secao barra-secao-<?=$player->character()->anime_id;?>">
	<table width="725" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td width="200" align="center"><?=t('rankings.players.header.personagem');?></td>
			<td width="180" align="center"><?=t('rankings.players.header.nome');?></td>
			<td width="220" align="center"><?=t('battles.waiting.desc');?></td>
			<td width="140" align="center">Status</td>
			
		</tr>
	</table>
</div>
<table width="725" border="0" cellpadding="0" cellspacing="0">
<?php
	$counter = 0;
	if ($rooms) {
		foreach ($rooms as $room) {
			$p		= Player::find_first('id=' . $room->player_id);
			$color	= $counter++ % 2 ? '091e30' : '173148';
?>
            <tr bgcolor="<?=$color;?>">
                <td width="200" align="center"><?=$p->character_theme()->first_image()->small_image();?></td>
                <td width="180" align="center">
                    <b style="font-size:16px"><?=$p->name;?></b><br />
					Nível <?=$p->level;?>
                </td>
                <td width="220" align="center"><?=$room->room_name;?></td>
                <td width="140" align="center">
					<a class="btn btn-sm btn-primary enter-pvp-training-battle" data-id="<?=$room->id;?>">Aceitar Duelo</a>
				</td>
            </tr>
            <tr height="4"></tr>
<?php
		}
	} else {
?>
	<tr>
		<div align="center" style="padding-top: 10px">
			<b class="laranja" style="font-size:14px;"><?=t('battles.waiting.no_room');?></b>
		</div>
	</tr>
<?php
	}
?>      
</table>
<?php } ?>