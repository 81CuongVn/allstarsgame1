<style type="text/css">
	.select2-container--bootstrap {
		margin-bottom: 0;
	}
</style>
<?=partial('shared/title', [
	'title'	=> 'rankings.hall.title',
	'place'	=> 'rankings.hall.title'
]);?>
<div class="barra-secao barra-secao-1">
	<p>Filtro do Hall da Fama</p>
</div>
<form id="ranking-players-filter-form" method="post">
	<input type="hidden" id="h_character_id" value="<?=$character_id;?>" />
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="filtros">
		<tr>
			<td align="center" style="max-width: 200px;">
				<b><?php echo t('characters.create.labels.anime') ?></b><br />
				<select class="form-control input-sm select2" name="anime_id" id="anime_id" style="width: 186px">
					<option value="0"><?php echo t('global.all') ?></option>
					<?php foreach ($animes as $anime): ?>
						<option value="<?php echo $anime->id ?>" <?php if ($anime->id == $anime_id): ?>selected="selected"<?php endif ?>><?php echo $anime->description()->name ?></option>
					<?php endforeach ?>
				</select>
			</td>
			<td align="center" style="max-width: 151px;">
				<b><?php echo t('characters.create.labels.character') ?></b><br />
				<div id="characters">
					<select name="character_id" class="form-control input-sm select2" id="character_id" style="width: 121px">
						<option value="0"><?php echo t('global.all') ?></option>
					</select>
				</div>
			</td>
			<td align="center" style="max-width: 98px;">
				<b>Round</b><br />
				<select class="form-control input-sm" name="round" style="width: 91px">
					<option value="eterno" <?php if ("eterno" == $round): ?>selected="selected"<?php endif ?>>Eterno</option>
					<option value="r1" <?php if ("r1" == $round): ?>selected="selected"<?php endif ?>>Round 1</option>
					<option value="r2" <?php if ("r2" == $round): ?>selected="selected"<?php endif ?>>Round 2</option>
					<option value="eterno2" <?php if ("eterno2" == $round): ?>selected="selected"<?php endif ?>>Eterno 2</option>
				</select>
			</td>
			<td align="center" style="max-width: 86px;">
				<b><?php echo t('characters.select.labels.faction') ?></b><br />
				<select class="form-control input-sm" name="faction_id" style="width: 80px">
					<option value="0"><?php echo t('global.all') ?></option>
					<?php foreach ($factions as $faction): ?>
						<option value="<?=$faction->id;?>"<?php if ($faction->id == $faction_id): ?>selected="selected"<?php endif ?>><?=$faction->description()->name;?></option>
					<?php endforeach ?>
				</select>
			</td>
			<td align="center" style="max-width: 130px;">
				<b><?php echo t('rankings.players.header.nome') ?></b><br />
				<input class="form-control input-sm" type="text" name="name" value="<?php echo $name ?>" />
			</td>
			<td align="center" style="max-width: 65px;">
				<a href="javascript:;" class="btn btn-sm btn-primary filter" style="margin-top: 14px"><?php echo t('buttons.filtrar') ?></a>
			</td>
		</tr>
	</table>
	<input type="hidden" name="page" value="<?=$page;?>" />
	<br /><br />
	<?php
	foreach ($players as $p) {
		if ($anime_id) {
			if($p->position_anime == 1) {
				$cor_fundo = "#f9e1a7";
				$cor	   = "ouro";
				$class	   = "league-img-1";
			} elseif ($p->position_anime == 2) {
				$cor_fundo = "#dddddd";
				$cor	   = "prata";
				$class	   = "league-img-2";
			} elseif ($p->position_anime == 3) {
				$cor_fundo = "#f89b52";
				$cor	   = "bronzeado";
				$class	   = "league-img-3";
			} elseif ($p->position_anime > 3) {
				$cor_fundo = "#232323";
				$cor	   = "branco";
				$class	   = "league-img-4";
			}
		} else {
			if ($p->position_general == 1) {
				$cor_fundo = "#f9e1a7";
				$cor	   = "ouro";
				$class	   = "league-img-1";
			} elseif ($p->position_general == 2) {
				$cor_fundo = "#dddddd";
				$cor	   = "prata";
				$class	   = "league-img-2";
			} elseif ($p->position_general == 3) {
				$cor_fundo = "#f89b52";
				$cor	   = "bronzeado";
				$class	   = "league-img-3";
			} elseif ($p->position_general > 3) {
				$cor_fundo = "#232323";
				$cor	   = "branco";
				$class	   = "league-img-4";
			}
		}
		?>
		<div class="ability-speciality-box" style="width: 175px !important; height: 250px !important; padding-bottom: 40px">
			<div class="image" align="center">
				<div class="<?=$class;?>">
					<div class="position">
					<?php if ($anime_id) { ?>
						<b class="<?=$cor;?>"><?=highamount($p->position_anime);?>º</b>
					<?php } else { ?>
						<b class="<?=$cor;?>"><?=highamount($p->position_general);?>º</b>
					<?php } ?>
					</div>
					<?=$p->character_theme()->first_image()->small_image();?>
				</div>
			</div>
			<div class="name" style="height: 45px !important;">
				<div class="amarelo" style="margin-bottom: 6px;">
					<?php if (is_player_online($p->player_id)): ?>
						<img src="<?php echo image_url("on.png" ) ?>"/>
					<?php else: ?>
						<img src="<?php echo image_url("off.png" ) ?>"/>
					<?php endif ?>
					<b><?=$p->name;?></b>
				</div>
				<img src="<?=image_url('factions/icons/big/' . $p->faction_id . ".png");?>" width="25" />
			</div>
			<div class="description" style="height: auto; font-size:11px">
				<span style="font-size:12px">
					<?=$p->anime()->description()->name;?>
				</span><br />
				Nível <?=highamount($p->level);?>
			</div>
			<div class="details">
				<b class="laranja" style="cursor: pointer; font-size: 14px"><?=highamount($p->score);?></b>
			</div>
			<div class="button" style="position:relative; top: 15px;">
				<b class="azul" style="text-transform: uppercase;font-size: 11px">Conta Nível <?php echo $p->account_level ?></b>
			</div>
		</div>
	<?php } ?>
	<div class="break"></div>	
	<?=partial('shared/paginator', [
    	'pages'		=> $pages,
		'current'	=> $page + 1
	]) ?>
</form>