<?php echo partial('shared/title', array('title' => 'menus.friend_search', 'place' => 'menus.friend_search')) ?>
<?php if (FW_ENV != 'dev') { ?>
	<!-- AASG - Friends -->
	<ins class="adsbygoogle"
		style="display:inline-block;width:728px;height:90px"
		data-ad-client="ca-pub-6665062829379662"
		data-ad-slot="3870547077"></ins>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
	</script><br />
<?php } ?>
<?php if ($player->level > 9) { ?>
	<form class="form-horizontal" id="f-search-friend">
		<?php
			echo partial('shared/info', [
				'id'		=> 4,
				'title'		=> 'friends.search_title',
				'message'	=> t('friends.search_description')
			]);
		?>
	</form><br />
	<div id="friend-search-results"></div>
<?php } else { ?>
	<?php
		echo partial('shared/info', [
			'id'		=> 4,
			'title'		=> 'friends.search_title',
			'message'	=> t('friends.search_description2')
		]);
	?>
<?php } ?>


