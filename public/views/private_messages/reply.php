<form class="form form-horizontal">
	<input type="hidden" name="to_id" value="<?php echo $orig_message ? $orig_message->from_id : '' ?>" />
	<?php if ($orig_message): ?>
		<input type="hidden" name="reply_id" value="<?php echo $orig_message->id ?>" />
	<?php endif ?>
	<div class="form-group">
		<label class="control-label col-sm-1"><?php echo t('private_messages.reply.to') ?></label>
		<div class="col-sm-11">
			<input type="text" class="form-control" name="to" value="<?php echo $orig_message ? $orig_message->from()->name : '' ?>" />
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-1"><?php echo t('private_messages.reply.subject') ?></label>
		<div class="col-sm-11">
			<input type="text" class="form-control" name="subject" value="<?php echo $orig_message ? 'RE:' . $orig_message->subject : '' ?>" />
		</div>
	</div>
	<div class="form-group row">
		<label class="control-label col-sm-1"><?php echo t('private_messages.reply.content') ?></label>
		<div class="col-sm-11">
			<textarea class="form-control" rows="10" name="content"><?php echo $reply_text ?></textarea>
		</div>
	</div>
</form>