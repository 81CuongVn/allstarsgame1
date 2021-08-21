<style type="text/css">
	.select2-container--bootstrap {
		margin-bottom: 0;
	}
</style>
<?php echo partial('shared/title', array('title' => 'rankings.battles.title', 'place' => 'rankings.players.title')) ?>
<div class="barra-secao barra-secao-1">
	<p>Filtro do Ranking</p>
</div>
<form id="ranking-players-filter-form" method="post">
	<input type="hidden" id="h_character_id" value="<?php echo $character_id?>" />
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="filtros">
		<tr>
			<td align="center">
				<b><?php echo t('characters.create.labels.anime') ?></b><br />
				<select name="anime_id" id="anime_id" class="form-control input-sm select2" style="width:110px">
					<option value="0"><?php echo t('global.all') ?></option>
					<?php foreach ($animes as $anime): ?>
						<option value="<?php echo $anime->id ?>" <?php if ($anime->id == $anime_id): ?>selected="selected"<?php endif ?>><?php echo $anime->description()->name ?></option>
					<?php endforeach ?>
				</select>
			</td>
			<td align="center">
				<b><?php echo t('characters.create.labels.character') ?></b><br />
				<div id="characters">
					<select name="character_id" class="form-control input-sm select2" id="character_id" style="width: 121px">
						<option value="0"><?php echo t('global.all') ?></option>
					</select>
				</div>
			</td>
			<td align="center">
				<b><?php echo t('characters.select.labels.faction') ?></b><br />
				<select name="faction_id" class="form-control input-sm">
					<option value="0"><?php echo t('global.all') ?></option>
          			<?php foreach ($factions as $faction): ?>
						<option value="<?=$faction->id;?>"<?php if ($faction->id == $faction_id): ?>selected="selected"<?php endif ?>><?=$faction->description()->name;?></option>
          			<?php endforeach ?>
				</select>
			</td>
			<td align="center">
				<b><?php echo t('characters.select.labels.graduation') ?></b><br />
				<select name="graduation_id" class="form-control input-sm" style="width: 130px">
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
				<b>Status</b><br />
				<select name="status" class="form-control input-sm">
					<option value="victory_pvp" <?php if ("victory_pvp" == $status): ?>selected="selected"<?php endif ?>>Vitórias PVP</option>
					<option value="victory_npc" <?php if ("victory_npc" == $status): ?>selected="selected"<?php endif ?>>Vitórias NPC</option>
					<option value="looses_pvp" <?php if ("looses_pvp" == $status): ?>selected="selected"<?php endif ?>>Derrotas PVP</option>
					<option value="looses_npc" <?php if ("looses_npc" == $status): ?>selected="selected"<?php endif ?>>Derrotas NPC</option>
					<option value="draws_pvp" <?php if ("draws_pvp" == $status): ?>selected="selected"<?php endif ?>>Empates PVP</option>
					<option value="draws_npc" <?php if ("draws_npc" == $status): ?>selected="selected"<?php endif ?>>Empates NPC</option>
				</select>
			</td>
			<td align="center">
				<b><?php echo t('rankings.battles.periodo') ?></b><br />
				<select name="periodo" class="form-control input-sm" style="width: 80px">
					<option value="daily" <?php if ("diario" == $periodo): ?>selected="selected"<?php endif ?>>Diário</option>
					<option value="weekly" <?php if ("semanal" == $periodo): ?>selected="selected"<?php endif ?>>Semanal</option>
					<option value="monthly" <?php if ("mensal" == $periodo): ?>selected="selected"<?php endif ?>>Mensal</option>
				</select>
			</td>
			<td align="center">
				<a href="javascript:;" class="btn btn-sm btn-primary filter" style="margin-top: 14px"><?php echo t('buttons.filtrar') ?></a>
			</td>
		</tr>
	</table>
	<br />
	<br />
	<input type="hidden" name="page" value="<?php echo $page ?>" />
	<?php
		$victory_pvp 	= ($periodo == "daily" || !$periodo)  ? "victory_pvp" : "victory_pvp_".$periodo;
		$victory_npc 	= ($periodo == "daily" || !$periodo)  ? "victory_npc" : "victory_npc_".$periodo;
		$looses_pvp		= ($periodo == "daily" || !$periodo)  ? "looses_pvp" : "looses_pvp_".$periodo;
		$looses_npc 	= ($periodo == "daily" || !$periodo)  ? "looses_npc" : "looses_npc_".$periodo;
		$draws_pvp 		= ($periodo == "daily" || !$periodo)  ? "draws_pvp" : "draws_pvp_".$periodo;
		$draws_npc 		= ($periodo == "daily" || !$periodo)  ? "draws_npc" : "draws_npc_".$periodo;

	?>
		<?php $counter = 0; ?>
		<?php foreach ($players as $p):
			  $counter++;
		?>
			<?php
				if($counter==1){
					$cor_fundo = "#f9e1a7";
					$cor	   = "ouro";
					$class	   = "league-img-1";
				}
				if($counter==2){
					$cor_fundo = "#dddddd";
					$cor	   = "prata";
					$class	   = "league-img-2";
				}
				if($counter==3){
					$cor_fundo = "#f89b52";
					$cor	   = "bronzeado";
					$class	   = "league-img-3";
				}
				if($counter>3){
					$cor_fundo = "#232323";
					$cor	   = "branco";
					$class	   = "league-img-4";
				}
			?>
			<div class="ability-speciality-box" style="width: 175px !important; height: 300px !important; padding-bottom: 40px">
				<div>
					<div class="image" align="center">
						<div class="<?=$class;?>">
							<div class="position">
								<b class="<?=$cor;?>"><?=highamount($page * 30 + $counter);?>º</b>
							</div>
							<?=$p->character_theme()->first_image()->small_image();?>
						</div>
					</div>
					<div class="name" style="height: 40px !important;">
						<div class="amarelo">
							<?php if (is_player_online($p->player_id)): ?>
								<img src="<?php echo image_url("on.png" ) ?>"/>
							<?php else: ?>
								<img src="<?php echo image_url("off.png" ) ?>"/>
							<?php endif ?>
							<b><?php echo $p->name ?></b>
						</div>
						<span style="font-size:12px"><?php echo $p->anime()->description()->name ?></span>
					</div>
					<div class="details">
						<div style="float: left; width: 70px;">
							<span class="laranja"><?php echo t('rankings.battles.victory') ?> PVP</span><br />
							<?=highamount($p->$victory_pvp);?>
						</div>
						<div style="float: left; width: 70px;">
							<span class="laranja"><?php echo t('rankings.battles.victory') ?> NPC</span><br />
							<?=highamount($p->$victory_npc);?>
						</div>
						<div style="float: left; width: 70px;">
							<span class="laranja"><?php echo t('rankings.battles.looses') ?> PVP</span><br />
							<?=highamount($p->$looses_pvp);?>
						</div>
						<div style="float: left; width: 70px;">
							<span class="laranja"><?php echo t('rankings.battles.looses') ?> NPC</span><br />
							<?=highamount($p->$looses_npc);?>
						</div>
						<div style="float: left; width: 70px;">
							<span class="laranja"><?php echo t('rankings.battles.draws') ?> PVP</span><br />
							<?=highamount($p->$draws_pvp);?>
						</div>
						<div style="float: left; width: 70px;">
							<span class="laranja"><?php echo t('rankings.battles.draws') ?> NPC</span><br />
							<?=highamount($p->$draws_npc);?>
						</div>
					</div>
					<div class="button" style="position:relative; top: 15px;">
						<img src="<?=image_url('factions/icons/big/' . $p->faction_id . ".png");?>" width="25" />
					</div>
				</div>
			</div>
		<?php endforeach ?>
		<div class="break"></div>
	<?=partial('shared/paginator', [
    'pages'   => $pages,
    'current' => $page + 1
  ]);?>
</form>
