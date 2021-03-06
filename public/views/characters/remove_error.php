<?php echo partial('shared/title', array('title' => 'characters.remove.title', 'place' => 'characters.remove.title')) ?>
<?php
	echo partial('shared/info', array(
		'id'		=> 3,
		'title'		=> 'characters.remove.error_title',
		'message'	=> $messages
	));
?>