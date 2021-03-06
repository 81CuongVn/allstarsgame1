<div id="popup-character-images">
	<?php foreach ($images as $image): ?>
		<?php if ($image->ultimate): ?>
			<div class="ultimate">
				<?php echo $image->profile_image() ?>
			</div>
		<?php else: ?>
				<?php echo $image->profile_image() ?>
		<?php endif ?>
	<?php endforeach ?>
	<div class="break"></div>
</div>