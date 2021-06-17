<div class="form-horizontal">
	<div class="form-group">
		<label class="control-label col-sm-2"><?php echo t('private_messages.reply.from') ?></label>
		<div class="col-sm-10" style="margin-top: 5px">
			<?php if ($message->from_id): ?>
				<?php echo $message->from()->name ?>
			<?php else: ?>
				<?php echo t('global.system_message') ?>
			<?php endif ?>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-2"><?php echo t('private_messages.reply.subject') ?></label>
		<div class="col-sm-10" style="margin-top: 5px">
			<?php echo $message->subject ?>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-2"><?php echo t('private_messages.reply.content') ?></label>
		<div class="col-sm-10" style="margin-top: 5px">
			<?php echo nl2br($message->content) ?>
		</div>
	</div>
</div>