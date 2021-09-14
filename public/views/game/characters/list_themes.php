<div id="popup-character-themes">
	<div id="theme-list" class="card-container">
		<?php foreach ($themes as $theme) { ?>
			<div style="float: left; height: 250px">
				<div class="barra-secao barra-secao-1">
					<p><?=$theme->description()->name;?></p>
				</div>
				<div class="card theme" data-theme="<?=$theme->id;?>" data-buyable="<?=$theme->is_buyable;?>" data-credits="<?=$theme->price_credits;?>" data-currency="<?=$theme->price_currency;?>" data-toggle="tooltip" title="<?=$theme->description()->name;?>">
					<?=$theme->small_image();?><br /><br />
					<?php if ($player) { ?>
						<?php if ($user->is_theme_bought($theme->id) || $theme->is_default) { ?>
							<?php if ($theme->id == $player->character_theme_id) { ?>
								<a href="javascript:;" class="btn btn-sm btn-warning disabled" data-theme="<?=$theme->id;?>"><?=t('characters.themes.use_this');?></a>
							<?php } else { ?>
								<a href="javascript:;" class="btn btn-sm btn-success use-theme" data-theme="<?=$theme->id;?>"><?=t('characters.themes.use_this');?></a>
							<?php } ?>
						<?php } ?>
					<?php } ?>
				</div>
				<div class="attack-list attack-list-<?=$theme->id;?>">
					<?php foreach ($theme->attacks(true) as $attack) { ?>
						<img src="<?=image_url($attack->image(true));?>" class="aasg-popover item-image" data-source="#technique-content-<?=$attack->description()->character_theme_id;?>-<?=$attack->id;?>" data-title="<?=$attack->description()->name;?>" data-trigger="hover" data-placement="bottom" />
						<div class="technique-container" id="technique-content-<?=$attack->description()->character_theme_id;?>-<?=$attack->id;?>">
							<?=$attack->technique_tooltip();?>
						</div>
					<?php } ?>
				</div>
				<div class="theme-controls theme-controls-<?=$theme->id;?>">
				<?php if ($player) { ?>
					<?php if ($theme->is_buyable) { ?>
						<?php if (!$user->is_theme_bought($theme->id)) { ?>
							<?php if ($theme->price_credits || $theme->price_currency) { ?>
								<?php if ($theme->price_credits) { ?>
									<?php if ($user->credits >= $theme->price_credits) { ?>
										<a href="javascript:void(0);" class="btn btn-sm btn-warning buy-theme" data-mode="1" data-theme="<?=$theme->id;?>">
											<?=t('characters.themes.buy_credits', [
												'price' => highamount($theme->price_credits)
											]);?>
										</a>
									<?php } else { ?>
										<a href="javascript:void(0);" class="btn btn-sm btn-warning disabled">
											<?=t('characters.themes.buy_credits', [
												'price' => highamount($theme->price_credits)
											]);?>
										</a>
									<?php } ?>
								<?php } ?>
								<?php if ($theme->price_currency) { ?>
									<?php if ($player->currency >= $theme->price_currency) { ?>
										<a href="javascript:void(0);" class="btn btn-sm btn-warning buy-theme" data-mode="2" data-theme="<?=$theme->id;?>">
											<?=t('characters.themes.buy_currency', [
												'price'		=> highamount($theme->price_currency),
												'currency'	=> t('currencies.' . $player->character()->anime_id)
											]);?>
										</a>
									<?php } else { ?>
										<a href="javascript:void(0);" class="btn btn-sm btn-warning disabled">
											<?=t('characters.themes.buy_currency', [
												'price'		=> highamount($theme->price_currency),
												'currency'	=> t('currencies.' . $player->character()->anime_id)
											]);?>
										</a>
									<?php } ?>
								<?php } ?>
							<?php } else { ?>
								<a href="javascript:;" class="btn btn-sm btn-warning buy-theme" data-theme="<?=$theme->id;?>"><?=t('characters.themes.buy_free');?></a>
							<?php } ?>
						<?php } else { ?>
							<a href="javascript:;" class="btn btn-sm btn-success disabled"><?=t('characters.themes.already_bought') ?></a>
						<?php } ?>
					<?php } else { ?>
						<?php if (!$theme->is_default && $theme->reward_lock && !$theme->map_lock) { ?>
							<?php $subgroup = HistoryModeSubgroup::find_first('reward_character_theme_id=' . $theme->id)->description()->name; ?>
							<a href="javascript:;" class="btn btn-sm btn-warning disabled"><?=t('characters.themes.liberado');?></a>
						<?php } elseif (!$theme->is_default && !$theme->reward_lock && $theme->map_lock) { ?>
							<a href="javascript:;" class="btn btn-sm btn-warning disabled"><?=t('characters.themes.liberado2');?></a>
						<?php } ?>
					<?php } ?>
				<?php } ?>
			</div>
		</div>
		<?php } ?>
	</div>
</div>
