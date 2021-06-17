<div class="msg-container">
	<div class="msg_top"></div>	
	 <div class="msg_repete">
		<div class="msg" style="background:url(<?=image_url('msg/' . $player->character()->anime_id . '-2.png');?>); background-repeat: no-repeat;"></div>
		<div class="msgb" style="position:relative; margin-left: 231px; text-align: left; top: -37px">
			<b><?=t('attributes.attributes.weekly_limit');?></b>
			<div class="content" id="basic-training-info-container">
				<?=t('attributes.attributes.info');?><br /><br />
				<?=exp_bar(
					$player->weekly_points_spent,
					$player->max_attribute_training(),
					455,
					highamount($player->weekly_points_spent) . ' / ' . highamount($player->max_attribute_training())
				);?>
				<?php if ($_POST) { ?>
					<div id="basic-training-complete">
						<br />
						<b><?=t('attributes.attributes.info_finished');?></b><br /><br />
						<span style="font-weight: bold;"><?=t('attributes.attributes.you_won');?></span>
						<span class="verde exp"><?=t('attributes.attributes.exp', [
							'exp'	=> highamount($earn_exp)
						]);?></span> e
						<span class="verde points"><?=t('attributes.attributes.points', [
							'points'	=> highamount($earn_points)
						]);?></span>.<br />
						<span style="font-weight: bold;"><?=t('attributes.attributes.you_spent');?></span>
						<span class="vermelho spent-stamina"><?=highamount($spent_stamina);?></span>
						<img width="16" src="<?=image_url('icons/for_stamina.png');?>" />
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
	<div class="msg_bot"></div>	
	<div class="msg_bot2"></div>	
</div>