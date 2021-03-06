<?php echo partial('shared/title', array('title' => 'characters.select.title', 'place' => 'characters.select.title')) ?>
<?php if (!sizeof($players)): ?>
	<?php echo partial('shared/info', array('id'=> 3, 'title' => 'characters.select.none', 'message' => t('characters.select.none_msg', array('url' => make_url('characters#create'))))) ?>
<?php else: ?>
	<?php if (isset($_GET['created'])): ?>
		<?php echo partial('shared/info', array('id'=> 3, 'title' => 'characters.create.created', 'message' => t('characters.create.created_msg'))) ?>
	<?php endif ?>
	<?php if (isset($_GET['deleted'])): ?>
		<?php echo partial('shared/info', array('id'=> 3, 'title' => 'characters.remove.success', 'message' => t('characters.remove.success_msg'))) ?>
	<?php endif ?>
	<?php if (isset($_GET['deleted_ok'])): ?>
		<?php echo partial('shared/info', array('id'=> 3, 'title' => 'characters.removed.success', 'message' => t('characters.removed.success_msg'))) ?>
	<?php endif ?>
	<div style="width: 730px; position: relative;">
		<div style="width:231px; height:300px; float: left; position: relative; top: 20px;" id="current-player-image">

		</div>
		<div style="width:495px; height:300px; float: left; position: relative; top: 30px;">
			<div style="float: left; width: 495px;">
				<div class="titulo-home2"><p>Dados do Personagem</p></div>
			</div>
			<div style="float: left; width: 255px; text-align: left; position: relative; top: 25px;" id="current-player-info">
				<div class="laranja name" style="font-size:16px;">--</div>
				<div class="box_level level">
					--
				</div>
				<div style="float: left; position: relative; top: 15px; left: 5px;">
					<div class="b4">
						<?php echo t('characters.select.labels.graduation') ?>: <span class="cinza graduation">--</span>
					</div>
					<div class="bar-exp"><?php echo exp_bar(0, 0, 175) ?></div>
				</div>
				<div style="float: left; clear:both; position: relative; top: 15px;">
					<span class="branco currency"><?php echo t('characters.select.labels.currency') ?> </span>: <span class="cinza amount">--</span><br />
					<span class="branco"><?php echo t('characters.select.labels.anime') ?>: </span><span class="cinza anime">--</span>
				</div>
				<div style="float: left; clear:both; position: relative; top: 40px; width: 490px; text-align: center">
					<div  id="playerButtons" style="display: none;">
						<input class="button btn btn-primary play" type="button" value="<?php echo t('buttons.play') ?>" style="width:80px;" />
						<input class="button btn btn-danger remove" type="button" value="<?php echo t('buttons.remove') ?>" style="width:80px;" data-message="<?php echo t('characters.select.delete_confirmation') ?>" />
					</div>
					<div  id="playerBanned" style="display: none; text-transform: uppercase;">
						<button class="button btn btn-danger disabled" type="button" style="text-transform: uppercase;" disabled>
							Este personagem foi banido!
						</button>
					</div>
				</div>
			</div>
			<div style="float: left; width: 240px; text-align: left; position: relative; top: 20px;" id="current-player-attributes">
				<div class="bg_td2">
					<div class="atr_float"  style="width: 24px; text-align:left; left: 10px; position:relative;">
						<img src="<?php echo image_url('icons/for_life.png') ?>" style="margin-top:-6px;" />
					</div>
					<div class="amarelo atr_float" style="width: 90px; text-align:left; padding-left:16px;">Vida</div>
					<div class="atr_float bar-life" style="margin-top: 7px">
						<?php echo exp_bar(0, 0, 110) ?>
					</div>
				</div>
				<div class="bg_td2">
					<div class="atr_float"  style="width: 24px; text-align:left; left: 10px; position:relative;">
						<img src="<?php echo image_url('icons/for_mana.png') ?>" style="margin-top:-6px;" />
					</div>
					<div class="amarelo atr_float mana-name" style="width: 90px; text-align:left; padding-left:16px;">--</div>
					<div class="atr_float bar-mana" style="margin-top: 7px">
						<?php echo exp_bar(0, 0, 110) ?>
					</div>
				</div>
				<div class="bg_td2">
					<div class="atr_float"  style="width: 24px; text-align:left; left: 10px; position:relative;">
						<img src="<?php echo image_url('icons/for_stamina.png') ?>" style="margin-top:-6px;" />
					</div>
					<div class="amarelo atr_float" style="width: 90px; text-align:left; padding-left:16px;">Stamina</div>
					<div class="atr_float bar-stamina" style="margin-top: 7px">
						<?php echo exp_bar(0, 0, 110) ?>
					</div>
				</div>
			</div>
		</div>
		<div style="position: relative; clear: both; float: left; top: 20px;">
			<div class="barra-secao"><p><?php echo t('characters.select.section_favorite') ?></p></div>
			<div id="select-player-list-container">
				<div id="select-player-list-container">
					<?php
					$counter	= 1;
					foreach ($players as $player):
						?>
						<a data-id="<?=$player->id;?>" data-map-id="<?=($player->map_id ? $player->map_id : 0);?>" class="player page-item page-item-<?=ceil($counter++ / 10);?> <?=($player->banned ? 'locked' : '');?>">
							<?php /*if ($player->banned) { ?>
								<span class="glyphicon glyphicon-ban-circle"></span>
							<?php }*/ ?>
							<?=$player->small_image();?>
						</a>
					<?php endforeach ?>
					<div class="break"></div>
					<div class="character-paginator" data-target-container="#select-player-list-container">
						<?php for($f = 1; $f <= ceil(sizeof($players) / 10); $f++): ?>
							<div class="page" data-page="<?php echo $f ?>"><?php echo $f ?></div>
						<?php endfor; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		var	_players	= [];
		<?php foreach ($players as $player) { ?>
		_players[<?=$player->id;?>]	= {
			name:			"<?=$player->name;?>",
			anime:			"<?=$player->character()->anime()->description()->name;?>",
			level:			<?=$player->level;?>,
			profile:		"<?=image_url($player->profile_image(true));?>",
			currency:		"<?=t('currencies.' . $player->character()->anime_id);?>",
			amount:			"<?=highamount($player->currency);?>",
			graduation:		"<?=$player->graduation()->description($player->character()->anime_id)->name;?>",
			mana_name:		"<?=t('formula.for_mana.' . $player->character_theme()->anime()->id);?>",
			exp:			<?=$player->exp ?>,
			level_exp:		<?=$player->level_exp();?>,
			life:			<?=$player->for_life();?>,
			max_life:		<?=$player->for_life(true);?>,
			mana:			<?=$player->for_mana();?>,
			max_mana:		<?=$player->for_mana(true);?>,
			stamina:		<?=$player->for_stamina();?>,
			max_stamina:	<?=$player->for_stamina(true);?>,
			ultimate:		<?=($player->character_theme_image()->ultimate ? 'true' : 'false');?>
		};
		<?php } ?>
	</script>
<?php endif ?>