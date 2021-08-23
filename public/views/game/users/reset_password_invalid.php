<?php echo partial('shared/title', array('title' => 'users.password_reset.title', 'place' => 'users.password_reset.title')) ?>
<!-- AASG - Users -->
<ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-6665062829379662"
     data-ad-slot="3196308392"></ins>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({});
</script><br />
<?php
	echo partial('shared/info', array(
		'id'		=> 3,
		'title'		=> 'users.password_reset.invalid.title',
		'message'	=> t('users.password_reset.invalid.message')
	));
?>
