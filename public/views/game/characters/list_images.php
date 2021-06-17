<div id="popup-character-images">
	<?php foreach ($images as $image) { ?>
		<?php if ($image->ultimate) { ?>
			<div class="ultimate">
				<?=$image->profile_image();?>
				<?php if (!$image->is_buyable || $user->character_theme_image($image->id)) { ?>
					<a class="ultimate-image btn btn-sm btn-primary" style="margin-left: 10px;" data-id="<?=$image->id;?>">Escolher essa Imagem</a>
				<?php } else {?>
					<a class="ultimate-image btn btn-sm btn-warning" style="margin-left: 10px;" data-id="<?=$image->id;?>">Comprar por <?=highamount($image->price_credits);?> Estrela(s)</a>
				<?php } ?>
			</div>
		<?php } else { ?>
			<div class="image2">
				<a class="image" data-id="<?=$image->id;?>">
					<?=$image->profile_image();?>
				</a>
				<?php if ($image->is_buyable) { ?>
					<div style="position:relative; top: -30px; text-align: center; font-size:14px" class="laranja">Comprar por <?=highamount($image->price_credits);?> Estrela(s)</div>
				<?php } ?>
			</div>
		<?php } ?>
	<?php } ?>
	<div class="break"></div>
</div>
