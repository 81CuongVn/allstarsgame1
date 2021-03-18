<?php echo partial('shared/title', array('title' => 'rankings.organizations.title', 'place' => 'rankings.organizations.title')) ?>
<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
	<p>Filtro do Ranking</p>
</div>
<form id="ranking-players-filter-form" method="post">
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="filtros">
		<tr>
			<td width="205" align="center">
				<b><?php echo t('characters.select.labels.faction') ?></b><br />
				<select name="faction_id" class="form-control input-sm" style="max-width: 100px;">
					<option value="0"><?=t('global.all');?></option>
					<?php foreach ($factions as $faction): ?>
						<option value="<?=$faction->id;?>"<?php if ($faction->id == $faction_id): ?>selected="selected"<?php endif ?>><?=$faction->name;?></option>
          			<?php endforeach ?>
				</select>
			</td>
			<td width="264" align="center">
				<b><?php echo t('rankings.players.header.nome') ?></b><br />
				<input type="text" class="form-control input-sm" name="name" value="<?php echo $name ?>" />
			</td>
			<td width="256" align="center">
				<a href="javascript:;" class="btn btn-sm btn-primary filter" style="margin-top: 14px"><?php echo t('buttons.filtrar') ?></a>
			</td>
		</tr>
	</table>
	<br />
	<br />
	<input type="hidden" name="page" value="<?php echo $page ?>" />
	<?php
	foreach ($players as $p) {
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
		?>
		<div class="ability-speciality-box" style="width: 175px !important; height: 235px !important; padding-bottom: 40px">
			<div class="image" align="center">
				<div class="<?=$class;?>">
					<div class="position">
						<b class="<?=$cor;?>"><?=$p->position_general;?>º</b>
					</div>
					<?=$p->character_theme()->first_image()->small_image();?>
				</div>
			</div>
			<div class="name" style="height: 40px !important;">
				<div class="verde" style="margin-bottom: 5px;">
					<b><?=$p->name;?></b>
				</div>
				<div class="amarelo">
					<?=$p->leader_name;?>
				</div>
			</div>
			<div class="description" style="height: auto;">
				<div style="float: left; width: 70px;">
					<span class="laranja">Membros</span><br />
					<?=highamount($p->members);?>
				</div>
				<div style="float: left; width: 70px;">
					<span class="laranja">Pontos</span><br />
						<?=highamount($p->score);?>
				</div>
			</div>
			<div class="button" style="position:relative; top: 15px;">
				<img src="<?=image_url($p->faction_id . ".png");?>" width="25" />
			</div>
		</div>
	<?php } ?>
	<div class="break"></div>
	<?=partial('shared/paginator', [
		'pages'		=> $pages,
		'current'	=> $page + 1
	]) ?>
</form>