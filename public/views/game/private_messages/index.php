<?php echo partial('shared/title', array('title' => 'private_messages.index.title', 'place' => 'private_messages.index.title')) ?>
<?php if (FW_ENV != 'dev') { ?>
	<!-- AASG - Mensagens -->
	<ins class="adsbygoogle"
		style="display:inline-block;width:728px;height:90px"
		data-ad-client="ca-pub-6665062829379662"
		data-ad-slot="1807205109"></ins>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
	</script><br />
<?php } ?>
<div>
	<div class="pull-left">
		<a class="btn btn-sm btn-danger" id="private-message-delete-selected"><?php echo t('private_messages.delete_selected') ?></a>
		<a class="btn btn-sm btn-danger" id="private-message-delete-all"><?php echo t('private_messages.delete_all') ?></a>
	</div>
	<div class="pull-right">
		<a class="btn btn-sm btn-primary" id="private-message-compose"><?php echo t('private_messages.compose') ?></a>
	</div>
	<div class="break"></div>
</div>
<br />
<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
	<table width="725" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="60" align="center">&nbsp;</td>
		<td width="140" align="center"><?php echo t('private_messages.header.from') ?></td>
		<td width="180" align="center"><?php echo t('private_messages.header.subject') ?></td>
		<td width="120" align="center"><?php echo t('private_messages.header.when') ?></td>
		<td width="100" align="center">&nbsp;</td>
		<td width="100" align="center">&nbsp;</td>
	</tr>
	</table>
</div>
<div id="private-messages-list" align="center"></div>
