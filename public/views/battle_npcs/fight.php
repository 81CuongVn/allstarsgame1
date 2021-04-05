<?=partial('shared/title_battle', [
	'title' => 'battles.npc.fight.title',
	'place' => 'battles.npc.fight.title'
]);?>
<?php if (!$player_tutorial->battle_npc) { ?>
	<script type="text/javascript">
		$(function () {
			var tour = new Tour({
				backdrop: true,
				page: 17,
				steps: [{
					element: "#player",
					title: "Seu Personagem",
					content: "O mais importante de uma Batalha é saber controlar sua Mana! Você precisará dela para executar os Golpes, ativar sua Habilidade e Especialidade! Sua Mana irá recuperar 2 Pontos por Turno, então controle sua mana e controlará a partida!",
					placement: "right"
				}, {
					element: "#enemy",
					title: "Seu Oponente",
					content: "Estude seu oponente! É importante aprender que golpes ele poderá usar, a Habilidade e Especialidade, e quanto de Mana ele ainda possui! Seu objetivo é zerar a Vida dele antes que ele zere a sua.",
					placement: "left"
				}, {
					element: "#vs",
					title: "Resumo de Combate",
					content: "Fique de olho nesta parte da Batalha, em uma Batalha PVP você terá apenas 1:30 para atacar! Aqui também irá mostrar o dano causado e se algum efeito aconteceu, como um golpe Crítico, por exemplo.",
					placement: "top"
				}, {
					element: "#player .bg-attributes",
					title: "Suas Habilidades",
					content: "Essa pode ser sua chave para a vitória! Escolha uma Habilidade e uma Especialidade que melhor se adapte ao seu jeito de lutar, utilize na hora certa e surpreenda seu inimigo!",
					placement: "right"
				}, {
					element: ".technique-list-box",
					title: "Seus Golpes",
					content: "Aqui ficam seus 10 Golpes Equipados. É importante balancear bem o Custo de Mana para você controlar bem a Batalha, atacando sempre que possível.",
					placement: "top"
				}, {
					element: ".technique-list-box",
					title: "Atributos dos Golpes",
					content: "Em ordem, os ícones dos golpes significam: Ataque/Defesa, Custo de Mana, Tempo de Recarga (quantidade de turnos que poderá ser utilizado novamente), Chance de Acerto e Tempo de Execução (quanto menor, mais rápido, usado principalmente no último golpe da batalha).",
					placement: "top"
				}, {
					element: ".technique-list-box",
					title: "Vantagens e Desvantagens",
					content: "Existem 5 Tipos de Golpes, cada um com uma Vantagem sob o outro. A ordem de Vantagens é: Corpo-a-corpo > Mágico > Atirador > Guerreiro > Elemental > Corpo-a-corpo.",
					placement: "top"
				}, {
					element: "#skip-turn",
					title: "Recuperando suas Forças",
					content: "Caso ficar com pouca Mana, você poderá Pular o Turno e assim apenas Recuperar 2 Pontos de Mana, sem gastar nada. Use esta recuperação estrategicamente e vença seus inimigos!",
					placement: "bottom"
				}]
			});
			tour.restart();
			tour.init(true);
			tour.start(true);
		});
	</script>	
<?php } ?>
<?=partial('shared/fight_panel', [
	'player'		=> $player,
	'enemy'			=> $npc,
	'techniques'	=> $techniques,
	'target_url'	=> $target_url,
	'log'			=> $log,
	'is_watch'		=> FALSE
]);?>