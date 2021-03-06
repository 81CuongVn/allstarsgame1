<div id="waiting">
	<?=partial('shared/title', [
		'title' => 'battles.waiting.title',
		'place' => 'battles.waiting.title'
	]);?>
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