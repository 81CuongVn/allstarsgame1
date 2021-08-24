<div id="waiting">
	<?=partial('shared/title', [
		'title' => 'battles.waiting.title',
		'place' => 'battles.waiting.title'
	]);?>
	<?php if (FW_ENV != 'dev') { ?>
		<!-- AASG - Batalhas -->
		<ins class="adsbygoogle"
			style="display:inline-block;width:728px;height:90px"
			data-ad-client="ca-pub-6665062829379662"
			data-ad-slot="5606300570"></ins>
		<script>
			(adsbygoogle = window.adsbygoogle || []).push({});
		</script><br />
	<?php } ?>
	<?=partial('shared/info', [
		'id'		=> 2,
		'title'		=> 'battles.waiting.title',
		'message'	=> t('battles.waiting.description')
	]);?>
</div>
<script type="text/javascript">
	$(document).ready(function () {
		setInterval(function () {
			$.ajax({
				url:		make_url('battle_pvps#waiting_queue'),
				dataType:	'json',
				success:	function (result) {
					if (result.redirect) {
						location.href	= result.redirect;
					}
				}
			});
		}, 3000);
	});
</script>
