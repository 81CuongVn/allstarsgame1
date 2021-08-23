<?php echo partial('shared/title', array('title' => 'characters.remove.title', 'place' => 'characters.remove.title')) ?>
<!-- AASG - Personagem -->
<ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-6665062829379662"
     data-ad-slot="7609647387"></ins>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({});
</script><br />
<?php
	echo partial('shared/info', array(
		'id'		=> 3,
		'title'		=> 'characters.remove.error_title',
		'message'	=> $messages
	));
?>
