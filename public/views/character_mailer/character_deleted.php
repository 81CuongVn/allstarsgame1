<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
	<h3><?php echo t('emails.character_deleted.user', ['user' => $user->name]) ?></h3>
	<p><?php echo t('emails.character_deleted.message', ['character' => $character->name]) ?></p>
	<div style="clear: block"></div>
	<div style="width: 90%; margin: 0px auto">
		<span style="font-size: 12px"><?php echo make_url('characters#remove/' . $character->id . '/' . $character->remove_key) ?></span>
	</div>
	<div style="clear: block"></div>
</body>
</html>