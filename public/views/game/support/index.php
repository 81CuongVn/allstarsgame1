<?php echo partial('shared/title', array('title' => 'support.index.title', 'place' => 'support.index.title')) ?>
<?php if (FW_ENV != 'dev') { ?>
	<!-- AASG - Suporte -->
	<ins class="adsbygoogle"
		style="display:inline-block;width:728px;height:90px"
		data-ad-client="ca-pub-6665062829379662"
		data-ad-slot="6963614279"></ins>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
	</script><br />
<?php } ?>
<?php echo partial('shared/info', [
		'id'		=> 5,
		'title'		=> 'support.open.info_box.title',
		'message'	=> t('support.open.info_box.text', ['url' => make_url('support#open')])
	]);
?><br />
<?php if($_SESSION['universal']): ?>
<form id="f-support-search" onsubmit="return false">
	<input type="hidden" name="search" value="1" />
	<input type="hidden" name="page" value="0" />
	<table width="730">
		<tr >
			<td align="center" valign="top">
				<b style="font-size:14px">ID</b><br />
				<input type="text" name="id" size="10" class="form-control input-sm" style="width: auto" />
			</td>
			<td align="center" valign="top"><b style="font-size:14px">
				<?php echo t('support.title') ?></b><br />
				<input type="text" name="title" size="25" class="form-control input-sm" style="width: auto" />
			</td>
			<td	align="center" valign="top"><b style="font-size:14px">
				<?php echo t('support.category') ?></b><br />
				<select name="category" class="form-control input-sm" style="width: auto">
					<option value=""><?php echo t('global.all') ?></option>
					<?php foreach ($categories as $category): ?>
						<option value="<?php echo $category->id ?>"><?php echo $category->name ?></option>
					<?php endforeach ?>
				</select>
			</td>
			<td	align="center" valign="top">
				<b style="font-size:16px">Status</b><br />
				<select name="status" class="form-control input-sm" style="width: auto">
					<option value="" selected><?php echo t('global.all') ?></option>
					<?php foreach ($statuses as $status): ?>
						<option value="<?php echo $status->id ?>"><?php echo $status->name ?></option>
					<?php endforeach ?>
				</select>
			</td>
			<td	align="center">
				<input type="submit" value="<?=t('support.filter');?>" class="btn btn-sm btn-primary" style="margin-top: 15px;"/>
			</td>
		</tr>
	</table>
</form>
<?php else: ?>
<form id="f-support-search">
	<input type="hidden" name="search" value="1" />
	<input type="hidden" name="page" value="0" />
</form>
<?php endif ?>
<br />
<div id="support-ticket-list"></div>
