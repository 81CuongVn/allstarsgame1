<?php if($_SESSION['universal']){

}
?>
<?php if(!$player_tutorial->status){?>
    <script>
        $(function () {
            $("#conteudo.with-player").css("z-index", 'initial');
            $(".info").css("z-index", 'initial');
            $("#background-topo2").css("z-index", 'initial');

            var tour = new Tour({
                backdrop: true,
                page: 1,

                steps: [
                    {
                        element: ".top-expbar-container",
                        title: "Bem Vindo ao <?=GAME_NAME;?>!",
                        content: "Ganhe experiência batalhando e realizando missões! Sua vida e sua mana serão recuperados no final de cada batalha, porém sua Stamina irá recuperar 2 a cada 5 minutos, ou comendo Alimentos!",
                        placement: "left"
                    },
                    {
                        element: ".top-expbar-container",
                        title: "Evolua sua Conta",
                        content: "Além do personagem, você também recebe Experiência de Conta, que é compartilhada entre todos os seus personagens. Ao evoluir sua conta, você pode aprender novos Talentos e Treinar Atributos!",
                        placement: "right"
                    },
                    {
                        element: ".tutorial_formulas",
                        title: "Atributos de Combate",
                        content: "Coloque o mouse em cima de cada ícone para entender melhor cada um! Você aumenta seus atributos através de Equipamentos e evoluindo o level da sua conta.",
                        placement: "right",
                    },
                    {
                        element: ".h-combates",
                        title: "Estatísticas de Batalha",
                        content: "Suas vitórias, derrotas e empates contra NPCs e outros jogadores ficarão marcadas aqui!",
                        placement: "left",
                    },
                    {
                        element: ".tutorial_missoes",
                        title: "Estatísticas de Missões",
                        content: "Resumo de suas missões concluídas com sucesso. Cada missão completa te dá uma certa quantidade de Pontos.",
                        placement: "top",
                    },
                    {
                        element: ".msg-container",
                        title: "Batalhas Competitivas",
                        content: "Nas terças, quintas e domingos as Batalhas PVP automaticamente se tornam Batalhas da Liga, onde você terá 25 batalhas por Rank para ir para o próximo. No final do mês receberá recompensas baseada no Rank que parou.",
                        placement: "left",
                    },
                    {
                        element: ".tutorial_ranking",
                        title: "Colocação do Personagem",
                        content: "Consiga Pontos por cada coisa realizada pelo jogo e suba sua colocação para ser reconhecido por todos seus amigos e inimigos!",
                        placement: "left",
                    },
                    {
                        element: ".tutorial_profile",
                        title: "Customização do Personagem",
                        content: "Consiga títulos completando Conquistas e outras tarefas pelo site. Você também poderá mudar o Tema de seu personagem e escolher entre diversas Imagens!",
                        placement: "right",
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
<script>
    $(document).ready(function() {
        if (!$.cookie('guide-game')) {
            var guideGame	= $('#guide');
            guideGame.css("display","block");
            var	win	= bootbox.dialog({
                message: '...',
                buttons: [
                {
                    label: 'Fechar',
                    class:	'btn btn-default'
                }
            ]});
            $('.modal-dialog', win).addClass('pattern-container');
            $('.modal-content', win).addClass('with-pattern');
            $('.bootbox-body', win).html(guideGame);

            // Gerando um cookie
            $.cookie('guide-game', 1);
        }
    });
</script>
<div id="guide" style="display:none">
    <div class="msg-container">
        <div class="msg_top"></div>
        <div class="msg_repete">
            <div class="msg" style="background:url(<?php echo image_url('msg/guia.png')?>); background-repeat: no-repeat;">
            </div>
            <div class="msgb" style="position:relative; margin-left: 231px; text-align: left; top: -37px">
                <b><?php echo t('guide.title') ?></b>
                <div class="content"><?php echo t('guide.description', [
                        'game'  => GAME_NAME,
                        'link'  => make_url('guides#game')
                    ]) ?></div>
            </div>
        </div>
        <div class="msg_bot"></div>
        <div class="msg_bot2"></div>
    </div>
</div>
<?=partial('shared/title', [
	'title'	=> 'characters.status.title',
	'place'	=> 'characters.status.title'
]);?>
<div style="width: 730px; position: relative;">
    <div style="position: relative; float: left; width:365px;">
		<div class="tutorial_formulas">
			<div class="titulo-home"><p>Fórmulas</p></div>
			<?php foreach ($formulas as $_ => $formula) { ?>
				<div class="bg_td">
					<div class="amarelo atr_float" style="width: 130px; text-align:left; padding-left:16px; text-align: center"><?=$formula;?></div>
					<div class="atr_float" style="width: 20px; text-align:left;margin-left: 6px;">
						<img src="<?=image_url('icons/' . $_ . '.png');?>" style="position: relative; top: -5px; left: 2px;" class="requirement-popover" data-source="#attribute-tooltip-<?=$_;?>" data-title="<?=t('formula.tooltip.title.' . $_);?>" data-trigger="hover" data-placement="bottom" />
						<div id="attribute-tooltip-<?=$_;?>" class="status-popover-container">
							<div class="status-popover-content"><?=t('formula.tooltip.description.' . $_);?></div>
						</div>
					</div>
					<div class="atr_float" style="margin-top: 7px; margin-left: 20px">
						<?=exp_bar($player->{$_}(), $max, 175);?>
					</div>
				</div>
			<?php } ?>
		</div>
    </div>
</div>
<div class="h-combates">
    <div style="width: 341px; text-align: center; padding-top: 12px"><b class="amarelo" style="font-size:13px">Resumo de Combate</b></div>
    <div style="width: 341px; text-align: center; padding-top: 22px; font-size: 12px !important; line-height: 15px;">
        <span class="verde"><?=t('characters.status.wins_npc');?>:</span> <?=highamount($player->wins_npc);?> <br />
        <span class="verde"><?=t('characters.status.wins_pvp');?>:</span> <?=highamount($player->wins_pvp);?> <br />
        <span class="vermelho"><?=t('characters.status.losses_npc');?>:</span> <?=highamount($player->losses_npc);?> <br />
        <span class="vermelho"><?=t('characters.status.losses_pvp');?>:</span> <?=highamount($player->losses_pvp);?> <br />
        <span><?=t('characters.status.draws_npc');?>:</span> <?=highamount($player->draws_npc);?> <br />
        <span><?=t('characters.status.draws_pvp');?>:</span> <?=highamount($player->draws_pvp);?>
    </div>
</div>
<div class="h-missoes tutorial_missoes">
    <div style="width: 341px; text-align: center; padding-top: 12px"><b class="amarelo" style="font-size:13px">Missões Completas</b></div>
    <div style="width: 341px; text-align: center; padding-top: 22px; font-size: 12px !important; line-height: 15px;">
        <span class="verde"><?php echo t('characters.status.time') ?>:</span> <?php echo $quest_counters->time_total ?><br />
        <span class="verde"><?php echo t('characters.status.especiais') ?>:</span> <?php echo $quest_counters->pvp_total ?><br />
        <span class="verde"><?php echo t('characters.status.daily') ?>:</span> <?php echo $quest_counters->daily_total ?><br />
        <span class="verde"><?php echo t('characters.status.pet') ?>:</span> <?php echo $quest_counters->pet_total ?><br />
        <span class="verde"><?php echo t('characters.status.account') ?>:</span> <?php echo $user_quest_counters->daily_total ?><br />
    </div>
</div>