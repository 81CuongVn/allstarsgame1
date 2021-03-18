<?=partial('shared/title', [
	'title' => 'battles.pvp.title',
	'place' => 'battles.pvp.title'
]);?>
<?php if (!$player_tutorial->battle_pvp) { ?>
<script>
$(function () {
	 $("#conteudo.with-player").css("z-index", 'initial');
	 $(".info").css("z-index", 'initial');
	 $("#background-topo2").css("z-index", 'initial');
	
    var tour = new Tour({
	  backdrop: true,
	  page: 16,
	 
	  steps: [
	  {
		element: ".msg-container",
		title: "Busque seu Oponente",
		content: "Ao entrar na Fila PVP, você gastará 2 de Stamina e terá que esperar achar algum outro jogador que também estará na Fila. Ao encontrar, você deverá aceitar a batalha e esperar que ele faça o mesmo. Se você cancelar e sair da fila, receberá sua Stamina de volta.",
		placement: "top"
	  }
	]});
	//Renicia o Tour
	tour.restart();
	
	// Initialize the tour
	tour.init(true);
	
	// Start the tour
	tour.start(true);
	
});
</script>	
<?php } ?>
<div class="msg-container">
	<div class="msg_top"></div>
	 <div class="msg_repete">
		<div class="msg" style="background:url(<?=image_url('msg/'. $player->character()->anime_id . '-1.png');?>); background-repeat: no-repeat;">
		</div>
		<div class="msgb" style="position:relative; margin-left: 231px; text-align: left; top: -37px">
			<b><?=t('battles.pvp.m1_title');?></b>
			<div class="content">
				<?=t('battles.pvp.m1_message');?>
				<br /><br /> 
				<?php if ($player->is_pvp_queued) { ?>
					<a href="javascript:;" id="1x-queue-data" class="btn btn-danger btn-lg"><?=t('battles.sair_fila');?></a>
				<?php } else { ?>
					<a id="battle-pvp-enter-queue" href="javascript:void(0);" class="btn btn-primary btn-lg"><?=t('battles.ir_fila');?></a>
				<?php } ?>
			</div>
		</div>
	</div>
	<div class="msg_bot"></div>	
	<div class="msg_bot2"></div>
</div>