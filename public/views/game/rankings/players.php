<style type="text/css">
	.select2-container--bootstrap {
		margin-bottom: 0;
	}
</style>
<?php echo partial('shared/title', array('title' => 'rankings.players.title', 'place' => 'rankings.players.title')) ?>
<?php if (FW_ENV != 'dev') { ?>
	<!-- AASG - Rankings -->
	<ins class="adsbygoogle"
		style="display:inline-block;width:728px;height:90px"
		data-ad-client="ca-pub-6665062829379662"
		data-ad-slot="5869383826"></ins>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
	</script><br />
<?php } ?>
<div class="barra-secao barra-secao-1">
	<p>Filtro do Ranking</p>
</div>
<form id="ranking-players-filter-form" method="post">
	<input type="hidden" id="h_character_id" value="<?php echo $character_id?>" />
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="filtros">
		<tr>
			<td align="center">
				<b><?php echo t('characters.create.labels.anime') ?></b><br />
				<select name="anime_id" id="anime_id" class="form-control input-sm select2" style="width:130px">
					<option value="0"><?=t('global.all');?></option>
					<?php foreach ($animes as $anime): ?>
					<option value="<?=$anime->id;?>" <?php if ($anime->id == $anime_id): ?>selected="selected"<?php endif; ?>><?=$anime->description()->name;?></option>
					<?php endforeach; ?>
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
				<b><?=t('characters.select.labels.faction');?></b><br />
				<select name="faction_id" class="form-control input-sm">
					<option value="0"><?=t('global.all');?></option>
					<?php foreach ($factions as $faction): ?>
						<option value="<?=$faction->id;?>"<?php if ($faction->id == $faction_id): ?>selected="selected"<?php endif ?>><?=$faction->description()->name;?></option>
          			<?php endforeach ?>
				</select>
			</td>
			<td align="center">
				<b><?=t('characters.select.labels.graduation');?></b><br />
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
				<b><?=t('rankings.players.header.nome');?></b><br />
				<input type="text" name="name" class="form-control input-sm" value="<?=$name;?>" style="width:120px"/>
			</td>
			<td align="center">
				<a href="javascript:;" class="btn btn-sm btn-primary filter" style="margin-top: 14px"><?=t('buttons.filtrar');?></a>
			</td>
		</tr>
	</table>
	<br />
	<br />
	<input type="hidden" name="page" value="<?=$page;?>" />
	<?php
	foreach ($players as $p) {
		if ($faction_id) {
			if($p->position_faction == 1) {
				$cor_fundo = "#f9e1a7";
				$cor	   = "ouro";
				$class	   = "league-img-1";
			} elseif ($p->position_faction == 2) {
				$cor_fundo = "#dddddd";
				$cor	   = "prata";
				$class	   = "league-img-2";
			} elseif ($p->position_faction == 3) {
				$cor_fundo = "#f89b52";
				$cor	   = "bronzeado";
				$class	   = "league-img-3";
			} elseif ($p->position_faction > 3) {
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
		<div class="ability-speciality-box" style="width: 175px; height: <?=($player ? 280 : 250);?>px; padding-bottom: 40px;">
			<div class="image" align="center">
				<div class="<?=$class;?>">
					<div class="position">
						<?php if ($faction_id) { ?>
							<b class="<?=$cor;?>"><?=highamount($p->position_faction);?>º</b>
						<?php } else { ?>
							<b class="<?=$cor;?>"><?=highamount($p->position_general);?>º</b>
						<?php } ?>
					</div>
					<?=$p->character_theme()->first_image()->small_image();?>
				</div>
			</div>
			<div class="name" style="height: 45px;">
				<a href="<?=make_url('profile', [
					'player'	=> $p->player_id
				]);?>" class="amarelo" style="margin-bottom: 6px; text-decoration: none; display: block;">
					<img src="<?=image_url((is_player_online($p->player_id) ? 'on' : 'off') . ".png");?>" />
					<b><?=$p->name;?></b>
				</a>
				<img src="<?=image_url('factions/icons/big/' . $p->faction_id . ".png");?>" width="25" />
			</div>
			<div class="description" style="height: auto; font-size: 11px;">
				<span style="font-size: 12px;">
					<?=$p->anime()->description()->name;?><br />
					<?=$p->graduation()->description($p->anime()->id)->name;?>
				</span><br />
				Nível <?=highamount($p->level);?>
			</div>
			<div class="details">
				<div class="technique-popover buff" data-source="#ranking-container-<?=$p->id;?>" data-title="Extrato de Pontuação" data-trigger="click" data-placement="bottom">
					<b class="laranja" style="cursor: pointer; font-size: 14px;"><?=highamount($p->score);?></b>
					<div id="ranking-container-<?=$p->id;?>" class="technique-container">
						<?php $detail = explode(",", $p->detail);?>
						<div class="status-popover-content" style="min-width: 150px;">
							Vitórias PVP: <span class="verde" style="float: right;"><?=highamount($detail[0] * 50);?></span><br />
							Vitórias NPC: <span class="verde" style="float: right;"><?=highamount($detail[1] * 10);?></span><br />
							Graduação: <span class="verde" style="float: right;"><?=highamount($detail[2] * 1000);?></span><br />
							Nível: <span class="verde" style="float: right;"><?=highamount($p->level * 1000);?></span><br />
							Missão de Tempo: <span class="verde" style="float: right;"><?=highamount($detail[3] * 100);?></span><br />
							Missão PVP: <span class="verde" style="float: right;"><?=highamount($detail[4] * 200);?></span><br />
							Missão Diária: <span class="verde" style="float: right;"><?=highamount($detail[5] * 250);?></span><br />
							Missão de Mascote: <span class="verde" style="float: right;"><?=highamount($detail[6] * 50);?></span><br />
							Missão de Combate: <span class="verde" style="float: right;"><?=highamount($detail[7] * 200);?></span><br /><br />
							Total de Pontos: <span class="laranja" style="float: right;"><?=highamount($p->score);?></span>
						</div>
					</div>
				</div>
			</div>
			<div class="button" style="position: relative; top: 15px;">
				<?php if ($player) { ?>
					<a href="<?=make_url('profile#achievements', [
						'player'	=> $p->player_id
					]);?>">
						<img src="<?=image_url('icons/achievements.png')?>" style="margin: 0 5px" data-toggle="tooltip" title="<?=make_tooltip('Ver Conquisstas', 125);?>" />
					</a>
					<?php if ($player->has_vip_item(2114)) { ?>
						<a href="<?=make_url('profile#talents', [
							'player'	=> $p->player_id
						]);?>">
							<img src="<?=image_url('icons/talents.png')?>" style="margin: 0 5px" data-toggle="tooltip" title="<?=make_tooltip('Ver Talentos', 125);?>" />
						</a>
					<?php } ?>
					<?php if ($player->has_vip_item(2115)) { ?>
						<a href="<?=make_url('profile#equipments', [
							'player'	=> $p->player_id
						]);?>">
							<img src="<?=image_url('icons/equipments.png')?>" style="margin: 0 5px" data-toggle="tooltip" title="<?=make_tooltip('Ver Equipamentos', 125);?>" />
						</a>
					<?php } ?>
				<?php } ?>
			</div>
		</div>
	<?php } ?>
	<div class="break"></div>
	<?=partial('shared/paginator', [
    	'pages'		=> $pages,
		'current'	=> $page + 1
	]) ?>
</form>
