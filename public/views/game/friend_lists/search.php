<?php echo partial('shared/title', array('title' => 'menus.friend_search', 'place' => 'menus.friend_search')) ?>
<?php if($player->level > 9){?>
	<form class="form-horizontal" id="f-search-friend">
	<?php
		echo partial('shared/info', [
			'id'		=> 4,
			'title'		=> 'friends.search_title',
			'message'	=> t('friends.search_description')
		]);
	?>
	</form>
	<br />
	<div id="friend-search-results"></div>
<?php }else{?>
	<?php
		echo partial('shared/info', [
			'id'		=> 4,
			'title'		=> 'friends.search_title',
			'message'	=> t('friends.search_description2')
		]);
	?>
<?php }?>

	
