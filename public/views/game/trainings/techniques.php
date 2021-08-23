<?php echo partial('shared/title', array('title' => 'techniques.training.title', 'place' => 'techniques.training.title')) ?>
<!-- AASG - Treinamento -->
<ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-6665062829379662"
     data-ad-slot="5650532609"></ins>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({});
</script><br />
<?php if (!sizeof($techniques)): ?>
	<?php echo partial('shared/info', array('id' => 4, 'title' => 'techniques.training.no_technique_title', 'message' => t('techniques.training.no_technique'))) ?>
<?php else: ?>
	<div class="msg-container">
		<div class="msg_top"></div>
		 <div class="msg_repete">
			<div class="msg" style="background:url(<?php echo image_url('msg/'. $player->character()->anime_id . '-2.png')?>); background-repeat: no-repeat;">
			</div>
			<div class="msgb" style="position:relative; margin-left: 231px; text-align: left; top: -37px">
				<b><?php echo t('techniques.training.weekly_limit') ?></b>
				<div class="content">
					<?php echo t('techniques.training.info') ?><br /><br />
					<?php echo exp_bar($player->technique_training_spent, $max_training, 455, $player->technique_training_spent . ' / ' . $max_training) ?>
				</div>
			</div>
		</div>
		<div class="msg_bot"></div>
		<div class="msg_bot2"></div>
	</div>
	<br />
	<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
		<table width="725" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td width="96">&nbsp;</td>
			<td align="center"><?php echo t('techniques.index.header.name') ?></td>
			<td width="140" align="center"><?php echo t('techniques.index.header.enhancements') ?></td>
			<td width="96" align="center"><?php echo t('techniques.training.header.time') ?></td>
			<td width="117" align="center"></td>
		</tr>
		</table>
	</div>
	<table id="learned-technique-list" width="725" border="0" cellpadding="0" cellspacing="0" style="padding-left:5px">
	<?php $counter = 0; ?>
		<?php foreach ($techniques as $technique): ?>
			<?php
				$item		= $technique->item();
				$stats		= $technique->stats();
				$color		= $counter++ % 2 ? '091e30' : '173148';
				//$tooltip	= $item->technique_level_tooltip($player);
			?>
			<tr bgcolor="<?php echo $color ?>" id="learn-status-<?php echo $item->id ?>">
				<td width="96" align="center">
					<img src="<?php echo image_url($item->image(true)) ?>" class="technique-popover" data-source="#technique-content-<?php echo $item->id ?>" data-title="<?php echo $item->description()->name ?>" data-trigger="click" data-placement="right" />
					<div class="technique-container" id="technique-content-<?php echo $item->id ?>">
						<?php echo $item->technique_tooltip() ?>
					</div>
				</td>
				<td align="left">
					<b class="amarelo" style="font-size:14px; position: relative; top: 5px;"><?php echo $item->description()->name ?></b><hr />
					<?php /*if ($technique->level < 5): ?>
						<?php echo exp_bar($stats->exp, $item->exp_needed_for_level(), 250, $stats->exp . ' / ' . $item->exp_needed_for_level()) ?>
						<br /><br />
					<?php endif*/ ?>
				</td>
				<td width="140" align="center">
					<img src="<?php echo image_url('requer.png') ?>" class="requirement-popover" data-source="#requirement-content-<?php echo $item->id ?>" data-title="<?php echo t('popovers.titles.enhancer') ?>" data-trigger="hover" data-placement="left" />
					<div class="requirement-container" id="requirement-content-<?php echo $item->id ?>"><?php echo $tooltip ?></div>
				</td>
				<?php if ($technique->level < 5): ?>
					<td width="96" align="center">
						<select id="technique-training-duration-<?php echo $technique->id ?>" style="width: 96px">
							<option value="1"><?php echo t('techniques.training.time.1') ?></option>
							<option value="2"><?php echo t('techniques.training.time.2') ?></option>
							<option value="3"><?php echo t('techniques.training.time.3') ?></option>
						</select>
					</td>
					<td width="117" align="center">
						<?php if (!$can_train): ?>
							<a class="btn btn-sm btn-primary disabled"><?php echo t('techniques.training.train') ?></a>
						<?php else: ?>
							<input type="button" class="btn btn-sm btn-primary train" value="<?php echo t('techniques.training.train') ?>" data-item="<?php echo $technique->id ?>" />
						<?php endif ?>
					</td>
				<?php else: ?>
					<td colspan="2" align="center">
						<?php echo t('techniques.training.max_level_reached') ?>
					</td>
				<?php endif ?>
			</tr>
			<tr height="4"></tr>
		<?php endforeach ?>
	</table>
<?php endif ?>
