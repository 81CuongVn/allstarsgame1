<script>
$('.technique-popover, .requirement-popover, .shop-item-popover').each(function () {
		$(this).popover({
			trigger:	'manual',
			content:	function () {
				return $($(this).data('source')).html();
			},
			html:		true
		}).on("mouseenter", function () {
		    var _this = this;
		    $(this).popover("show");
		    $(this).siblings(".popover").on("mouseleave", function () {
		        $(_this).popover('hide');
		    });
		}).on("mouseleave", function () {
		    var _this = this;
		    setTimeout(function () {
		        if (!$(".popover:hover").length) {
		            $(_this).popover("hide")
		        }
		    }, 100);
		});
	});
</script>
<div id="popup-character-themes">
	<div id="theme-list" class="card-container">
		<?php foreach ($themes as $theme): ?>
			<div style="float: left; height: 250px">
				<div class="barra-secao barra-secao-1"><p><?php echo $theme->description()->name ?></p></div>	
				<div class="card theme"
					data-theme="<?php echo $theme->id ?>"
					data-buyable="<?php echo $theme->is_buyable ?>"
					data-credits="<?php echo $theme->price_credits ?>"
					data-currency="<?php echo $theme->price_currency ?>"
					data-toggle="tooltip"
					title="<?php echo $theme->description()->name ?><br /><?php echo t('characters.themes.theme_info_click') ?>">
					<?php echo $theme->small_image() ?><br /><br />
					<?php if ($player): ?>
						<?php if ($user->is_theme_bought($theme->id) || $theme->is_default): ?>
							<?php if ($theme->id == $player->character_theme_id): ?>
								<a href="javascript:;" class="btn btn-warning disabled" data-theme="<?php echo $theme->id ?>"><?php echo t('characters.themes.use_this') ?></a>
							<?php else: ?>
								<a href="javascript:;" class="btn btn-success use-theme" data-theme="<?php echo $theme->id ?>"><?php echo t('characters.themes.use_this') ?></a>
							<?php endif ?>
						<?php endif ?>
					<?php endif ?>
					
				</div>
				<div class="attack-list attack-list-<?php echo $theme->id ?>">
					<?php foreach ($theme->attacks(true) as $attack): ?>
						<img src="<?php echo image_url($attack->image(true)) ?>" class="technique-popover item-image" data-source="#technique-content-<?php echo $attack->description()->character_theme_id?>-<?php echo $attack->id ?>" data-title="<?php echo $attack->description()->name?>" data-trigger="hover" data-placement="bottom" />
						<div class="technique-container" id="technique-content-<?php echo $attack->description()->character_theme_id?>-<?php echo $attack->id ?>">
							<?php echo $attack->technique_tooltip() ?>
						</div>
					<?php endforeach ?>	
				</div>
				<div class="theme-controls theme-controls-<?php echo $theme->id ?>">
				<?php if($player): ?>
					<?php if ($theme->is_buyable): ?>
							<?php if (!$user->is_theme_bought($theme->id)): ?>
								<?php if ($theme->price_credits || $theme->price_currency): ?>
									<?php if ($theme->price_credits): ?>
										<?php if ($user->credits >= $theme->price_credits): ?>
											<a href="javascript:;" class="btn btn-warning buy-theme" data-mode="1" data-theme="<?php echo $theme->id ?>"><?php echo t('characters.themes.buy_credits', array('price' => highamount($theme->price_credits))) ?></a>
										<?php else: ?>
											<a href="javascript:;" class="btn btn-warning disabled"><?php echo t('characters.themes.buy_credits', array('price' => highamount($theme->price_credits))) ?></a>
										<?php endif ?>
									<?php endif; ?>
									<?php if ($theme->price_currency): ?>
										<?php if ($player->currency >= $theme->price_currency): ?>
											<a href="javascript:;" class="btn btn-warning buy-theme" data-mode="2" data-theme="<?php echo $theme->id ?>">
												<?php echo t('characters.themes.buy_currency', array('price' => highamount($theme->price_currency), 'currency' => t('currencies.' . $player->character()->anime_id))) ?>
											</a>
										<?php else: ?>
											<a href="javascript:;" class="btn btn-warning disabled">
												<?php echo t('characters.themes.buy_currency', array('price' => highamount($theme->price_currency), 'currency' => t('currencies.' . $player->character()->anime_id))) ?>
											</a>
										<?php endif ?>
									<?php endif ?>
								<?php else: ?>
									<a href="javascript:;" class="btn btn-warning buy-theme" data-theme="<?php echo $theme->id ?>"><?php echo t('characters.themes.buy_free') ?></a>
								<?php endif ?>
							<?php else: ?>
								<a href="javascript:;" class="btn btn-success disabled"><?php echo t('characters.themes.already_bought') ?></a>
							<?php endif ?>
					<?php else: ?>
						<?php if (!$theme->is_default && $theme->reward_lock && !$theme->map_lock){ 
							$subgroup = HistoryModeSubgroup::find_first('reward_character_theme_id='. $theme->id)->description()->name;
						?>
							<a href="javascript:;" class="btn btn-warning disabled"><?php echo t('characters.themes.liberado') ?></a>
						<?php }elseif(!$theme->is_default && !$theme->reward_lock && $theme->map_lock){?>				
							<a href="javascript:;" class="btn btn-warning disabled"><?php echo t('characters.themes.liberado2') ?></a>
						<?php } ?>
					<?php endif ?>
				<?php endif ?>
			</div>
		</div>	
		<?php endforeach ?>		
	</div>
</div>