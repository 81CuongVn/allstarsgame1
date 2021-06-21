<!DOCTYPE html>
<html>
<head>
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
