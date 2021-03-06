<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
<table width="725" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="105" align="center"><?php echo t('support.header.id') ?></td>
		<td width="200" align="center"><?php echo t('support.header.title') ?></td>
		<td width="110" align="center"><?php echo t('support.header.category') ?></td>
		<td width="140" align="center"><?php echo t('support.header.status') ?></td>
		<td width="170" align="center"><?php echo t('support.header.open_date') ?></td>
	</tr>
</table>
</div>
<table width="725" border="0" cellpadding="0" cellspacing="0" class="table table-striped" style="width: 725px">	
	<?php foreach ($tickets as $ticket): ?>
		<tr>
			<td width="105" align="center">
				<a href="<?php echo make_url('support#ticket/' . $ticket->id) ?>">#<?php echo $ticket->id ?></a>
			</td>
			<td width="200" align="center">
				<a href="<?php echo make_url('support#ticket/' . $ticket->id) ?>"><?php echo $ticket->title ?></a>
			</td>
			<td width="110" align="center">
				<?php echo $ticket->category()->name ?>
			</td>
			<td width="140" align="center">
				<span class="laranja"><?php echo $ticket->status()->name ?></span>
			</td>
			<td width="170" align="center">
				<?php echo date('d/m/Y H:i:s', strtotime($ticket->created_at)) ?>
			</td>
		</tr>
	<?php endforeach ?>
	<?php if (!sizeof($tickets)): ?>
		<td colspan="5"><?php echo t('support.no_results') ?></td>
	<?php endif ?>
</table>
<?php echo partial('shared/paginator', ['pages' => $pages, 'current' => $page + 1]) ?>
