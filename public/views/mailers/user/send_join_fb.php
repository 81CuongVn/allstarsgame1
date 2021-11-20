<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title></title>
</head>
<body>
	<h3><?php echo t('emails.join_fb.welcome_message', array('user' => $user->name)) ?></h3>
	<p><?php echo t('emails.join_fb.message', [
		'email'		=>  $user->email,
		'password'	=>  $user->password
	]) ?></p>
	<div style="clear: block"></div>
</body>
</html>
