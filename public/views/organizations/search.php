<?php echo partial('shared/title', array('title' => 'organizations.search.title', 'place' => 'organizations.search.title')) ?>
<div class="msg-container">
	<div class="msg_top"></div>
	 <div class="msg_repete">
		<div class="msg" style="background:url(<?php echo image_url('msg/'. $player->character()->anime_id . '-1.png')?>); background-repeat: no-repeat;">
		</div>
		<div class="msgb" style="position:relative; margin-left: 231px; text-align: left; top: -37px">
			<b><?php echo t('organizations.search.create') ?></b>
			<div class="content">
				<form class="form-horizontal" id="f-create-organization">
					<div class="form-group">
						<label class="col-sm-2 control-label"><?php echo t('organizations.create.name') ?></label>
						<div class="col-sm-10">
							<input type="text" name="name" class="form-control input-sm" />
							<p><?php echo t('organizations.create.name_note') ?></p>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<div class="checkbox" style="float:left">
								<label>
									<input type="radio" value="1" name="creation_mode" /> <?php echo t('organizations.create.normal', ['level' => $min_level, 'price' => $currency_price]) ?>
								</label>
							</div>
							<div class="checkbox" style="float:left">
								<label>
									<input type="radio" value="2" name="creation_mode" /> <?php echo t('organizations.create.credits', ['credits' => $credits_price]) ?>
								</label>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<input type="submit" class="btn btn-sm btn-primary" value="<?php echo t('organizations.create.submit') ?>" />
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="msg_bot"></div>	
	<div class="msg_bot2"></div>
</div>
<br />
<div class="msg-container">
	<div class="msg_top"></div>
	 <div class="msg_repete">
		<div class="msg" style="background:url(<?php echo image_url('msg/'. $player->character()->anime_id . '-2.png')?>); background-repeat: no-repeat;">
		</div>
		<div class="msgb" style="position:relative; margin-left: 231px; text-align: left; top: -37px">
			<b><?php echo t('organizations.search.do_search') ?></b>
			<div class="content">
				<?php echo t('organizations.search.search_text') ?><br />
				<form class="form-horizontal" id="f-search-organization">
					<div class="form-group">
						<label class="col-sm-2 control-label"><?php echo t('organizations.search.by_name') ?></label>
						<div class="col-sm-10">
							<input type="text" name="name" class="form-control input-sm" />
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<input type="submit" class="btn btn-sm btn-primary" value="<?php echo t('organizations.search.submit') ?>" />
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="msg_bot"></div>	
	<div class="msg_bot2"></div>
</div>
<div id="organization-search-results"></div>