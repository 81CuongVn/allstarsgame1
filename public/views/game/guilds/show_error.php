<?php echo partial('shared/title', array('title' => 'guilds.show.title', 'place' => 'guilds.show.title')) ?>
<?php if (FW_ENV != 'dev') { ?>
	<!-- AASG - Guild -->
	<ins class="adsbygoogle"
		style="display:inline-block;width:728px;height:90px"
		data-ad-client="ca-pub-6665062829379662"
		data-ad-slot="7693601385"></ins>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
	</script><br />
<?php } ?>
<?php echo partial('shared/info', array('id'=> 1, 'title' => 'guilds.show.error_title', 'message' => t('guilds.show.error_message'))) ?>
