<br />
<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
	<table width="730" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="30">&nbsp;</td>
		<td width="200" align="center">Nome</td>
		<td width="100" align="center">Líder</td>
		<td width="100" align="center">Membros</td>
		<td align="center" width="300">Status</td>
	</tr>
	</table>
</div>
<table width="730" border="0" cellpadding="0" cellspacing="0">
	<?php if (sizeof($guilds)): ?>
		<?php $counter = 0; ?>
		<?php foreach ($guilds as $guild):
				$color	= $counter++ % 2 ? '091e30' : '173148';
		?>
			<tr id="guild-search-item-<?php echo $guild->id ?>" height="50" bgcolor="<?php echo $color ?>">
				<td width="30">&nbsp;</td>
				<td width="200" align="center"><span style="font-size:14px" class="amarelo"><?php echo $guild->name ?></span></td>
				<td width="100" align="center" class="azul">
					<span style="font-size:13px"><?php echo $guild->leader()->name ?></span>
				</td>
				<td width="100" align="center">
					<?php echo highamount($guild->member_count); ?>
				</td>
				<td width="300" align="center">
					<a class="btn btn-sm btn-primary details" data-id="<?php echo $guild->id ?>"><?php echo t('guilds.search.details') ?></a>
					<?php
						$request	= GuildRequest::find_first('player_id=' . $player->id . ' AND guild_id=' . $guild->id);
					?>
					<a class="btn btn-sm btn-primary join <?php echo $request ? 'disabled' : '' ?>" data-id="<?php echo $guild->id ?>"><?php echo t('guilds.search.join') ?></a>
				</td>
			</tr>
		<?php endforeach ?>
	<?php else: ?>
		<?php echo t('guilds.search.nothing') ?>
	<?php endif ?>
</table>
