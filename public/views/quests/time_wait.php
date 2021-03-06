<?=partial('shared/title', [
	'title'	=> 'quests.time.wait.title',
	'place'	=> 'quests.time.wait.title'
]);?>
<div class="msg-container">
	<div class="msg_top"></div>	
	 <div class="msg_repete">
		<div class="msg" style="background:url(<?=image_url('msg/'. $player->character()->anime_id . '-3.png');?>); background-repeat: no-repeat;">
		</div>
		<div class="msgb" style="position:relative; margin-left: 231px; text-align: left; top: -37px">
			<b><?=$quest->description()->name;?></b>
			<div class="content"><?=$quest->description()->description;?><hr />
				<?php if ($can_finish) { ?>
					<h3>
						<?=t('quests.finish_text', [
							'exp'			=> $duration->exp + percent($effects['bonus_exp_mission_percent'], $duration->exp) + $effects['bonus_exp_mission'] + percent($extras->exp_quest, $duration->exp) ,
							'currency'		=> $duration->currency + percent($effects['bonus_gold_mission_percent'], $duration->currency) + $effects['bonus_gold_mission'] + percent($extras->currency_quest, $duration->currency),
							'currency_name'	=> t('currencies.' . $player->character()->anime_id)
						]);?>
						<?php if ($player_quest->reward_pet_id) { ?>
							<?=t('quests.finish_pet_text', [
								'pet'	=> Item::find($player_quest->reward_pet_id)->description()->name
							]);?>
						<?php } ?>
						<?php if ($player_quest->reward_equipment) { ?>
							<?=t('quests.finish_equipment_text');?>
						<?php } ?>
					</h3>
					<a id="timer-quest-finish" class="btn btn-primary" href="javascript:;"><?=t('quests.finish');?></a>
				<?php } else { ?>
					<?=t('quests.time.time_left');?> <span class="quest-timer-container">--:--:--</span>
					<script type="text/javascript">
						$(document).ready(function () {
							create_timer(<?=$diff['hours'];?>, <?=$diff['minutes'];?>, <?=$diff['seconds'];?>, 'quest-timer-container', null, null, true);
						});
					</script>
				<?php } ?>
			</div>
		</div>		
	</div>
	<div class="msg_bot"></div>
	<div class="msg_bot2"></div>
</div>
<?php if (!$can_finish) { ?><br />
	<div align="center">
		<a id="timer-quest-cancel" class="btn btn-danger" href="javascript:;"><?php echo t('quests.cancel') ?></a>
	</div>	
<?php } ?>