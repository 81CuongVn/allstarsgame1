<?=partial('shared/title', [
	'title' => 'battles.training.title',
	'place' => 'battles.training.title'
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

<?=partial('shared/battle_menu', [ 'player' => $player ]);?>

<form class="form-horizontal" id="room-search-friend">
	<?=partial('shared/info', [
			'id'		=> 1,
			'title'		=> 'battles.training.m1_title',
			'message'	=> t('battles.training.m1_description')
	]);?>
</form><br />
<script type="text/javascript">
	$(document).ready(function () {
		var results = $('#room-search-results');
		setInterval(function () {
			$.ajax({
				url:		make_url('battle_pvps#room_list'),
				data:		$(this).serialize(),
				success:	function (result) {
					if (result) {
						lock_screen(false);
						results.html(result);
					}
				}
			});
		}, 3000);
	});
</script>
<div id="room-search-results"></div>
