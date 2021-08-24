<?php echo partial('shared/title', array('title' => 'history_mode.index.title', 'place' => 'history_mode.index.title')) ?>
<?php if (FW_ENV != 'dev') { ?>
	<!-- AASG - Modo Aventura -->
	<ins class="adsbygoogle"
		style="display:inline-block;width:728px;height:90px"
		data-ad-client="ca-pub-6665062829379662"
		data-ad-slot="8659839325"></ins>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
	</script><br />
<?php } ?>
<?php echo partial('shared/info', array('id'=> 1, 'title' => 'history_mode.show.error_title', 'message' => t('history_mode.show.denied'))) ?>
