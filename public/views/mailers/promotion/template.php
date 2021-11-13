<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title></title>
</head>
<body>
	<h3><?=t('emails.promotion.user', [ 'user' => $user->name ]);?></h3>
	<p><?=t('emails.promotion.message');?></p>
	<div style="clear: block"></div>
	<div style="width: 90%; margin: 0px auto">
		<span style="font-size: 12px">
			<a href="<?=make_url('home');?>" target="_blank"><?=make_url('home');?></a>
		</span>
	</div>
	<div style="clear: block"></div>
</body>
</html>
