<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>O beta começou!</title>
</head>
<body>
	<img src="<?=image_url('emails/beta_launch/top.jpg')?>">
	<h3>Olá, <?php echo $user['name'] ?></h3>
	<p>O beta do <?=GAME_NAME;?> acaba de começar!</p>
	<p>Gostariamos de deixar apenas um lembrete antes de mais nada.</p>
	<p>A fase BETA, significa que estamos em fase de testes, então muitos erros podem ocorrer, e estaremos disponibilizando o sistema de suporte em breve.</p>
	<p>Se você perceber algum erro ou comportamente estranho, por favor, reporte!</p>
	<hr />
	<p>Acesse <a href="<?=make_url('home');?>"><?=make_url('home');?></a> e entre com seu e-mail e senha para começar a jogar agora!</p>
	<hr />
	<p>A equipe do <?=GAME_NAME;?> agradece o seu interesse em conhecer o nosso jogo!</p>
</body>
</html>