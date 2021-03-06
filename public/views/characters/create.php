<?php echo partial('shared/title', array('title' => 'characters.create.title', 'place' => 'characters.create.title')) ?>
<?php if(sizeof($total) >= $user->character_slots){?>
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
<?php }else{?>	
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
					<input class="button btn btn-warning" id="change-theme" type="button" value="<?php echo t('characters.create.change_theme') ?>" style="position:relative; top: -30px" />
					<div id="character-info" style="float: left; width: 240px; text-align: left; position: relative; line-height: 27px;">
						<div class="row">
							<div class="col-lg-2">
								<labeL class="branco" style="margin-top: 7px"><?php echo t('characters.create.labels.name') ?>:</labeL>
							</div>
							<div class="col-lg-9" style="height: 30px">
								<input type="text" name="name" placeholder="Nome do personagem" class="form-control" /><br />
							</div>
						</div>
						<span class="branco"><?php echo t('characters.create.labels.anime') ?>:</span> <span class="cinza anime">--</span><br />
						<span class="branco"><?php echo t('characters.create.labels.anime_totals') ?>:</span> <span class="cinza anime_totals">--</span><br />
						<span class="branco"><?php echo t('characters.create.labels.character') ?>:</span> <span class="cinza character">--</span><br />
						<span class="branco"><?php echo t('characters.create.labels.character_totals') ?>:</span> <span class="cinza character_totals">--</span>
						<div class="break"></div>
						<div class="titulo-home4"><p>Facção</p></div>
						<div class="faccao" data-faction="1">
							<img src="<?php echo image_url('herois.jpg') ?>" width="120" /><br />
							<div>Heróis</div>
						</div>
						<div class="faccao" data-faction="2">
							<img src="<?php echo image_url('viloes.jpg') ?>"  width="120" /><br />
							<div>Vilões</div>
						</div>	
						<input type="submit" class="btn btn-primary" value="<?php echo t('characters.create.submit') ?>" style="position:relative; left: 40px; top: 20px;"/>
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
							<a class="anime page-item page-item-<?php echo ceil($counter++ / 5) ?>" data-id="<?php echo $anime->id ?>">
								<img src="<?php echo image_url('anime/' . $anime->id . '.jpg') ?>" alt="<?php echo $anime->description()->name ?>" />
							</a>			
						<?php endforeach ?>
						<div class="break"></div>
						<div class="character-paginator" data-target-container="#anime-list">
							<?php for($f = 1; $f <= ceil(sizeof($animes) / 5); $f++): ?>
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
									$characters	= $anime->characters($_SESSION['universal'] ? '' : ' AND active=1');
								?>
								<?php foreach ($characters as $character): ?>
									<?php
										$unlocked	= $character->unlocked($user);
									?>
									<a class="character page-item page-item-<?php echo ceil($counter++ / 9) ?> <?php echo !$unlocked ? 'locked' : '' ?>" data-id="<?php echo $character->id ?>">
										<?php if (!$unlocked): ?>
											<span class="glyphicon glyphicon-ban-circle"></span>
										<?php endif ?>
										<?php echo $character->small_image() ?>
									</a>
								<?php endforeach ?>
								<div class="break"></div>
								<div class="character-paginator" data-target-container="#anime-characters-<?php echo $anime->id ?>">
									<?php for($f = 1; $f <= ceil(sizeof($characters) / 9); $f++): ?>
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
		var	_characters	= [];
		var	_animes		= [];
	
		<?php foreach ($animes as $anime): ?>
			_animes[<?php echo $anime->id ?>]	= '<?php echo addslashes($anime->description()->name) ?>';
	
			<?php foreach ($anime->characters($_SESSION['universal'] ? '' : ' AND active=1') as $character): ?>
				_characters[<?php echo $character->id ?>]	= {
					name:		'<?php echo addslashes($character->description()->name) ?>',
					anime:		<?php echo $anime->id ?>,
					profile:	"<?php echo image_url($character->profile_image(true)) ?>",
					at: {
						for_atk:	<?php echo $character->for_atk ?>,
						for_def:	<?php echo $character->for_def ?>,
						for_crit:	<?php echo $character->for_crit ?>,
						for_abs:	<?php echo $character->for_abs ?>,
						for_prec:	<?php echo $character->for_prec ?>,
						for_init:	<?php echo $character->for_init ?>
					}
				};
			<?php endforeach ?>
		<?php endforeach ?>
	</script>
<?php }?>	