<?php echo partial('shared/title', array('title' => 'hospital.title', 'place' => 'hospital.title')) ?>
<!-- AASG - Hospital -->
<ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-6665062829379662"
     data-ad-slot="8831778454"></ins>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({});
</script><br />
<?php
	echo partial('shared/info', array(
		'id'		=> 1,
		'title'		=> 'hospital.title2',
		'message'	=> t('hospital.description', ['mana' => t('formula.for_mana.' . $player->character_theme()->anime()->id)])
	));
?>
<br />
<div align="center">
	<a href="javascript:;" id="hospital-heal-button" class="btn btn-primary btn-lg"><?php echo t('hospital.heal_button', ['currency' => $currency, 'value' => highamount($cost)]) ?></a>
</div>
