<?php echo partial('shared/title', array('title' => 'techniques.training.wait.title', 'place' => 'techniques.training.wait.title')) ?>
<?php if (FW_ENV != 'dev') { ?>
	<!-- AASG - Treinamento -->
	<ins class="adsbygoogle"
		style="display:inline-block;width:728px;height:90px"
		data-ad-client="ca-pub-6665062829379662"
		data-ad-slot="5650532609"></ins>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
	</script><br />
<?php } ?>
<div class="msg-container" id="technique-training-status-container">
	<div class="msg_top"></div>
	<div class="msg_repete">
<?php if ($finished): ?>
	<div class="msg" style="background:url(<?php echo image_url('msg/'. $player->character()->anime_id . '-2.png')?>); background-repeat: no-repeat;"></div>
	<div class="msgb" style="position:relative; margin-left: 231px; text-align: left; top: -37px">
		<b><?php echo t('techniques.training.wait.title_finished') ?></b>
		<div class="content">
			<?php echo t('techniques.training.wait.info_finished') ?><br /><br />
			<br />
			<br />
			<a class="btn btn-sm btn-primary finish"><?php echo t('techniques.training.wait.finish') ?></a>
		</div>
	</div>
<?php else: ?>
	<div class="msg" style="background:url(<?php echo image_url('msg/'. $player->character()->anime_id . '-2.png')?>); background-repeat: no-repeat;"></div>
	<div class="msgb" style="position:relative; margin-left: 231px; text-align: left; top: -37px">
		<b><?php echo t('techniques.training.wait.title') ?></b>
		<div class="content">
			<?php echo t('techniques.training.wait.info') ?><br /><br />
			<?php echo t('techniques.training.wait.time_left') ?>
			<span id="technique-wait-timer" data-seconds="<?php echo $diff['seconds'] ?>" data-minutes="<?php echo $diff['minutes'] ?>" data-hours="<?php echo $diff['hours'] ?>">--:--:--</span>
			<br />
			<br />
			<a class="btn btn-sm btn-danger cancel" data-confirmation="<?php echo t('techniques.training.wait.cancel_msg') ?>"><?php echo t('techniques.training.wait.cancel') ?></a>
		</div>
	</div>
<?php endif ?>
	</div>
	<div class="msg_bot"></div>
	<div class="msg_bot2"></div>
</div>
