<?php echo partial('shared/title', array('title' => 'techniques.index.title', 'place' => 'techniques.index.title')) ?>
<?php if (FW_ENV != 'dev') { ?>
	<!-- AASG - Techniques -->
	<ins class="adsbygoogle"
		style="display:inline-block;width:728px;height:90px"
		data-ad-client="ca-pub-6665062829379662"
		data-ad-slot="7857252910"></ins>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
	</script><br />
<?php } ?>
<?php if (!$player_tutorial->golpes) { ?>
	<script type="text/javascript">
		$(function () {
			var tour = new Tour({
				backdrop: true,
				page: 10,
				steps: [{
					element: ".tutorial-equipados",
					title: "Montando sua Build",
					content: "Você poderá escolher apenas 10 Golpes para usar em Batalha, então teste constantemente novas combinações até achar o seu estilo de jogo!",
					placement: "top"
				}, {
					element: ".tutorial-1",
					title: "Modificadores",
					content: "Golpes com borda roxa são modificadores, golpes com efeitos especiais que irão aumentar seus atributos em combate, mas fique atento pois seu turno irá passar após utilizá-lo.",
					placement: "top"
				}, {
					element: ".tutorial-3",
					title: "Golpes Defensivos",
					content: "Golpes com borda azul são defensivos, que podem bloquear e contra atacar os golpes inimigos.",
					placement: "top",
				}, {
					element: ".tutorial-7",
					title: "Golpes Ofensivos",
					content: "Golpes com borda vermelha são Genéricos e com borda laranja são Únicos de cada personagem. São golpes para causar dano no oponente, sendo que alguns possuem efeitos especiais como Sangramento, Confusão, Lentidão e Atordoamento.",
					placement: "top",
				}, {
					element: ".tutorial-disponiveis",
					title: "Dezenas de Golpes",
					content: "Inicialmente você poderá escolher entre 25 golpes genéricos e 10 únicos, mas com o tempo aprenderá novos golpes através do Grimório Proibido e Modo Aventura! Para equipá-los, basta clicar e arrastar até um dos dez espaços acima.",
					placement: "top",
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
		'id'		=> 4,
		'title'		=> 'techniques.index.how_to_title',
		'message'	=> t('techniques.index.how_to_text')
	]);
?>
<div class="titulo-home3"><p>Golpes Equipados</p></div>
<div id="eqquiped-technique-list" class="technique-list-box tutorial-equipados">
	<?php for($f = 0; $f < MAX_EQUIPPED_ATTACKS; $f++): ?>
		<?php
			$item	= $player->has_technique_at($f);

			echo partial('item', [
				'item'		=> $item ? $item->item() : false,
				'player'	=> $player,
				'type'		=> 'drop',
				'slot'		=> $f
			]);
		?>
	<?php endfor; ?>
	<div class="clearfix"></div>
</div>
<br />
<div class="titulo-home3"><p>Golpes Disponíveis</p></div>
<div id="technique-list" class="technique-list-box tutorial-disponiveis">
	<?php
	foreach ($items as $item) {
		$item->set_player($player);
		$item->formula(true);

		echo partial('item', [
			'item'		=> $item,
			'player'	=> $player,
			'type'		=> 'source'
		]);
	}
	?>
	<div class="clearfix"></div>
</div>
