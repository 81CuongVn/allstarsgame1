<?php echo partial('shared/title', array('title' => 'attributes.attributes.title', 'place' => 'attributes.attributes.title')) ?>
<?php if(!$player_tutorial->treinamento){?>
<script>
$(function () {
	 $("#conteudo.with-player").css("z-index", 'initial');
	 $(".info").css("z-index", 'initial');
	 $("#background-topo2").css("z-index", 'initial');
	
    var tour = new Tour({
	  backdrop: true,
	  page: 8,
	 
	  steps: [
	  {
		element: "#training-distribute-container",
		title: "Fique mais Forte!",
		content: "A cada tantos pontos treinados em um Atributo, você irá receber um ponto completo no mesmo. Evolua o seu Nível de Conta para receber mais pontos livres!",
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
<?php }?>
<?php /*<div id="traning-limit-container">
	<?php echo partial('traning_limit', ['player' => $player]) ?>
</div>
<br /><br />
<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
	<table width="725" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td width="325" align="center"><?php echo t('attributes.attributes.headers.automatic_training') ?></td>
			<td width="250" align="center"><?php echo t('attributes.attributes.headers.quantity') ?></td>
			<td width="150" align="center"><?php echo t('attributes.attributes.headers.status') ?></td>
		</tr>
	</table>
</div>
<table width="725" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td align="center" width="325">
			Escolha o tempo que quer deixar treinando automático, esse recurso pode ser usado sempre que você quiser.
		</td>
		<td align="center" width="250">
			<select class="form-control input-sm" style="width: auto">
				<option value="1">Treinar por 30 Minutos</option>
				<option value="2">Treinar por 30 Minutos</option>
				<option value="3">Treinar por 30 Minutos</option>
			</select>
		</td>
		<td align="center" width="150">
			<a class="btn btn-sm btn-primary train"><?php echo t('attributes.attributes.train') ?></a>
		</td>
	</tr>
</table>
<br />*/ ?>
<div id="training-distribute-container" style="clear:both">
</div>