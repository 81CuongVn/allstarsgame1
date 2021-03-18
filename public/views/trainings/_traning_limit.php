<div class="msg-container">
	<div class="msg_top"></div>	
	 <div class="msg_repete">
		<div class="msg" style="background:url(<?php echo image_url('msg/'. $player->character()->anime_id . '-2.png')?>); background-repeat: no-repeat;">
		</div>
		<div class="msgb" style="position:relative; margin-left: 231px; text-align: left; top: -37px">
			<b><?php echo t('attributes.attributes.weekly_limit') ?></b>
			<div class="content" id="basic-training-info-container">
				<?php echo t('attributes.attributes.info') ?><br /><br />
				<?php echo exp_bar($player->weekly_points_spent, $player->max_attribute_training(), 455, $player->weekly_points_spent . ' / ' . $player->max_attribute_training()) ?>
				<?php if ($_POST): ?>
					<div id="basic-training-complete">
						<br />
						<b><?php echo t('attributes.attributes.info_finished') ?></b><br /><br />
						<span style="font-weight: bold;"><?php echo t('attributes.attributes.you_won') ?></span>
						<span class="verde exp"><?php echo t('attributes.attributes.exp', ['exp' => $earn_exp]) ?></span> e
						<span class="verde points"><?php echo t('attributes.attributes.points', ['points' => $earn_points]) ?></span>.<br />
						<span style="font-weight: bold;"><?php echo t('attributes.attributes.you_spent') ?></span>
						<!-- <span class="vermelho spent-mana"><?php echo $spent_mana ?></span>
						<img width="16" src="<?php echo image_url('icons/for_mana.png') ?>" /> -->
						<span class="vermelho spent-stamina"><?php echo $spent_stamina ?></span>
						<img width="16" src="<?php echo image_url('icons/for_stamina.png') ?>" />
					</div>
				<?php endif ?>
			</div>	
		</div>		
	</div>
	<div class="msg_bot"></div>	
	<div class="msg_bot2"></div>	
</div>

<br />
<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
	<p>Treinamento Manual</p>
</div>
<form id="training-attribute-basic">
	<table width="725" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td align="center" width="325">
				<p align="center">Quando a barra de completar, você receberá um ponto para distribuir.</p>
				<div align="center"><?=exp_bar($player->training_to_next_point(true), $player->training_to_next_point(), 350, $player->training_to_next_point(true) . ' / ' . $player->training_to_next_point());?></div>
			</td>
			<td align="center" width="250">
				<img src="<?=image_url("icons/for_stamina.png");?>" />
				<select class="form-control input-sm" style="width: auto; display: inline-block;" name="stamina" <?=($player->for_stamina() < 1 ? 'disabled' : '')?>>
					<?php for($i = 1; $i <= $player->for_stamina(true); $i++): ?>
						<option value="<?php echo $i ?>"><?php echo $i ?></option>
					<?php endfor ?>
				</select>
			</td>
			<td align="center" width="150">
				<?php if ($player->training_points_spent < $player->max_attribute_training() && $player->for_stamina() > 0): ?>
					<a class="btn btn-sm btn-primary train"><?php echo t('attributes.attributes.train') ?></a>
				<?php else: ?>
					<a class="btn btn-sm btn-danger disabled"><?php echo t('attributes.attributes.train') ?></a>
				<?php endif ?>
			</td>
		</tr>
	</table>
</form>