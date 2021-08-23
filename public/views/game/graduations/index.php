<?php echo partial('shared/title', array('title' => 'graduations.index.title', 'place' => 'graduations.index.title')) ?>
<!-- AASG - Graduação -->
<ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-6665062829379662"
     data-ad-slot="3503430151"></ins>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({});
</script><br />
<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
	<table width="725" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="15">&nbsp;</td>
		<td width="355" align="center">Nome / Descrição</td>
		<td width="140" align="center">Requerimentos</td>
		<td width="117" align="center">Status</td>
	</tr>
	</table>
</div>
<table id="graduation-list" width="725" border="0" cellpadding="0" cellspacing="0" style="padding-left:5px">
<?php $counter = 0; ?>
<?php foreach ($graduations as $graduation): ?>
	<?php
		$color	= $counter++ % 2 ? '091e30' : '173148';
		extract($graduation->has_requirement($player));
	?>
	<tr bgcolor="<?php echo $color ?>">
		<td width="15">&nbsp;</td>
		<td width="355" align="left">
			<b class="amarelo" style="font-size:14px; position: relative; top: 5px;"><?php echo $graduation->description($player->character()->anime_id)->name ?></b><hr />
			<span><?php echo $graduation->description($player->character()->anime_id)->description ?></span>
			<br /><br />
		</td>
		<td width="140" align="center">
			<img src="<?php echo image_url('requer.png') ?>" class="requirement-popover" data-source="#requirement-content-<?php echo $graduation->id ?>" data-title="<?php echo t('popovers.titles.requirements') ?>" data-trigger="hover" data-placement="left" />
			<div class="requirement-container" id="requirement-content-<?php echo $graduation->id ?>"><?php echo $requirement_log ?></div>
		</td>
		<td width="117" align="center">
			<?php if ($player->graduation_id >= $graduation->id): ?>
				<a class="btn btn-sm btn-success disabled"><b class="glyphicon glyphicon-ok"></b><?php echo t('graduations.index.graduated') ?></a>
			<?php else: ?>
				<?php if (!$has_requirement): ?>
					<a class="btn btn-sm btn-primary disabled"><?php echo t('graduations.index.graduate') ?></a>
				<?php else: ?>
					<input type="button" class="btn btn-sm btn-primary graduate" value="<?php echo t('graduations.index.graduate') ?>" data-id="<?php echo $graduation->id ?>" />
				<?php endif ?>
			<?php endif ?>
		</td>
	</tr>
	<tr height="4"></tr>
<?php endforeach ?>
</table>
