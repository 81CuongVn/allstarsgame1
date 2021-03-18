<div id="popup-character-images">
	<?php foreach ($images as $image): ?>
		<?php if ($image->ultimate): ?>
			<div class="ultimate">
				<?php echo $image->profile_image() ?>
				<?php if(!$image->is_buyable || $user->character_theme_image($image->id)){?>
					<a class="ultimate-image btn btn-sm btn-primary" data-id="<?php echo $image->id ?>">&nbsp;&nbsp;Escolher essa Imagem&nbsp;&nbsp;</a>
				<?php }else{?>
					<a class="ultimate-image btn btn-sm btn-warning" data-id="<?php echo $image->id ?>">Comprar por <?php echo highamount($image->price_credits); ?> Estrela(s)</a>
				<?php }?>	
			</div>
		<?php else: ?>
			<div class="image2">
				<a class="image" data-id="<?php echo $image->id ?>">
					<?php echo $image->profile_image() ?>
				</a>
				<?php if($image->is_buyable){?>
				<div style="position:relative; top: -30px; text-align: center; font-size:14px" class="laranja">Comprar por <?php echo highamount($image->price_credits); ?> Estrela(s)</div>
				<?php }?>
			</div>			
		<?php endif ?>
	<?php endforeach ?>
	<div class="break"></div>
</div>