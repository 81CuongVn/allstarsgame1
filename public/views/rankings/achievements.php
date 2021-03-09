<?php echo partial('shared/title', array('title' => 'rankings.achievement.title', 'place' => 'rankings.achievement.title')) ?>
<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
	<p>Filtro do Ranking</p>
</div>
<form id="ranking-players-filter-form" method="post">
	<input type="hidden" id="h_character_id" value="<?php echo $character_id?>" />
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="filtros">
		<tr>
			<td align="center">
				<b><?php echo t('characters.create.labels.anime') ?></b><br />
				<select name="anime_id" id="anime_id" class="form-control" style="width:130px">
					<option value="0"><?php echo t('global.all') ?></option>
					<?php foreach ($animes as $anime): ?>
						<option value="<?php echo $anime->id ?>" <?php if ($anime->id == $anime_id): ?>selected="selected"<?php endif ?>><?php echo $anime->description()->name ?></option>
					<?php endforeach ?>
				</select>
			</td>
			<td align="center">
				<div id="characters">Carregando...</div>
			</td>
			<td align="center">
				<b><?php echo t('characters.select.labels.faction') ?></b><br />
				<select name="faction_id" class="form-control">
					<option value="0"><?php echo t('global.all') ?></option>
          			<?php foreach ($factions as $faction): ?>
						<option value="<?=$faction->id;?>"<?php if ($faction->id == $faction_id): ?>selected="selected"<?php endif ?>><?=$faction->name;?></option>
          			<?php endforeach ?>
				</select>
			</td>
			<td align="center">
				<b><?php echo t('characters.select.labels.graduation') ?></b><br />
				<select name="graduation_id" class="form-control" style="width: 130px">
					<option value="0"><?=t('global.all');?></option>
					<?php if ($anime_id): foreach ($graduations as $graduation): ?>
						<?php if ($anime_id): ?>
							<option value="<?=$graduation->id;?>" <?php if ($graduation->id == $graduation_id): ?>selected="selected"<?php endif ?>><?=$graduation->description($anime_id)->name;?></option>
						<?php else: ?>
							<option value="<?=$graduation['id'];?>" <?php if ($graduation['id'] == $graduation_id): ?>selected="selected"<?php endif ?>><?=$graduation['name'];?></option>
						<?php endif; ?>
					<?php endforeach; endif; ?>
				</select>
			</td>
			<td align="center">
				<b><?php echo t('rankings.players.header.nome') ?></b><br />
				<input type="text" name="name" class="form-control" value="<?php echo $name ?>" style="width:120px"/>
			</td>
			<td align="center">
				<a href="javascript:;" class="btn btn-primary filter" style="margin-top: 14px"><?php echo t('buttons.filtrar') ?></a>
			</td>
		</tr>
	</table>
	<br />
	<br />
	<input type="hidden" name="page" value="<?php echo $page ?>" />
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
			<div>
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
					<img src="<?=image_url($p->faction_id . ".png");?>" width="25" />
				</div>
				<div class="description" style="height: auto; font-size:11px">
					<span style="font-size:12px">
						<?=$p->anime()->description()->name;?> /<br />
						<?=$p->graduation()->description($p->anime()->id)->name;?>
					</span><br />
					Nível <?=highamount($p->level);?>
				</div>
				<div class="details">
					<b class="laranja" style="font-size: 14px"><?=highamount($p->score);?></b>
				</div>
				<div class="button" style="position:relative; top: 15px;"></div>
			</div>
		</div>
	<?php } ?>
	<div class="break"></div>	
	<?=partial('shared/paginator', [
    	'pages'		=> $pages,
		'current'	=> $page + 1
	]) ?>
</form>