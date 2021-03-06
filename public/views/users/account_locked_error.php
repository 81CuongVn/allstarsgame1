<?php echo partial('shared/title', array('title' => 'users.account_locked.title', 'place' => 'characters.account_locked.title')) ?>
<?php
	echo partial('shared/info', array(
		'id'		=> 3,
		'title'		=> 'users.account_locked.invalid.title',
		'message'	=> t('users.account_locked.invalid.message')
	));
?>