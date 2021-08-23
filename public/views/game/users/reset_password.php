<?php echo partial('shared/title', array('title' => 'users.password_reset.title', 'place' => 'users.password_reset.title')) ?>
<!-- AASG - Users -->
<ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-6665062829379662"
     data-ad-slot="3196308392"></ins>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({});
</script><br />
<div id="reset-password-box">
	<?=partial('shared/info', [
		'id'		=> 2,
		'title'		=> 'users.password_reset.title',
		'message'	=> '<form id="reset-password-form" onsubmit="return false;">
			<p>' . t('users.password_reset.text') . '</p><br />
			<input type="text" name="email" class="form-control input-sm" placeholder="' . t('users.password_reset.email') . '" required />
			<div class="text-right" style="margin-top: 15px;">
				<button type="submit" class="btn btn-sm btn-primary g-recaptcha" data-sitekey="' . $recaptcha['site'] . '" data-callback="doResetPassword">
					'. t('users.password_reset.reset') . '
				</button>
			</div>
		<//form>'
	]);?>
</div>
