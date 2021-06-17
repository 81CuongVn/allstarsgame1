<?php
	$instance	= Player::get_instance();

	if(!$instance) {
        $anime	= Anime::find_first('active=1 ORDER BY RAND() LIMIT 1')->id;
	} else {
		$anime	= $instance->character()->anime_id;
	}
?>
<div class="msg-container battle">
	<div class="msg_top"></div>
	 <div class="msg_repete">
		<div class="msg" style="background:url(<?php echo image_url('msg/'. $anime . '-' . $id .'.png')?>); background-repeat: no-repeat;">
		</div>
		<div class="msgb" style="position:relative; margin-left: 231px; text-align: left; top: -37px">
			<b><?php echo t($title) ?></b>
			<div class="content"><?php echo $message ?></div>
		</div>		
		
	</div>
	<div class="msg_bot"></div>	
	<div class="msg_bot2"></div>
</div>
