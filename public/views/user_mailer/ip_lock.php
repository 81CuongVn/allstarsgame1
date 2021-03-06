<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
	<h3><?php echo t('emails.ip_lock.user', array('user' => $user->name)) ?></h3>
	<p><?php echo t('emails.ip_lock.message') ?></p>
	<div style="clear: block"></div>
	<div style="width: 90%; margin: 0px auto">
		<span style="font-size: 12px"><?php echo make_url('users#account_locked/' . $user->ip_lock_key) ?></span>
	</div>
	<div style="clear: block"></div>
</body>
</html>