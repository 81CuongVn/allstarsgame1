<?php echo partial('shared/title', array('title' => 'quests.time.title', 'place' => 'quests.time.title')); ?>
<?php if (FW_ENV != 'dev') { ?>
	<!-- AASG - Quests -->
	<ins class="adsbygoogle"
		style="display:inline-block;width:728px;height:90px"
		data-ad-client="ca-pub-6665062829379662"
		data-ad-slot="8048824605"></ins>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
	</script><br />
<?php } ?>
<?php if (!$player_tutorial->missoes_tempo) { ?>
	<script type="text/javascript">
		$(function () {
			var tour = new Tour({
				backdrop: true,
				page: 12,
				steps: [{
					element: "#time-quests-list-tabs",
					title: "Tempo para Descansar",
					content: "Ao aceitar uma Missão de Tempo, você não poderá mais ir lutar até o tempo da Missão acabar, então use esse tempo para dar uma pausa e ir descansar!",
					placement: "top"
				}, {
					element: "#time-quests-list-content",
					title: "Conseguindo Recompensas",
					content: "Ao completar sua primeira Missão, você receberá um Equipamento que poderá ser equipado no menu Personagem > Equipamentos! Fique de olho das recompensas de cada missão!",
					placement: "top"
				}]
			});

			tour.restart();
			tour.init(true);
			tour.start(true);
		});
	</script>
<?php } ?>

<div class="barra-secao barra-secao-<?=$player->character()->anime_id;?>">
	<table width="725" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td width="355" align="center"><?=t('quests.time.header.quest');?></td>
			<td width="75" align="center">Nível</td>
			<td width="110" align="center"><?=t('quests.time.header.duration');?></td>
			<td width="110" align="center"><?=t('quests.time.header.reward');?></td>
			<td width="100" align="center">&nbsp;</td>
		</tr>
	</table>
</div>
<div id="time-quests-list-content">
	<table width="725" border="0" cellpadding="0" cellspacing="0" class="table table-striped">
		<?php
		foreach ($quests as $quest) {
			if ($quest->req_level > $player->level || in_array($quest->id, $player_quests))
				continue;

			$expReward		= $quest->durations()[0]->exp + percent($effects['bonus_exp_mission_percent'], $quest->durations()[0]->exp) + $effects['bonus_exp_mission'] + percent($extras->exp_quest, $quest->durations()[0]->exp);
			$coinsReward	= $quest->durations()[0]->currency + percent($effects['bonus_gold_mission_percent'], $quest->durations()[0]->currency) + $effects['bonus_gold_mission'] + percent($extras->currency_quest, $quest->durations()[0]->currency);
		?>
			<tr>
				<td width="355" align="left">
					<div class="name"><b class="amarelo" style="font-size:14px; position: relative; top: 5px;"><?=$quest->description()->name;?></b></div><hr />
					<div class="description"><?=$quest->description()->description;?></div>
				</td>
				<td width="75" align="center">
					<span style="background-color: #1c3c53; font-size: 16px; padding: 9px; border-radius: 3px;">
						<?=$quest->req_level;?>
					</span>
				</td>
				<td width="110" align="center">
					<select name="duration" class="duration-selector form-control input-sm" style="width: auto;" data-id="<?=$quest->id;?>" id="quest-time-duration-selector-<?=$quest->id;?>">
						<?php
						foreach ($quest->durations() as $duration) {
							$expDurationReward		= $duration->exp + percent($effects['bonus_exp_mission_percent'], $duration->exp) + $effects['bonus_exp_mission'] + percent($extras->exp_quest, $duration->exp);
							$coinsDurationReward	= $duration->currency + percent($effects['bonus_gold_mission_percent'], $duration->currency) + $effects['bonus_gold_mission'] + percent($extras->currency_quest, $duration->currency);
						?>
							<option data-currency="<?=$coinsDurationReward;?>" data-exp="<?=$expDurationReward;?>" value="<?=$duration->multiplier;?>"><?=$duration->time['string'];?></option>
						<?php } ?>
					</select>
				</td>
				<td width="110" align="center" id="time-quest-reward-<?=$quest->id;?>" class="verde">
					<span class="exp"><?=highamount($expReward);?></span> Exp<br />
					<span class="currency"><?=highamount($coinsReward);?></span> <?=t('currencies.' . $player->character()->anime_id);?><br />

					<?php if ($quest->random_equipment_chance): ?>
						<span class="random-equipment"><?=t('quests.equipment_chance', [
							'chance' => $quest->random_equipment_chance
						]);?></span><br />
					<?php endif ?>
					<?php if ($quest->random_pet_chance && !$quest->reward_item_id): ?>
						<span class="random-pet"><?=t('quests.random_pet_chance', [
							'chance' => $quest->random_pet_chance
						]);?></span><br />
					<?php
					elseif($quest->random_pet_chance && $quest->reward_item_id):
						$pet	= Item::find($quest->reward_item_id);
					?>
						<span class="specific-pet"><?=t('quests.specific_pet_chance', [
							'chance'	=> $quest->random_pet_chance,
							'pet'		=> $pet->description()->name
						]);?></span>
					<?php endif ?>
				</td>
				<td width="100" align="center">
					<?php if (!in_array($quest->id, $player_quests)) { ?>
						<?php if ($quest->req_level <= $player->level) { ?>
							<a href="javascript:;" class="accept btn btn-sm btn-primary" data-id="<?=$quest->id;?>"><?=t('quests.accept');?></a>
						<?php } else { ?>
							<a href="javascript:;" class="disabled btn btn-sm btn-primary" data-id="<?=$quest->id;?>"><?=t('quests.accept');?></a>
						<?php } ?>
					<?php } else { ?>
						<a href="javascript:;" class="disabled btn btn-sm btn-success"><?=t('quests.completed');?></a>
					<?php } ?>
				</td>
			</tr>
		<?php } ?>
	</table>
</div>
