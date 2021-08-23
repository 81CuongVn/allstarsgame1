<?php echo partial('shared/title', array('title' => 'users.account_locked.title', 'place' => 'users.account_locked.title')) ?>
<!-- AASG - Users -->
<ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-6665062829379662"
     data-ad-slot="3196308392"></ins>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({});
</script><br />
<?php
	echo partial('shared/info', array(
		'id'		=> 1,
		'title'		=> 'users.account_locked.title2',
		'message'	=> t('users.account_locked.message')
	));
?>
<?php if (sizeof($errors)): ?>
<?php
	$message	= '';

	foreach($errors as  $error) {
		$message[]	= '<li>' . $error . '</li>';
	}

	echo partial('shared/info', array(
		'id'		=> 3,
		'title'		=> 'global.problem',
		'message'	=> t('global.following_errors') . '<ul>' . implode('', $message) . '</ul>'
	));
?>
<?php endif ?>
<hr />
<form method="post">
	<input type="hidden" value="<?php echo $_SESSION['ip_unlock_key'] ?>" name="ip_unlock_key" />
	<div align="center">
		<input type="submit" value="Desbloquear conta" class="btn btn-primary btn-lg" />
	</div>
</form>
