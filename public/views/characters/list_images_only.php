<div id="popup-character-images">
	<?php foreach ($images as $image) { ?>
		<?php if ($image->ultimate) { ?>
			<div class="ultimate">
				<?=$image->profile_image();?>
			</div>
		<?php } else { ?>
			<?=$image->profile_image();?>
		<?php } ?>
	<?php } ?>
	<div class="break"></div>
</div>
