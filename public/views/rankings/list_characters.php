<input type="hidden" id="h_character_id2" value="" />

<b><?php echo t('menu_categories.character') ?></b><br />
<select name="character_id" class="form-control" id="character_id" style="width: 121px">
	<option value="0"><?php echo t('global.all') ?></option>
	<?php foreach ($characters as $character): ?>
		<option value="<?php echo $character->id ?>"><?php echo $character->description()->name ?></option>
	<?php endforeach ?>
</select>