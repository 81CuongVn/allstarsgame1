<?php echo partial('shared/title', array('title' => 'notfound.title', 'place' => 'notfound.title')) ?>
<?php if (FW_ENV != 'dev') { ?>
	<!-- AASG - Home -->
	<ins class="adsbygoogle"
		style="display:inline-block;width:728px;height:90px"
		data-ad-client="ca-pub-6665062829379662"
		data-ad-slot="4041296834"></ins>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
	</script><br />
<?php } ?>
<?php echo partial('shared/info', array('id'=> 1, 'title' => 'notfound.title_msg', 'message' => t('notfound.message'))) ?>
