<div id="chat" data-register="<?=$player->chatRegister();?>" <?php if ($_SESSION['universal']) { ?>data-universal="true"<?php } ?>>
	<div class="title">
        Chat All-Stars
	</div>
	<ul class="messages">
		<li class="wait">Conectando...</li>
	</ul>
    <div class="selector">
        <ul>
            <li data-channel="world" data-cmd="w">Mundo</li>
            <li data-channel="faction" data-cmd="f"><?=$player->faction()->description()->name;?></li>
            <?php if ($player->guild_id) { ?>
                <li data-channel="guild" data-cmd="g">Organização</li>
            <?php } ?>
            <?php if ($player->battle_pvp_id) { ?>
                <li data-channel="battle" data-cmd="b">Batalha</li>
            <?php } ?>
            <?php if ($_SESSION['universal']) { ?>
                <li data-channel="system" data-cmd="s">Sistema</li>
            <?php } ?>
        </ul>
        <div class="selector-trigger">Mundo</div>
        <input type="text" id="message" autocomplete="off" name="message" <?php if (!$_SESSION['universal']) { ?>maxlength="60"<?php } ?> />
        <input type="checkbox" id="as" checked="checked" class="auto-scroll" />
    </div>
</div>
<script type="text/javascript" src="<?=asset_url('js/chat.js');?>"></script>