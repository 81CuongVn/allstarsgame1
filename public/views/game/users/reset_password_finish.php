<?php echo partial('shared/title', array('title' => 'users.password_reset.title', 'place' => 'users.password_reset.title')) ?>
<?=partial('shared/info', [
	'id'		=> 2,
	'title'		=> 'users.password_reset.title',
	'message'	=> '<form id="reset-password-form" onsubmit="return false;">
		<p>' . t('users.password_reset.text2') . '</p><br />
		<div class="row">
			<div class="col-md-4">
				<input type="password" name="password" class="form-control input-sm" placeholder="' . t('users.password_reset.password') . '" required />
			</div>
			<div class="col-md-4">
				<input type="password" name="password_confirmation" class="form-control input-sm" placeholder="' . t('users.password_reset.password_confirmation') . '" required />
			</div>
			<div class="col-md-4 text-center">
				<button type="submit" class="btn btn-sm btn-primary">
					'. t('users.password_reset.reset_finish') . '
				</button>
			</div>
		</div>
	<//form>'
]);?>
