<?php echo partial('shared/title', array('title' => 'characters.next_level.title', 'place' => 'characters.next_level.title')) ?>
<?php
	echo partial('shared/info', array(
		'id'		=> 3,
		'title'		=> 'characters.next_level.message_title',
		'message'	=> t('characters.next_level.message')
	));
?>
<div align="center">
	<form method="post">
		<input type="hidden" name="key" value="<?php echo uniqid() ?>">
		<input type="submit" value="<?php echo t('characters.next_level.next') ?>" class="btn btn-primary btn-lg" />
	</form>
</div>
