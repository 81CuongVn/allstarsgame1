<?php echo partial('shared/title', array('title' => 'vips.purchase.title', 'place' => 'vips.purchase.title')) ?>
<?php
if (!$is_dbl) {
	// $timestamp	= strtotime($is_dbl->data_end);
	$timestamp	= time();
	echo partial('shared/info', [
		'id'		=> 4,
		'title'		=> 'vips.make_donation.title',
		'message'	=> t('vips.make_donation.description') . ' <span class="laranja">'. date('d/m/Y H:i:s', $timestamp) .'<span>'
	]);
}
?>
<div class="titulo-home3">
	<p>Sistema de Estrelas</p>
</div>
<div class="conteudo-news" style="padding: 5px 10px;">
	<p style="text-align: justify;">
		O <?=GAME_NAME;?> é um jogo gratuito e sem fins lucrativos,
		e por isso a contribuição de seus jogadores é fundamental para que,
		cada dia mais, o jogo se desenvolva e melhore suas funcionalidades.
		Qualquer tipo de arrecadação ou doações feitas ao <?=GAME_NAME;?> serão revertidas em manutenção e melhorias ao site,
		bem como divulgação deste e dos animes. E contribuindo com sua doação ao jogo,
		além de nos ajudar a cada dia melhorar o <?=GAME_NAME;?>, você jogador, passa a ser um Jogador Estrela, com acesso à vantagens exclusivas.<br /><br />

		O sistema de Estrelas permite usufluir do valor doado de forma inteligente, e o valor doado é convertido para você em estrelas, conforme descrito abaixo:
	</p><br />
	<p>
		Ao fazer uma doação tenha a certeza que:<br />
		- Suas estrelas não expiram por falta de uso.<br />
		- Todos os personagens de sua conta podem usufluir das estrelas.<br />
		- Estar colaborando com a manuntenção e evolução do jogo.<br /><br />

		Com a opção Mercado Pago, você poderá doar com todos os Cartões de Crédito, Transferências Bancárias e Boletos Bancários e PIX.
	</p><br />
	<p class="verde">
		Todos as estrelas adquiridas só serão liberados após Confirmação da Doação,
		no caso de depósitos e cartões de créditos o limite é de 24 a 48 horas,
		no caso de boletos somente após a Confirmação da Doação que pode levar até 4 dias.
	</p>
</div><br />
<ul class="nav nav-pills nav-justified" id="methods-details-tabs">
	<?php $i = 1; foreach($methods as $method => $currency) { ?>
	<li class="<?php echo $i == 1 ? 'active' : ''; ?>">
		<a href="#method-<?php echo $method?>-list" role="tab" data-toggle="tab">
			<img src="<?php echo image_url($method . ".png")?>" width="147"/>
		</a>
	</li>
	<?php $i++; } ?>
</ul>
<br />
<div class="tab-content">
	<?php $i = 1; foreach($methods as $method => $currency) { $value = 'price_' . strtolower($currency); ?>
	<div id="method-<?php echo $method?>-list" class="tab-pane <?php echo $i == 1 ? 'active' : ''; ?>">
		<?php foreach($plans as $plan): ?>
		<div class="ability-speciality-box" data-id="<?php echo $plan->id; ?>" style="width: 237px !important; height: 260px !important">
			<div>
				<div class="image">
					<img src="<?php echo image_url('stars/' . $plan->id . '.png') ?>" />
				</div>
				<div class="name" style="height: 15px !important;">
					<?php echo $plan->name ?>
				</div>
				<div class="description" style="height: 40px !important;">
				<?php echo $plan->description ?><br />
				</div>
				<div class="details">
					<img src="<?php echo image_url("icons/vip-on.png" ) ?>" width="26" height="26"/><span class="amarelo_claro" style="font-size: 16px; margin-left: 5px; top: 2px; position: relative"><?php echo $is_dbl ? '<span class="vermelho" style="text-decoration: line-through; font-size: 12px">' . $plan->credits . '</span><span class="verde"> '. ($plan->credits * 2) .'</span>' :  $plan->credits; ?></span>
				</div>
				<div class="button" style="position:relative; top: 15px;">
					<a class="btn btn-sm btn-primary vip_purchase" data-message="<?php echo t('vips.done_donation.you_have') ?> <?php echo $symbols[$currency]; ?> <?php echo $plan->$value ?>, <?php echo t('vips.done_donation.you_have2') ?>" data-mode="<?php echo $plan->id?>" data-valor="<?php echo $method; ?>"><?php echo t('vips.done_donation.donation_by') ?> <?php echo $symbols[$currency]; ?> <?php echo $plan->$value ?></a>
				</div>
			</div>
		</div>
		<?php endforeach?>
	</div>
	<?php $i++; } ?>
</div>
