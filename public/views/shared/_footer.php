<div id="rodape">
    <div id="rodape-posicao">
        <div id="facebook">
            <div style="width: 385px; height: 70px;">
                <?php if (FW_ENV != 'dev') { ?>
                    <div class="fb-page" data-href="https://www.facebook.com/AllStarsGame" data-tabs="timeline" data-width="385" data-height="70" data-small-header="true" data-adapt-container-width="false" data-hide-cover="true" data-show-facepile="false"></div>
                <?php } ?>
            </div>
        </div>
        <div id="texto-rodape">
            <p>Personagens e desenhos &copy; CopyRight by seus Respectivos criadores. Todos os direitos reservados<br />
                <b>&copy; 2013-<?=date('Y');?> <a href="<?=make_url('/');?>"><?=GAME_NAME;?></a> - Todos os direitos reservados sobre o sistema e gráficos</b>
            </p>
        </div>
        <div id="outros-jogos"></div>
    </div>
</div>
<?php if (FW_ENV != 'dev') { ?>
    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous"
        src="https://connect.facebook.net/pt_BR/sdk.js#xfbml=1
             &version=v10.0&appId=<?=FB_APP_ID;?>&autoLogAppEvents=1"
        nonce="z3ba4zPG">
    </script>
<?php } ?>