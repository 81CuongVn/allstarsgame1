<?php if (isset($beta)): ?>
	<?php echo partial('shared/title_battle', array('title' => 'users.activation.title', 'place' => 'users.activation.title')) ?>
	<?php echo partial('shared/info_battle', array('id'=> 1, 'title' => 'users.beta.messages.m2_title', 'message' => t('users.beta.messages.m2_message'))) ?>
<?php else: ?>
	<?php echo partial('shared/title', array('title' => 'users.activation.title', 'place' => 'users.activation.title')) ?>
	<?php echo partial('shared/info', array('id'=> 1, 'title' => 'users.activation.title', 'message' => t('users.activation.base_text'))) ?>	
<?php endif ?>
<form method="post" action="<?php echo make_url(isset($beta) ? 'users#beta_activate' : 'users#activate') ?>" clas="form">
	<div class="form-group">
		<label class="control-label"><?php echo t('users.activation.labels.key') ?></label>
		<input type="text" class="form-control input-sm" placeholder="<?php echo t('users.activation.placeholders.key') ?>" name="key" />
	</div>
	<div class="pull-right">
		<input type="submit" value="<?php echo t('buttons.proceed') ?>" class="btn btn-sm btn-primary" />
	</div>
	<div class="clearfix"></div>
</form>