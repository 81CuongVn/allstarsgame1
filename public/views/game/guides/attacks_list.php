<?php foreach ($themes as $theme): ?>
	<div style="float: left; width: 495px;">
		<div class="titulo-home2"><p>Habilidades</p></div>
		<div class="ability-list ability-list-<?php echo $theme->id ?>" style="width:495px; text-align: center; margin: auto; padding: 10px;">
			<div class="attack">
				<?php foreach ($abilities as $ability): ?>
					<div class="technique-popover" data-source="#ability-container-<?php echo $ability->id ?>" data-title="<?php echo $ability->description()->name ?>" data-trigger="click" data-placement="bottom" style="display:inline-block"><?php echo $ability->image() ?></div>
					<div id="ability-container-<?php echo $ability->id ?>" class="technique-container">
						<?php echo $ability->tooltip() ?>
					</div>
				<?php endforeach ?>
			</div>
		</div>
	</div>
	<div style="float: left; width: 495px;">
		<div class="titulo-home2"><p>Especialidades</p></div>
		<div class="specialities-list specialities-list-<?php echo $theme->id ?>" style="width:495px; text-align: center; margin: auto; padding: 10px;">
			<div class="attack">
				<?php foreach ($specialities as $speciality): ?>
					<div class="technique-popover" data-source="#speciality-container-<?php echo $speciality->id ?>" data-title="<?php echo $speciality->description()->name ?>" data-trigger="click" data-placement="bottom" style="display:inline-block"><?php echo $speciality->image() ?></div>
					<div id="speciality-container-<?php echo $speciality->id ?>" class="technique-container">
						<?php echo $speciality->tooltip() ?>
					</div>
				<?php endforeach ?>
			</div>
		</div>
	</div>
	<div style="float: left; width: 495px;">
		<div class="titulo-home2"><p>Golpes Únicos</p></div>
		<div class="attack-list attack-list-<?php echo $theme->id ?>" style="width:350px; text-align: center; margin: auto; padding: 10px;">
			<?php foreach ($theme->attacks(true) as $attack): ?>
				<img src="<?php echo image_url($attack->image(true)) ?>" class="technique-popover item-image" data-source="#technique-content-<?php echo $attack->id ?>" data-title="<?php echo $attack->description()->name ?>" data-trigger="hover" data-placement="bottom" />
				<div class="technique-container" id="technique-content-<?php echo $attack->id ?>">
					<?php echo $attack->technique_tooltip() ?>
				</div>
			<?php endforeach ?>
		</div>
	</div>
<?php endforeach ?>
<div align="center">
<?php if($player): ?>
	<?php if ($theme->is_buyable): ?>
			<?php if (!$user->is_theme_bought($theme->id)): ?>
				<?php if ($theme->price_credits || $theme->price_currency): ?>
					<?php if ($theme->price_credits): ?>
						<?php if ($user->credits >= $theme->price_credits): ?>
							<a href="javascript:;" class="btn btn-sm btn-warning buy-theme" data-mode="1" data-theme="<?php echo $theme->id ?>"><?php echo t('characters.themes.buy_credits', array('price' => $theme->price_credits)) ?></a>
						<?php else: ?>
							<a href="javascript:;" class="btn btn-sm btn-warning disabled"><?php echo t('characters.themes.buy_credits', array('price' => highamount($theme->price_credits))) ?></a>
						<?php endif ?>
					<?php endif; ?>
					<?php if ($theme->price_currency): ?>
						<?php if ($player->currency >= $theme->price_currency): ?>
							<a href="javascript:;" class="btn btn-sm btn-warning buy-theme" data-mode="2" data-theme="<?php echo $theme->id ?>">
								<?php echo t('characters.themes.buy_currency', array('price' => highamount($theme->price_currency), 'currency' => t('currencies.' . $player->character()->anime_id))) ?>
							</a>
						<?php else: ?>
							<a href="javascript:;" class="btn btn-sm btn-warning disabled">
								<?php echo t('characters.themes.buy_currency', array('price' => highamount($theme->price_currency), 'currency' => t('currencies.' . $player->character()->anime_id))) ?>
							</a>
						<?php endif ?>
					<?php endif ?>
				<?php else: ?>
					<a href="javascript:;" class="btn btn-sm btn-warning buy-theme" data-theme="<?php echo $theme->id ?>"><?php echo t('characters.themes.buy_free') ?></a>
				<?php endif ?>
			<?php else: ?>
				<a href="javascript:;" class="btn btn-sm btn-warning disabled"><?php echo t('characters.themes.already_bought') ?></a>
			<?php endif ?>
	<?php else: ?>
		<?php if (!$theme->is_default && $theme->reward_lock && !$theme->map_lock){
			$subgroup = HistoryModeSubgroup::find_first('reward_character_theme_id='. $theme->id)->description()->name;
		?>
			<a href="javascript:;" class="btn btn-sm btn-warning disabled"><?php echo t('characters.themes.liberado') ?></a>
		<?php }elseif(!$theme->is_default && !$theme->reward_lock && $theme->map_lock){?>
			<a href="javascript:;" class="btn btn-sm btn-warning disabled"><?php echo t('characters.themes.liberado2') ?></a>
		<?php } ?>
	<?php endif ?>
<?php endif ?>
</div>
