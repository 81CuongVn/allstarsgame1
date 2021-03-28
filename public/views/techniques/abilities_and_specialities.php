<?php echo partial('shared/title', array('title' => 'abilities.index.title', 'place' => 'abilities.index.title')) ?>
<?php if (!$player_tutorial->habilidades) { ?>
	<script type="text/javascript">
		$(function () {
			var tour = new Tour({
				backdrop: true,
				page: 2,
				steps: [{
					element: ".tutorial_ability",
					title: "Escolha sua Habilidade",
					content: "Mude de Habilidade a qualquer hora! Você poderá alterar o efeito clicando com o botão direito após selecioná-lo!",
					placement: "top"
				}, {
					element: ".tutorial_speciality",
					title: "Escolha sua Especialidade",
					content: "Assim como as Habilidades, você pode mudar de Especialidade a qualquer hora. Também é possível alterar o efeito clicando com o botão direito após selecioná-la.",
					placement: "top"
				}]
			});

			tour.restart();
			tour.init(true);
			tour.start(true);
		});
	</script>
<?php } ?>
<?php
	echo partial('shared/info', [
		'id'		=> 1,
		'title'		=> 'techniques.index.title2',
		'message'	=> t('techniques.index.description2')
	]);
?>
<br /><br />
<div id="ability-speciality-list">
	<div class="tutorial_ability">
	<div class="titulo-home3"><p>Escolha sua Habilidade</p></div>
	<?php foreach ($abilities as $ability): ?>
		<?php echo partial('ability_speciality_box', ['target' => $ability, 'player' => $player]) ?>
	<?php endforeach ?>
	<div class="clearfix"></div>
	</div>
	<div class="clearfix"></div><br />
	<div class="tutorial_speciality">
	<div class="titulo-home3"><p>Escolha sua Especialidade</p></div>
	<?php foreach ($specialities as $speciality): ?>
		<?php echo partial('ability_speciality_box', ['target' => $speciality, 'player' => $player]) ?>
	<?php endforeach ?>
	<div class="clearfix"></div>
	</div>
	<div class="clearfix"></div>
</div>