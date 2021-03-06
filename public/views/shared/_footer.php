<div id="rodape">
    <div id="rodape-posicao">
        <div id="facebook">
            <div style="width: 385px; height: 70px;">
                <?php if (FW_ENV != 'development') { ?>
                    <div class="fb-page" data-href="https://www.facebook.com/AnimeAllStars" data-tabs="" data-width="385" data-height="70" data-small-header="true" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="false"></div>
                <?php } ?>
            </div>
        </div>
        <div id="texto-rodape">
            <p>Personagens e desenhos &copy; CopyRight by seus Respectivos criadores. Todos os direitos reservados<br />
                <b>&copy; 2013-<?=date('Y');?> <?=GAME_NAME;?> - Todos os direitos reservados sobre o sistema e gráficos</b>
            </p>
        </div>
        <div id="outros-jogos"></div>
    </div>
</div>
<?php if (FW_ENV != 'development') { ?>
    <div id="fb-root"></div>
    <script type="text/javascript" src="https://connect.facebook.net/pt_BR/sdk.js#xfbml=1&version=v6.0"></script>
<?php } ?>