<?php echo partial('shared/title', array('title' => 'rankings.account.title', 'place' => 'rankings.account.title')) ?>
<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
	<p>Filtro do Ranking</p>
</div>
<form id="ranking-players-filter-form" method="post">
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="filtros">
		<tr>
			<td width="556" align="center">
				<b><?php echo t('rankings.players.header.nome') ?></b><br />
				<input type="text" name="name" class="form-control" value="<?php echo $name ?>" style="width:400px" />
			</td>
			<td width="169" align="center">
				<a href="javascript:;" class="btn btn-primary filter" style="margin-top: 14px"><?php echo t('buttons.filtrar') ?></a>
			</td>
		</tr>
	</table>
	<br />
	<br />
	<input type="hidden" name="page" value="<?php echo $page ?>" />
	<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
		<table width="725" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td width="40">&nbsp;</td>
			<td width="100" align="center"><?php echo t('rankings.players.header.posicao') ?></td>
			<td width="350" align="center"><?php echo t('rankings.players.header.nome') ?></td>
			<td width="80" align="center"><?php echo t('rankings.players.header.level') ?></td>
			<td width="100" align="center"><?php echo t('rankings.players.header.score') ?></td>
		</tr>
		</table>
	</div>
	<table width="725" border="0" cellpadding="0" cellspacing="0">
		<?php $counter = 0; ?>
		<?php foreach ($players as $p): 
				$color	= $counter++ % 2 ? '091e30' : '173148';
		?>
			<tr bgcolor="<?php echo $color ?>" height="30">
				<td width="40" align="center">
				</td>
				<td width="100" align="center">
						<b style="font-size:16px"><?php echo highamount($p->position_general) ?>º</b>
				</td>
				<td width="350"><b style="font-size:16px">
					<?php echo $p->name ?></b>
				</td>
				<td width="80" align="center"><?php echo highamount($p->level) ?></td>
				<td width="100" align="center"><?php echo highamount($p->score) ?></td>
			</tr>
			<tr height="4"></tr>
		<?php endforeach ?>
	</table>
	<?php echo partial('shared/paginator', ['pages' => $pages, 'current' => $page + 1]) ?>
</form>