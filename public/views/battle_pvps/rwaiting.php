<div id="ranked_waiting">
<?php echo partial('shared/title', array('title' => 'ranked.liga', 'place' => 'ranked.liga')) ?>
<?php
	echo partial('shared/info', array(
		'id'		=> 2,
		'title'		=> 'battles.waiting.title',
		'message'	=> t('battles.waiting.description2')
	));
?>
</div>
<script>
$(document).ready(function () {
	setInterval(function () {
		$.ajax({
			url: make_url('battle_pvps#rwaiting_queue'),
			dataType:	'json',
			success:	function (result) {
				if(result.redirect) {
					location.href	= result.redirect;
				}
			}
		});
	}, 3000);
});
</script>