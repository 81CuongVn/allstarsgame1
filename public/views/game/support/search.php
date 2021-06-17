<div class="barra-secao barra-secao-<?=($player ? $player->character()->anime_id : rand(1, 40));?>">
	<table width="725">
		<tr>
			<td width="305" class="text-center"><?=t('support.header.title');?></td>
			<td width="110" class="text-center"><?=t('support.header.player');?></td>
			<td width="110" class="text-center"><?=t('support.header.category');?></td>
			<td width="140" class="text-center"><?=t('support.header.status');?></td>
			<td width="170" class="text-center"><?=t('support.header.open_date');?></td>
		</tr>
	</table>
</div>
<table width="725" class="table table-striped">
	<?php foreach ($tickets as $ticket) { ?>
		<?php
		$tuser		= $ticket->user();
		$user_name	= explode(' ', $tuser->name);
		$tplayer	= $ticket->player();
		?>
		<tr>
			<td width="305" class="text-left">
				<a href="<?=make_url('support#ticket/' . $ticket->id);?>">
					#<?=$ticket->id;?> - <?=$ticket->title;?>
				</a>
			</td>
			<td width="110" class="text-center">
				<!-- <?=$user_name[0];?> -->
				<?php if ($tplayer) { ?>
					<?=$tplayer->name;?>
				<?php } ?>
			</td>

			<td width="110" class="text-center">
				<?=$ticket->category()->name;?>
			</td>
			<td width="140" class="text-center">
				<span class="laranja"><?=$ticket->status()->name;?></span>
			</td>
			<td width="170" class="text-center">
				<?=date('d/m/Y H:i:s', strtotime($ticket->created_at));?>
			</td>
		</tr>
	<?php } ?>
	<?php if (!sizeof($tickets)) { ?>
		<td colspan="5"><?=t('support.no_results');?></td>
	<?php } ?>
</table>
<?=partial('shared/paginator', [
	'pages'		=> $pages,
	'current'	=> ($page + 1)
]) ?>
