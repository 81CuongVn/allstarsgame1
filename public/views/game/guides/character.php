<style type="text/css">
	div#theme-list-ajax {
		text-align: left;
		padding-left: 9px;
	}
	div#theme-list-ajax img {
		width: 55px;
		margin: 0 -1px;
	}
</style>
<?php echo partial('shared/title', array('title' => 'menus.character_guide', 'place' => 'menus.character_guide')) ?>
<?php if (FW_ENV != 'dev') { ?>
	<!-- AASG - Guias -->
	<ins class="adsbygoogle"
		style="display:inline-block;width:728px;height:90px"
		data-ad-client="ca-pub-6665062829379662"
		data-ad-slot="7729901030"></ins>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
	</script><br />
<?php } ?>
<form id="f-create-character">
	<input type="hidden" name="faction_id" value="1" />
	<input type="hidden" name="character_id" value="" />
	<input type="hidden" id="character_theme_id" name="character_theme_id" value="" />
	<div id="character-creation-container">
		<div id="character-data">
				<div style="float: left; width: 730px;">
					<div class="titulo-home3"><p><?php echo t('characters.create.section_anime') ?></p></div>
				</div>
				<div id="anime-list">
					<?php
						$counter	= 1;
					?>
					<?php foreach ($animes as $anime): ?>
						<a data-toggle="tooltip" title="<?=make_tooltip($anime->description()->name);?>" data-placement="bottom" class="anime page-item page-item-<?php echo ceil($counter++ / 12) ?>" data-id="<?php echo $anime->id ?>" style="padding-left: 10px !important">
							<img src="<?php echo image_url('anime/' . $anime->id . '.jpg') ?>" alt="<?php echo $anime->description()->name ?>" width="50"/>
						</a>
					<?php endforeach ?>
					<div class="break"></div>
					<div class="character-paginator" data-target-container="#anime-list">
						<?php for($f = 1; $f <= ceil(sizeof($animes) / 12); $f++): ?>
							<div class="page" data-page="<?php echo $f ?>"><?php echo $f ?></div>
						<?php endfor; ?>
					</div>
				</div>
				<div style="float: left; width: 730px;">
					<div class="titulo-home3"><p><?php echo t('characters.create.section_character') ?></p></div>
				</div>
				<div id="anime-character-list">
					<?php foreach ($animes as $anime): ?>
						<div id="anime-characters-<?php echo $anime->id ?>" class="anime-characters">
							<?php
								$counter	= 1;
								$characters	= $anime->characters();
							?>
							<?php foreach ($characters as $character): ?>
								<?php $character_themes  = $character->themes_default($character->id); ?>
								<?php foreach ($character_themes as $character_theme): ?>
									<a data-toggle="tooltip" title="<?=make_tooltip($character->description()->name);?>" data-placement="top" class="themes-uniques character page-item page-item-<?php echo ceil($counter++ / 10) ?>" data-id="<?php echo $character->id ?>" data-theme-id="<?php echo $character_theme->id ?>" style="height: 70px; width: 70px;">
										<?php echo $character->small_image2() ?>
									</a>
								<?php endforeach ?>
							<?php endforeach ?>
							<div class="break"></div>
							<div class="character-paginator" data-target-container="#anime-characters-<?php echo $anime->id ?>">
								<?php for($f = 1; $f <= ceil(sizeof($characters) / 10); $f++): ?>
									<div class="page" data-page="<?php echo $f ?>"><?php echo $f ?></div>
								<?php endfor; ?>
							</div>
						</div>
					<?php endforeach ?>
					<div class="break"></div>
				</div>
			<div class="break"></div>
			<div style="width:231px; float: left; position: relative; top: 20px; text-align: center ">
				<img width="235" height="281" id="character-profile-image" />
				<div id="name-character" class="nome-personagem"></div>
				<input class="button btn btn-sm btn-primary" type="button" id="theme-view-image" data-url="<?php echo make_url('characters#list_images_only') ?>" value="Imagens" style="position:relative; top: -30px" />
				<div class="titulo-home4" style="margin-top: -30px;"><p>Temas</p></div>
				<div id="theme-list-ajax"></div>
			</div>
			<div style="width:495px; height:auto; float: left; position: relative; top: 10px; left: 3px">
				<div id="character-attacks-unique"></div>
			</div>
		</div>
	</div>
</form>
<script type="text/javascript">
	var	_characters	= [];
	var	_animes		= [];

	<?php foreach ($animes as $anime): ?>
		_animes[<?php echo $anime->id ?>]	= '<?php echo addslashes($anime->description()->name) ?>';

		<?php foreach ($anime->characters() as $character): ?>
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
