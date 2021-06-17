<?php echo partial('shared/title', array('title' => 'characters.create.title', 'place' => 'characters.create.title')) ?>
<?php if (sizeof($total) >= $user->character_slots) { ?>
	<?php
		echo partial('shared/info', [
			'id'		=> 1,
			'title'		=> 'characters.title_chars',
			'message'	=> t('characters.description_chars', [
				'link' => make_url('vips')
			])
		]);
	?>
	<br />
<?php } else {?>
	<?php
		echo partial('shared/info', [
			'id'		=> 1,
			'title'		=> 'characters.title',
			'message'	=> t('characters.description', array('link' => make_url('guides#character'))) . '<br /><br /><span class="laranja">Você ainda pode criar '. (($user->character_slots) - sizeof($total)).' personagen(s).</span>'
		]);
	?>
	<br />
	<form id="f-create-character">
		<input type="hidden" name="faction_id" value="1" />
		<input type="hidden" name="character_id" value="" />
		<div id="character-creation-container">
			<div id="character-data">
				<div style="width:231px; height:300px; float: left; position: relative; top: 20px; text-align: center ">
					<img width="235" height="281" id="character-profile-image" />
					<input class="button btn btn-sm btn-warning" id="change-theme" type="button" value="<?php echo t('characters.create.change_theme') ?>" style="position:relative; top: -30px" />
					<div id="character-info" style="float: left; width: 240px; text-align: left; position: relative; line-height: 27px;">
						<div class="form-group">
							<labeL for="name"><?php echo t('characters.create.labels.name') ?>:</labeL>
							<input type="text" id="name" name="name" placeholder="Nome do personagem" class="form-control input-sm" require />
						</div>
						<div class="break"></div>
						<div class="titulo-home4"><p>Facção</p></div>
						<?php foreach ($factions as $faction) { ?>
							<div data-toggle="tooltip" title="<?=make_tooltip($faction->description()->name)?>" data-placement="top" class="faccao" data-faction="<?=$faction->id;?>">
								<img src="<?=image_url($faction->image(true));?>" width="120" />
							</div>
						<?php } ?>
						<input type="submit" class="btn btn-sm btn-primary" value="<?php echo t('characters.create.submit') ?>" style="position:relative; left: 40px; top: 20px;"/>
					</div>

				</div>
				<div style="width:495px; height:auto; float: left; position: relative; top: 10px; left: 3px">
					<div style="float: left; width: 495px;">
						<div class="titulo-home2"><p><?php echo t('characters.create.section_anime') ?></p></div>
					</div>
					<div id="anime-list">
						<?php
							$counter	= 1;
						?>
						<?php foreach ($animes as $anime): ?>
							<a data-toggle="tooltip" title="<?=make_tooltip($anime->description()->name)?>" data-placement="bottom" class="anime page-item page-item-<?php echo ceil($counter++ / 6) ?>" data-id="<?php echo $anime->id ?>">
								<img src="<?php echo image_url('anime/' . $anime->id . '.jpg') ?>" alt="<?php echo $anime->description()->name ?>" />
							</a>
						<?php endforeach ?>
						<div class="break"></div>
						<div class="character-paginator" data-target-container="#anime-list">
							<?php for($f = 1; $f <= ceil(sizeof($animes) / 6); $f++): ?>
								<div class="page" data-page="<?php echo $f ?>"><?php echo $f ?></div>
							<?php endfor; ?>
						</div>
					</div>
					<div style="float: left; width: 495px;">
						<div class="titulo-home2"><p><?php echo t('characters.create.section_character') ?></p></div>
					</div>
					<div id="anime-character-list">
						<?php foreach ($animes as $anime): ?>
							<div id="anime-characters-<?php echo $anime->id ?>" class="anime-characters">
								<?php
									$counter	= 1;
									$characters	= $anime->characters($_SESSION['universal'] ? '' : ' AND active = 1');
								?>
								<?php foreach ($characters as $character): ?>
									<?php
										$unlocked	= $character->unlocked($user);
									?>
									<a data-toggle="tooltip" title="<?=make_tooltip($character->description()->name)?>" data-placement="top" class="character page-item page-item-<?php echo ceil($counter++ / 12) ?> <?php echo !$unlocked ? 'locked' : '' ?>" data-id="<?php echo $character->id ?>">
										<?php if (!$unlocked): ?>
											<span class="glyphicon glyphicon-ban-circle"></span>
										<?php endif ?>
										<img src="<?=image_url($character->small_image(true));?>" width="120" />
									</a>
								<?php endforeach ?>
								<div class="break"></div>
								<div class="character-paginator" data-target-container="#anime-characters-<?php echo $anime->id ?>">
									<?php for($f = 1; $f <= ceil(sizeof($characters) / 12); $f++): ?>
										<div class="page" data-page="<?php echo $f ?>"><?php echo $f ?></div>
									<?php endfor; ?>
								</div>
							</div>
						<?php endforeach ?>
						<div class="break"></div>
					</div>
				</div>
				<div class="break"></div>
			</div>
		</div>
	</form>
	<script type="text/javascript">
		var	_characters	= [],
			_animes		= [];

		<?php foreach ($animes as $anime) { ?>
			_animes[<?=$anime->id;?>]	= '<?=addslashes($anime->description()->name);?>';

			<?php foreach ($anime->characters($_SESSION['universal'] ? '' : ' AND active = 1') as $character) { ?>
				_characters[<?=$character->id;?>]	= {
					name:		'<?=addslashes($character->description()->name);?>',
					anime:		<?=$anime->id ?>,
					profile:	"<?=image_url($character->profile_image(true));?>",
					at: {
						for_atk:	<?=$character->for_atk;?>,
						for_def:	<?=$character->for_def;?>,
						for_crit:	<?=$character->for_crit;?>,
						for_abs:	<?=$character->for_abs;?>,
						for_prec:	<?=$character->for_prec;?>,
						for_init:	<?=$character->for_init;?>
					}
				};
			<?php } ?>
		<?php } ?>
	</script>
<?php } ?>
