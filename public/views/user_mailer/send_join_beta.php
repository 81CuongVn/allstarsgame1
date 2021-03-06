<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
	<h3><?php echo t('emails.join.welcome_message_beta', array('user' => $user->name)) ?></h3>
	<p><?php echo t('emails.join.message_beta') ?></p>
	<div style="clear: block"></div>
	<div style="width: 70%; margin: 0px auto">
		<span style="font-size: 12px"><?php echo make_url('users#beta_activate/' . $user->activation_key) ?></span>
	</div>
	<div style="clear: block"></div>
	<br />
	<br />
	<div style="width: 70%; margin: 0px auto; text-align: center">
		<span style="font-size: 24px"><?php echo $user->activation_key ?></span>
	</div>
</body>
</html>