<?php echo partial('shared/title', array('title' => 'attributes.attributes.title', 'place' => 'attributes.attributes.title')) ?>
<?php if (!$player_tutorial->treinamento) { ?>
	<script type="text/javascript">
		$(function () {
			var tour = new Tour({
				backdrop: true,
				page: 8,
				steps: [{
					element: "#training-distribute-container",
					title: "Fique mais Forte!",
					content: "A cada tantos pontos treinados em um Atributo, você irá receber um ponto completo no mesmo. Evolua o seu Nível de Conta para receber mais pontos livres!",
					placement: "top"
				}]
			});

			tour.restart();
			tour.init(true);
			tour.start(true);
		});
	</script>
<?php } ?>
<div id="training-distribute-container" style="clear:both">
</div>