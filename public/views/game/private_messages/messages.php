<?php if (!sizeof($messages)): ?>
	<?php echo t('private_messages.no_messages') ?>
<?php else: ?>
	<table width="725" border="0" cellpadding="0" cellspacing="0">
		<?php $counter = 0 ?>
		<?php foreach ($messages as $message): ?>
			<?php $color = $counter++ % 2 ? '091e30' : '173148'; ?>
			<tr height="48" bgcolor="<?php echo $color ?>">
				<td width="60" align="center"><input type="checkbox" data-id="<?php echo $message->id ?>" /></td>
				<td width="140" align="center">
					<?php if ($message->from_id): ?>
						<?php echo $message->from()->name ?>
					<?php else: ?>
						<?php echo t('global.system_message') ?>
					<?php endif ?>
				</td>
				<td width="180" align="center"><a href="javascript:;" class="message" data-id="<?php echo $message->id ?>" data-sender="<?php echo $message->from_id ?>"><?php echo $message->subject ?></a></td>
				<td width="120" align="center"><?php echo date('d/m/Y H:i:s', strtotime($message->created_at)) ?></td>
				<td width="100" align="center">
					<?php if (!$message->read_at): ?>
						<img src="<?php echo image_url('icons/pm_new.png') ?>" />
					<?php else: ?>
						<img src="<?php echo image_url('icons/pm.png') ?>" />
					<?php endif ?>
				</td>
				<td width="100" align="center">
					<?php if ($message->reply()): ?>
						<img src="<?php echo image_url('icons/pm_replied.png') ?>" />
					<?php endif ?>
				</td>
			</tr>
		<?php endforeach ?>
	</table>
	<?php if(sizeof($messages2) > 10):?>
		<?php echo partial('shared/paginator', ['pages' => $pages, 'current' => $page + 1]) ?>
	<?php endif ?>
<?php endif ?>
