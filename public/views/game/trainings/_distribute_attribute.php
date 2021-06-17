<div class="msg-container">
	<div class="msg_top"></div>
	<div class="msg_repete">
		<div class="msg" style="background:url(<?php echo image_url('msg/'. $player->character()->anime_id . '-3.png')?>); background-repeat: no-repeat;">
		</div>
		<div class="msgb" style="position:relative; margin-left: 231px; text-align: left; top: -37px">
			<b>Pontos Disponíveis</b>
			<div class="content">
				<?php if ($points): ?>
					<?php
					echo t('attributes.distribute.having_points', array(
						'total'	=> $points,
						'lack'	=> $point_exp - $current_exp
					));
					?>
				<?php else: ?>
					<?php echo t('attributes.distribute.no_points') ?>
				<?php endif ?><br /><br />
				<?php /*<?=exp_bar($current_exp, $point_exp, 455, highamount($current_exp) . ' / ' . highamount($point_exp));?><br />
				<span class="laranja"><?=t('attributes.distribute.info');?></span>*/ ?>
			</div>
		</div>
	</div>
	<div class="msg_bot"></div>
	<div class="msg_bot2"></div>
</div>
<br />
<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>"><p><?php echo t('attributes.attributes.headers.title') ?></p></div>
<?php if(sizeof($errors)): ?>
	<div class="alert alert-block alert-danger" style="margin: 10px 20px">
		<a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>
		<h4><?php echo t('attributes.distribute.errors.header') ?></h4>
		<ul>
			<?php foreach ($errors as $error): ?>
				<li><?php echo $error ?></li>
			<?php endforeach ?>
		</ul>
	</div>
<?php endif; ?>
<div style="width: 100%;">
	<?php foreach ($attributes as $_ => $attribute): ?>
		<?php
		if ($_ == "for_inc_crit") {
			$attrPoints = $attrRate['for_crit_inc'];
		} elseif ($_=="for_inc_abs") {
			$attrPoints = $attrRate['for_abs_inc'];
		} else {
			$attrPoints = $attrRate[$_];
		}
		?>
		<div class="ability-speciality-box Ataque" style="width: 178px !important; height: auto !important; padding-bottom: 40px">
			<div class="image">
				<img src="<?php echo image_url('icons/' . str_replace('_trained', '', $_) . '.png') ?>" class="requirement-popover" data-source="#attribute-tooltip-<?php echo $_ ?>" data-title="<?php echo t('formula.tooltip.title.' . $_) ?>" data-trigger="hover" data-placement="bottom" />
				<div id="attribute-tooltip-<?php echo $_ ?>" class="status-popover-container">
					<div class="status-popover-content"><?php echo t('formula.tooltip.description.' . $_, ['mana' => t('formula.for_mana.' . $player->character()->anime_id)]) ?></div>
				</div>
			</div>
			<div class="name" style="height: 60px !important;">
				<span class="amarelo"><?php echo $attribute ?></span><br>
				<span class="verde" style="font-size: 11px"><?php echo t('formula.tooltip.description2.' . $_, [
					'points'	=> $attrPoints
				]) ?></span>
			</div>
			<div class="description" style="height: auto; font-size:11px">
				<?php echo exp_bar($player->{$_} / $attrPoints, $max, 145);	?>
			</div>
			<div class="details text-center">
				<?php if ($points): ?>
					<select name="<?php echo str_replace('_trained', '', $_) ?>_val" class="form-control input-sm" data-default="<?php echo t('attributes.distribute.select') ?>">
						<option value="0"><?php echo t('attributes.distribute.select') ?></option>
						<?php for($i = 1; $i <= $points; $i++): ?>
							<option value="<?php echo $i ?>"><?php echo $i ?></option>
						<?php endfor; ?>
					</select>
				<?php else: ?>
					--
				<?php endif ?>
			</div>
			<div class="button" style="position:relative; top: 15px;">
				<?php if ($points): ?>
					<a class="btn btn-sm btn-primary distribute" data-attribute="<?php echo str_replace('_trained', '', $_) ?>"><?php echo t('attributes.distribute.distribute') ?></a>
				<?php else: ?>
					<a class="btn btn-sm btn-primary disabled"><?php echo t('attributes.distribute.distribute') ?></a>
				<?php endif ?>
			</div>
		</div>
	<?php endforeach; ?>
	<div class="break"></div>
</div>
<div class="text-center" style="margin-top: 15px;">
	<?php if ($points): ?>
		<a class="btn btn-sm btn-primary distribute-general" data-max="<?php echo $points ?>"><?php echo t('attributes.distribute.distribute_general') ?></a>
	<?php else: ?>
		<a class="btn btn-sm btn-primary disabled"><?php echo t('attributes.distribute.distribute_general') ?></a>
	<?php endif ?>
</div>
