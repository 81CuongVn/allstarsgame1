<input type="hidden" id="h_character_id2" value="" />
<select name="character_id" class="form-control input-sm select2" id="character_id" style="width: 121px">
	<option value="0"><?php echo t('global.all') ?></option>
	<?php foreach ($characters as $character): ?>
		<option value="<?php echo $character->id ?>"><?php echo $character->description()->name ?></option>
	<?php endforeach ?>
</select>
<script type="text/javascript">
	$(document).ready(function() {
		$('.select2').select2({
			theme: "bootstrap",
		});
	});
</script>