<?=partial('shared/title', [
	'title'	=> 'legal.privacy.title',
	'place'	=> 'legal.privacy.title'
]);?>
<?php if (FW_ENV != 'dev') { ?>
	<!-- AASG - Legal -->
	<ins class="adsbygoogle"
		style="display:inline-block;width:728px;height:90px"
		data-ad-client="ca-pub-6665062829379662"
		data-ad-slot="7518696782"></ins>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
	</script><br />
<?php } ?>
<?=t('legal.privacy.text', [
	'game'	=> GAME_NAME
]);?>
