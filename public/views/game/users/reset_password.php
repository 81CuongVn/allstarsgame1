<?php echo partial('shared/title', array('title' => 'users.password_reset.title', 'place' => 'users.password_reset.title')) ?>
<p><?php echo t('users.password_reset.text') ?></p>
<hr />
<form method="post" role="form" class="form-horizontal" id="reset-password-form">
	<div class="form-group">
		<label class="col-sm-2"><?php echo t('users.password_reset.email') ?></label>
		<div class="col-sm-10">
			<input type="text" name="email" class="form-control input-sm" />
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2"><?php echo t('users.password_reset.captcha') ?></label>
		<div class="col-sm-10">
			<div class="row">
				<div class="col-sm-3">
					<img src="<?php echo make_url('captcha/reset_password') ?>" style="float: left">
				</div>
				<div class="col-sm-9">
					<input type="text" name="captcha" class="form-control input-sm" style="margin-top: 5px" />
				</div>
			</div>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-10 col-sm-offset-2">
			<input type="submit" value="<?php echo t('users.password_reset.reset') ?>" class="btn btn-sm btn-primary" />
		</div>
	</div>
</form>