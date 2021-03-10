<br />
<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
	<table width="730" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="30">&nbsp;</td>
		<td width="200" align="center">Nome</td>
		<td width="100" align="center">Líder</td>
		<td width="100" align="center">Membros</td>
		<td align="center" width="300">Status</td>
	</tr>
	</table>
</div>
<table width="730" border="0" cellpadding="0" cellspacing="0">
	<?php if (sizeof($organizations)): ?>
		<?php $counter = 0; ?>
		<?php foreach ($organizations as $organization): 
				$color	= $counter++ % 2 ? '091e30' : '173148';
		?>
			<tr id="organization-search-item-<?php echo $organization->id ?>" height="50" bgcolor="<?php echo $color ?>">
				<td width="30">&nbsp;</td>
				<td width="200" align="center"><span style="font-size:14px" class="amarelo"><?php echo $organization->name ?></span></td>
				<td width="100" align="center" class="azul">
					<span style="font-size:13px"><?php echo $organization->leader()->name ?></span>
				</td>
				<td width="100" align="center">
					<?php echo highamount($organization->member_count); ?>
				</td>
				<td width="300" align="center">
					<a class="btn btn-primary details" data-id="<?php echo $organization->id ?>"><?php echo t('organizations.search.details') ?></a>
					<?php
						$request	= OrganizationRequest::find_first('player_id=' . $player->id . ' AND organization_id=' . $organization->id);
					?>
					<a class="btn btn-primary join <?php echo $request ? 'disabled' : '' ?>" data-id="<?php echo $organization->id ?>"><?php echo t('organizations.search.join') ?></a>
				</td>
			</tr>
		<?php endforeach ?>
	<?php else: ?>
		<?php echo t('organizations.search.nothing') ?>
	<?php endif ?>
</table>