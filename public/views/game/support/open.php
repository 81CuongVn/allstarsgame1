<?php echo partial('shared/title', array('title' => 'support.open.title', 'place' => 'support.open.title')) ?>
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
<?php if (sizeof($errors)): ?>
	<?php
		$messages	= [];

		foreach ($errors as $error) {
			$messages[]	= "<li>" . $error . "</li>";
		}

		echo partial('shared/info', [
			'id'		=> 5,
			'title'		=> 'support.open.error_title',
			'message'	=> "<ul>" . implode('', $messages) . "</ul>"
		]);
	?>
<?php endif ?>
<form id="open-support-ticket-form" method="post" class="form-horizontal" enctype="multipart/form-data">
	<div class="form-group">
		<label class="col-sm-2 control-label"><?php echo t('support.open.form.category') ?></label>
		<div class="col-sm-10">
			<select class="form-control input-sm" name="category">
				<?php foreach ($categories as $c): ?>
					<option value="<?php echo $c->id ?>" <?php echo $c->id == $category ? ' selected="selected"' : '' ?>><?php echo $c->name ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label"><?php echo t('support.open.form.title') ?></label>
		<div class="col-sm-10">
			<input type="text" name="title" value="<?php echo $title ?>" class="form-control input-sm" />
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label"><?php echo t('support.open.form.browser') ?></label>
		<div class="col-sm-10">
			<input type="text" class="form-control input-sm" name="browser" disabled="disabled" />
			<label class="checkbox">
				<input type="checkbox" name="same_browser" checked="checked" value="1" />
				<?php echo t('support.open.form.same_browser') ?>
			</label>
		</div>
	</div>
	<div class="form-group row">
		<label class="control-label col-md-offset-1 col-md-10" style="text-align: left"><?php echo t('support.open.form.description') ?></label>
		<br /><br />
		<div class="col-md-offset-1 col-md-10">
			<textarea name="description" class="form-control input-sm" rows="15"><?php echo $description ?></textarea>
		</div>
	</div>
	<hr />
	<p class="laranja"><?php echo t('support.open.attachment_info') ?></p>
	<br /><br />
	<div class="form-group">
		<label class="col-sm-2 control-label"><?php echo t('support.open.form.attachment') ?></label>
		<div class="col-sm-10">
			<input type="file" name="attachments[]" />
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label"><?php echo t('support.open.form.attachment') ?></label>
		<div class="col-sm-10">
			<input type="file" name="attachments[]" />
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label"><?php echo t('support.open.form.attachment') ?></label>
		<div class="col-sm-10">
			<input type="file" name="attachments[]" />
		</div>
	</div>
	<br />
	<div align="center"><input type="submit" class="btn btn-sm btn-primary" value="<?php echo t('support.open.open') ?>"/></div>
	<div class="clearfix"></div>
</form>
