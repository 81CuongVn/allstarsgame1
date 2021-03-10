<?php echo partial('shared/title', array('title' => 'quests.pvp.title', 'place' => 'quests.pvp.title')) ?>
<?php if(!$player_tutorial->missoes_pvp){?>
<script>
$(function () {
	 $("#conteudo.with-player").css("z-index", 'initial');
	 $(".info").css("z-index", 'initial');
	 $("#background-topo2").css("z-index", 'initial');
	
    var tour = new Tour({
	  backdrop: true,
	  page: 13,
	 
	  steps: [
	  {
		element: "#time-quests-list-tabs",
		title: "Testando suas Capacidades",
		content: "Aceite essas Missões e vença batalhas contra outros jogadores com determinadas condições, como sem usar Modificadores, Habilidades e Especialidades! Essas Missões resetam todo dia 1 e 15, então faça o quanto antes!",
		placement: "top"
	  }
	]});
	//Renicia o Tour
	tour.restart();
	
	// Initialize the tour
	tour.init(true);
	
	// Start the tour
	tour.start(true);
	
});
</script>	
<?php }?>
<ul class="nav nav-pills" id="time-quests-list-tabs">
	<?php $first = true; ?>
	<?php foreach ($graduations as $graduation): ?>
		<li <?php echo $first ? 'class="active"' : '' ?>><a href="#quest-graduation-tab-<?php echo $graduation->id ?>"><?php echo $graduation->description($player->character()->anime_id)->name ?></a></li>
		<?php $first = false; ?>
	<?php endforeach ?>
</ul>
<br />
<div class="tab-content" id="pvp-quests-list-content">
	<?php $first = true; ?>
	<?php foreach ($graduations as $graduation): ?>
		<div class="tab-pane<?php echo $first ? ' active' : '' ?>" id="quest-graduation-tab-<?php echo $graduation->id ?>">
			<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
				<table width="725" border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td width="355" align="center"><?php echo t('quests.pvp.header.quest') ?></td>
						<td width="75" align="center">Nível</td>
						<td width="110" align="center"><?php echo t('quests.pvp.header.reward') ?></td>
						<td width="100" align="center">&nbsp;</td>
					</tr>
				</table>
			</div>
			<table width="725" border="0" cellpadding="0" cellspacing="0" class="table table-striped" >
				<?php foreach ($quests as $quest): ?>
					<?php if ($quest->req_graduation_sorting != $graduation->sorting) { continue; } ?>
					<tr>
						<td width="355">
							<div class="name"><b class="amarelo" style="font-size:14px; position: relative; top: 5px;"><?php echo $quest->description()->name ?></b></div>
							<hr />
							<ul>
								<?php if ($quest->req_same_level): ?>
									<li><?php echo t('quests.pvp.conditions.req_same_level', ['count' => $quest->req_same_level]) ?></li>
								<?php endif ?>
								<?php if ($quest->req_low_level): ?>
									<li><?php echo t('quests.pvp.conditions.req_low_level', ['count' => $quest->req_low_level]) ?></li>
								<?php endif ?>
								<?php if ($quest->req_kill_wo_amplifier): ?>
									<li><?php echo t('quests.pvp.conditions.req_kill_wo_amplifier', ['count' => $quest->req_kill_wo_amplifier]) ?></li>
								<?php endif ?>
								<?php if ($quest->req_kill_wo_buff): ?>
									<li><?php echo t('quests.pvp.conditions.req_kill_wo_buff', ['count' => $quest->req_kill_wo_buff]) ?></li>
								<?php endif ?>
								<?php if ($quest->req_kill_wo_ability): ?>
									<li><?php echo t('quests.pvp.conditions.req_kill_wo_ability', ['count' => $quest->req_kill_wo_ability]) ?></li>
								<?php endif ?>
								<?php if ($quest->req_kill_wo_speciality): ?>
									<li><?php echo t('quests.pvp.conditions.req_kill_wo_speciality', ['count' => $quest->req_kill_wo_speciality]) ?></li>
								<?php endif ?>
							</ul>
						</td>
						
						<td width="75" align="center" style="display: table-cell; vertical-align: middle;">
							<span style="background-color: #1c3c53; font-size: 16px; padding: 9px; border-radius: 3px;">
								<?php echo $quest->req_level ?>
							</span>
						</td>
						<td width="110" align="center" class="verde" style="display: table-cell; vertical-align: middle;">
							<span class="exp"><?php echo highamount($quest->exp() + percent($effects['bonus_exp_mission_percent'], $quest->exp()) + $effects['bonus_exp_mission']) ?></span> Exp<br />
							<span class="currency"><?php echo highamount($quest->currency() + percent($effects['bonus_gold_mission_percent'], $quest->currency()) + $effects['bonus_gold_mission']) ?></span> <?php echo t('currencies.' . $player->character()->anime_id) ?><br />

							<?php if ($quest->random_equipment_chance): ?>
								<span class="random-equipment"><?php echo t('quests.equipment_chance', ['chance' => $quest->random_equipment_chance]) ?></span><br />
							<?php endif ?>

							<?php if ($quest->random_pet_chance && !$quest->reward_item_id): ?>
								<span class="random-pet"><?php echo t('quests.random_pet_chance', ['chance' => $quest->random_pet_chance]) ?></span><br />
							<?php elseif($quest->random_pet_chance && $quest->reward_item_id): ?>
								<?php
									$pet	= Item::find($quest->reward_item_id);
								?>
								<span class="specific-pet"><?php echo t('quests.specific_pet_chance', ['chance' => $quest->random_pet_chance, 'pet' => $pet->description()->name]) ?></span>
							<?php endif ?>
						</td>
						<td width="100" align="center" style="display: table-cell; vertical-align: middle;">
							<?php if (!in_array($quest->id, $player_quests)): ?>
								<?php if ($quest->req_graduation_sorting <= $player->graduation()->sorting && $quest->req_level <= $player->level): ?>
									<a href="javascript:;" class="accept btn btn-primary" data-id="<?php echo $quest->id ?>"><?php echo t('quests.accept') ?></a>									
								<?php else: ?>
									<a href="javascript:;" class="disabled btn btn-primary" data-id="<?php echo $quest->id ?>"><?php echo t('quests.accept') ?></a>									
								<?php endif ?>
							<?php else: ?>
								<a href="javascript:;" class="disabled btn btn-success"><?php echo t('quests.completed') ?></a>
							<?php endif ?>
						</td>
					</tr>
				<?php endforeach ?>
			</table>
		</div>
	<?php $first = false; ?>
	<?php endforeach; ?>
</div>