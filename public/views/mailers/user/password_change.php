<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title></title>
</head>
<body>
	<h3><?php echo t('emails.password_change.user', array('user' => $user->name)) ?></h3>
	<p><?php echo t('emails.password_change.message') ?></p>
	<div style="clear: block"></div>
	<div style="width: 90%; margin: 0px auto">
		<span style="font-size: 12px"><?php echo make_url('users#reset_password/' . $user->reset_password_key) ?></span>
	</div>
	<div style="clear: block"></div>
</body>
</html>
