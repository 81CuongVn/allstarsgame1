<?php echo partial('shared/title_battle', array('title' => 'users.beta.title', 'place' => 'users.beta.title')) ?>
<br />
<br />
<?php
	echo partial('shared/info_battle', array(
		'id'		=> 1,
		'title'		=> 'users.beta.messages.m1_title',
		'message'	=> t('users.beta.messages.m1_message')
	));
?>
<?php echo render_file(ROOT . '/views/users/join.php', ['countries' => $countries, 'beta' => true]) ?>
<hr />