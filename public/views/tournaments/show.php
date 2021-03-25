<?php echo partial('shared/title', [
    'title' => 'tournaments.title',
    'place' => 'tournaments.title'
]); ?>
<div id="tournament">
    <?php
    extract($tournament->has_requirement($player));
    echo partial('shared/info', array(
        'id'		=> 5,
        'title'		=> 'tournaments.info.title2',
        'message'	=> t('tournaments.info.description2')
    )); ?><br />
    <div style="width: 730px; height: 185px; position: relative; left: 24px">
        <div class="h-missoes">
            <div style="width: 341px; text-align: center; padding-top: 12px">
                <b class="amarelo" style="font-size:13px"><?php echo $tournament->name; ?></b>
            </div>
            <div style="width: 341px; padding: 20px;">
                <span class="laranja">Inicio das Inscrições:</span> <span class="pull-right"><?php echo date('d/m/Y à\s H:i:s', strtotime($tournament->subscribe_starts_at)); ?></span><br />
                <span class="laranja">Fim das Inscrições:</span> <span class="pull-right"><?php echo date('d/m/Y à\s H:i:s', strtotime($tournament->subscribe_ends_at)); ?></span><br /><br />
                <span class="vermelho">Inicio das Batalhas:</span> <span class="pull-right"><?php echo date('d/m/Y à\s H:i:s', strtotime($tournament->starts_at)); ?></span><br />

                <span class="verde">Participantes:</span> <span class="pull-right"><?php echo sizeof($tournament->players()); ?> / <?php echo $tournament->places; ?></span><br />
                <span class="verde">Vencedor:</span> <span class="pull-right"><?php echo ($tournament->finished && $tournament->winner ? $tournament->winner : '-'); ?></span>
            </div>
        </div>
        <div class="h-missoes">
            <div style="width: 341px; text-align: center; padding-top: 12px">
                <b class="amarelo" style="font-size:13px">Inscrição</b>
            </div>
            <div style="width: 341px; padding: 15px;">
                <?php if ($subscribed) { ?>
                    <?php if ($can_action) { ?>
                        <button type="button" class="btn btn-danger btn-block unsubscribe" data-tournament="<?php echo $tournament->id; ?>">
                            Cancelar Inscrição
                        </button>
                    <?php } else { ?>
                        <button type="button" class="btn btn-danger btn-block btn-disabled" disabled>
                            Cancelar Inscrição
                        </button>
                    <?php } ?>
                <?php } else { ?>
                    <div class="row ">
                        <div class="col-sm-6 text-center vcenter" style="width: 49%;">
                            <img src="<?php echo image_url('requer.png') ?>" class="requirement-popover" data-source="#requirement-content-<?php echo $tournament->id ?>" data-title="<?php echo t('popovers.titles.requirements') ?>" data-trigger="hover" data-placement="left" />
                            <div class="requirement-container" id="requirement-content-<?php echo $tournament->id ?>"><?php echo $requirement_log ?></div>
                        </div>
                        <div class="col-sm-6 text-center vcenter" style="width: 49%;">
                            <?php if ($can_action) { ?>
                                <button type="button" class="btn btn-sm btn-primary btn-block subscribe" data-tournament="<?php echo $tournament->id; ?>" data-method="currency">
                                    Fazer Inscrição<br />
                                    <?php echo highamount($tournament->price_currency); ?> <?php echo t('currencies.' . $player->character()->anime_id); ?>
                                </button>
                                <button type="button" class="btn btn-sm btn-warning btn-block subscribe" data-tournament="<?php echo $tournament->id; ?>" data-method="credits">
                                    Fazer Inscrição<br />
                                    <?php echo highamount($tournament->price_credits); ?> <?php echo t('currencies.credits'); ?>
                                </button>
                            <?php } else { ?>
                                <button type="button" class="btn btn-sm btn-primary btn-block btn-disabled" disabled>
                                    Fazer Inscrição<br />
                                    <?php echo highamount($tournament->price_currency); ?> <?php echo t('currencies.' . $player->character()->anime_id); ?>
                                </button>
                                <button type="button" class="btn btn-sm btn-warning btn-block btn-disabled" disabled>
                                    Fazer Inscrição<br />
                                    <?php echo highamount($tournament->price_credits); ?> <?php echo t('currencies.credits'); ?>
                                </button>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="break"></div>
    </div>
    <?php if ($tournament->round > 0) { ?>
        <div class="titulo-home3">
            <p><?php echo t('tournaments.view.bracket'); ?></p>
        </div>
        <style>
            div.jQBracket {
                margin: 0 auto;
            }
            div.jQBracket .team div.label {
                color: black
            }
        </style>
        <script type="text/javascript">
            $(function() {
                $('.tournament-bracket').bracket({
                    skipConsolationRound: <?php echo (!$tournament->consolation_round ? 'true': 'false'); ?>,
                    teamWidth: <?php echo $tournament->teamWidth(); ?>,
                    scoreWidth: 20,
                    matchMargin: 5,
                    roundMargin: <?php echo $tournament->roundMargin(); ?>,
                    init: {
                        teams: [
                            <?php foreach ($tournament->bracket() as $match) { ?>

                                ["<?php echo $match->player()->name; ?>", "<?php echo $match->enemy()->name; ?>"],
                            <?php } ?>

                        ],
                        results: [
                            <?php
                            for ($i = 1; $i <= $tournament->total_rounds(); $i++) {
                                $result = $tournament->results($i);
                                if ($result) {
                                    echo '[ ' . $result . ' ],';
                                }
                            }
                            ?>

                            // [ [1, 0], [10, 5] ]
                            // [ [1,0], [0,1], [1,0], [0,1], [1,0], [0,1], [1,0], [0,1] , [0,1], [1,0], [0,1], [1,0], [0,1], [1,0], [0,1], [1,0], [0,1] , [0,1] ],
                            // [ [1,0], [0,1], [1,0], [0,1], [1,0], [0,1], [1,0], [0,1] ],
                            // [ [1,0], [0,1], [1,0], [0,1] ],
                            // [ [1,0], [0,1] ],
                            // [ [1,0] ]
                        ]
                    }
                })
            })
        </script>
        <div class="tournament-bracket" style="margin: 0 10px 10px 15px;"></div>
        <div class="break"></div><br />
    <?php } else { ?>
        <div class="titulo-home3">
            <p><?php echo t('tournaments.view.players'); ?></p>
        </div>
        <?php foreach ($tournament->players() as $p) { ?>
            <?php
            $subscribe  = $p->created_at;
            $p          = $p->player();
            ?>
            <div class="ability-speciality-box" style="height: auto;">
                <div class="image text-center">
                    <?=$p->character_theme()->first_image()->small_image();?>
                </div>
                <div class="name" style="height: auto;">
                    <div class="amarelo">
                        <?php if (is_player_online($p->id)): ?>
                            <img src="<?php echo image_url("on.png" ) ?>"/>
                        <?php else: ?>
                            <img src="<?php echo image_url("off.png" ) ?>"/>
                        <?php endif ?>
                        <b><?=$p->name;?></b>
                    </div>
                </div>
                <div class="description" style="height: auto;">
                    <span style="font-size: 12px">
                        <?=$p->character()->anime()->description()->name;?><br />
                        <?=$p->graduation()->description($p->character()->anime()->id)->name;?>
                    </span><br /><br />
                    Nível <?=highamount($p->level);?>
                </div>
                <div class="details" style="padding: 10px;">
                    <img src="<?=image_url($p->faction_id . ".png");?>" width="25" /><br />
                </div>
                <div class="button">Inscrito em<br />
                    <b class="laranja"><?php echo date('d/m/Y à\s H:i:s', strtotime($subscribe)); ?></b>
                </div>
            </div>
        <?php } ?>
        <div class="break"></div>
    <?php } ?>
</div>