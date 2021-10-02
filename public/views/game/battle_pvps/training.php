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
<div class="text-right">
	<button type="button" class="btn btn-primary btn-sm mr-2 mb-2" id="refresh-rooms">
		<i class="fa fa-refresh fa-fw"></i>
		Atualizar
	</button>
</div>
<div id="room-search-results">
	<p class="text-center">Estamos buscando salas de treinamento, aguarde...</p>
</div>
