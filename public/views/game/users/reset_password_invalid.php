<?php echo partial('shared/title', array('title' => 'users.password_reset.title', 'place' => 'users.password_reset.title')) ?>
<?php
	echo partial('shared/info', array(
		'id'		=> 3,
		'title'		=> 'users.password_reset.invalid.title',
		'message'	=> t('users.password_reset.invalid.message')
	));
?>