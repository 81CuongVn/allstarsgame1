<?php echo partial('shared/title', array('title' => 'guilds.search.title', 'place' => 'guilds.search.title')) ?>
<!-- AASG - Guild -->
<ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-6665062829379662"
     data-ad-slot="7693601385"></ins>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({});
</script><br />
<div class="msg-container">
	<div class="msg_top"></div>
	 <div class="msg_repete">
		<div class="msg" style="background:url(<?php echo image_url('msg/'. $player->character()->anime_id . '-1.png')?>); background-repeat: no-repeat;">
		</div>
		<div class="msgb" style="position:relative; margin-left: 231px; text-align: left; top: -37px">
			<b><?php echo t('guilds.search.create') ?></b>
			<div class="content">
				<form class="form-horizontal" id="f-create-guild">
					<div class="form-group">
						<label class="col-sm-2 control-label"><?php echo t('guilds.create.name') ?></label>
						<div class="col-sm-10">
							<input type="text" name="name" class="form-control input-sm" />
							<p><?php echo t('guilds.create.name_note') ?></p>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<div class="checkbox" style="float:left">
								<label>
									<input type="radio" value="1" name="creation_mode" /> <?php echo t('guilds.create.normal', ['level' => $min_level, 'price' => $currency_price]) ?>
								</label>
							</div>
							<div class="checkbox" style="float:left">
								<label>
									<input type="radio" value="2" name="creation_mode" /> <?php echo t('guilds.create.credits', ['credits' => $credits_price]) ?>
								</label>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<input type="submit" class="btn btn-sm btn-primary" value="<?php echo t('guilds.create.submit') ?>" />
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
			<b><?php echo t('guilds.search.do_search') ?></b>
			<div class="content">
				<?php echo t('guilds.search.search_text') ?><br />
				<form class="form-horizontal" id="f-search-guild">
					<div class="form-group">
						<label class="col-sm-2 control-label"><?php echo t('guilds.search.by_name') ?></label>
						<div class="col-sm-10">
							<input type="text" name="name" class="form-control input-sm" />
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<input type="submit" class="btn btn-sm btn-primary" value="<?php echo t('guilds.search.submit') ?>" />
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="msg_bot"></div>
	<div class="msg_bot2"></div>
</div>
<div id="guild-search-results"></div>
