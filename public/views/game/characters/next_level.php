<?=partial('shared/title', [
	'title'	=> 'characters.next_level.title',
	'place'	=> 'characters.next_level.title'
]);?>
<?=partial('shared/info', [
	'id'		=> 3,
	'title'		=> 'characters.next_level.message_title',
	'message'	=> t('characters.next_level.message')
]);?>
<div align="center">
	<form method="post">
		<input type="hidden" name="key" value="<?=uniqid();?>">
		<input type="submit" value="<?=t('characters.next_level.next');?>" class="btn btn-primary btn-lg" />
	</form>
</div>
