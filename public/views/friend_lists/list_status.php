<div style="width: 730px; position: relative; height: 300px;" class="status">
    <div style="float: left">
		<?php echo $player->profile_image() ?>
        <div align="center" class="nome-personagem"><?php echo $player->name ?></div>
    </div>
    <div class="h-combates">
        <div style="width: 341px; text-align: center; padding-top: 12px"><b class="amarelo" style="font-size:13px">Resumo de Combate</b></div>
        <div style="width: 341px; text-align: center; padding-top: 22px; font-size: 12px !important; line-height: 15px;">
            <span class="verde"><?php echo t('characters.status.wins_npc') ?>:</span> <?php echo $player->wins_npc ?> <br />
            <span class="verde"><?php echo t('characters.status.wins_pvp') ?>:</span> <?php echo $player->wins_pvp ?> <br />
            <span class="vermelho"><?php echo t('characters.status.losses_npc') ?>:</span> <?php echo $player->losses_npc ?> <br />
            <span class="vermelho"><?php echo t('characters.status.losses_pvp') ?>:</span> <?php echo $player->losses_pvp ?> <br />
            <span><?php echo t('characters.status.draws_npc') ?>:</span> <?php echo $player->draws_npc ?> <br />
            <span><?php echo t('characters.status.draws_pvp') ?>:</span> <?php echo $player->draws_pvp ?> 
        </div>
    </div>
    <div class="h-missoes">
        <div style="width: 341px; text-align: center; padding-top: 12px"><b class="amarelo" style="font-size:13px">Missões Completas</b></div>
        <div style="width: 341px; text-align: center; padding-top: 22px; font-size: 12px !important; line-height: 15px;">
            <span class="verde"><?php echo t('characters.status.time') ?>:</span> <?php echo $quest_counters->time_total ?><br />
            <span class="verde"><?php echo t('characters.status.interativas') ?>:</span> 0<br />
            <span class="verde"><?php echo t('characters.status.especiais') ?>:</span> <?php echo $quest_counters->pvp_total ?><br />
            <span class="verde"><?php echo t('characters.status.daily') ?>:</span> <?php echo $quest_counters->daily_total ?><br />
            <span class="verde"><?php echo t('characters.status.pet') ?>:</span> <?php echo $quest_counters->pet_total ?><br />

        </div>
    </div>
</div>        
<!-- DIVISAO -->
<br />
<br />
