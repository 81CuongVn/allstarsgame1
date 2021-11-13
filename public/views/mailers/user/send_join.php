<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title></title>
</head>
<body>
	<h3><?php echo t('emails.join.welcome_message', array('user' => $user->name)) ?></h3>
	<p><?php echo t('emails.join.message') ?></p>
	<div style="clear: block"></div>
	<div style="width: 70%; margin: 0px auto">
		<span style="font-size: 12px"><?php echo make_url('users#activate/' . $user->activation_key) ?></span>
	</div>
	<div style="clear: block"></div>
	<br />
	<br />
	<div style="width: 70%; margin: 0px auto; text-align: center">
		<span style="font-size: 24px"><?php echo $user->activation_key ?></span>
	</div>
</body>
</html>
