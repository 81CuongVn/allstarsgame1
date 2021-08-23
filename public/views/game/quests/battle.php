<?php echo partial('shared/title', array('title' => 'menus.quests_battle', 'place' => 'menus.quests_battle')) ?>
<!-- AASG - Quests -->
<ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-6665062829379662"
     data-ad-slot="8048824605"></ins>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({});
</script><br />
<?php
	foreach ($quests as $quest):
		$player_quest = CombatQuest::find_first('id='.$quest->combat_quest_id,['cache' => true]);
?>
<div class="ability-speciality-box" data-id="<?php echo $quest->id ?>" style="height: 430px; width: 239px;">
	<div>
		<div class="image"> <img src="<?php echo image_url('combat_quest/'.$quest->period.'.png') ?>" /> </div>
		<div class="name" style="height: 15px;"> <?php echo $player_quest->name_br?> </div>
		<div class="description" style="height: 100px;"> <br />
			<img src="<?=image_url("icons/currency.png");?>" />
			<span class="amarelo_claro" style="font-size: 16px; margin-left: 5px; top: 2px; position: relative"><?=$player_quest->currency;?></span><br />

			<img src="<?=image_url("icons/exp.png");?>" />
			<span class="amarelo_claro" style="font-size: 16px; margin-left: 5px; top: 2px; position: relative"><?=$player_quest->exp;?></span><br />

			<?php if ($player_quest->credits) { ?>
				<img src="<?=image_url("icons/vip-on.png");?>" />
				<span class="amarelo_claro" style="font-size: 16px; margin-left: 5px; top: 2px; position: relative"><?=$player_quest->credits;?></span>
			<?php } ?>
		</div>
		<div class="details text-center">
			<?php
				$player_combat_quest = PlayerBattleStat::find_first("1=1 ORDER BY {$player_quest->type} DESC LIMIT 1");
				$type  = $player_quest->type;
			?>
			<?=$player_combat_quest->character_theme()->first_image()->small_image();?><br /><br />
			<img src="<?=image_url(is_player_online($player_combat_quest->player_id) ? 'on.png' : 'off.png');?>" />
			<b style="font-size:16px"><?=$player_combat_quest->name;?></b><br />
			<?=$player_combat_quest->anime()->description()->name;?><br />
			<?=$player_combat_quest->graduation()->description($player_combat_quest->anime_id)->name;?><br /><br />
			<span class="laranja"><?=($player_combat_quest->$type . ' ' . $player_quest->status_br);?></span>
		</div>
	</div>
</div>
<?php endforeach ?>
<div class="clearfix"></div>
<ul class="nav nav-pills nav-justified" id="quest-list-tabs" style="margin: 10px 0;">
	<li class="active"><a href="#quest-tab-diario"><?php echo t('quests.battle.b1')?></a></li>
	<li><a href="#quest-tab-semanal"><?php echo t('quests.battle.b2')?></a></li>
	<li><a href="#quest-tab-mensal"><?php echo t('quests.battle.b3');?></a></li>
</ul>
<div class="tab-content" id="quest-list-content">
	<?php $first = true; ?>
	<?php foreach ($finish_quests as $finish_quest): ?>
	<table width="730" border="0" cellpadding="0" cellspacing="0" id="quest-tab-<?php echo $finish_quest->period?>" class="tab-pane<?php echo $first ? ' active' : '' ?>">
		<?php $counter = 0; ?>
		<?php $player_quest_combat_finisheds =  PlayerCombatQuest::find("finished=1 AND period='".$finish_quest->period."' ORDER BY data_ins DESC");?>
		<?php foreach ($player_quest_combat_finisheds as $player_quest_combat_finished):
				$p 					 =  RankingPlayer::find_first("player_id=".$player_quest_combat_finished->player_id);
				$quest_combat 		 = CombatQuest::find_first("id=".$player_quest_combat_finished->combat_quest_id);
				$color	= $counter++ % 2 ? '091e30' : '173148';
		?>
		<?php
			if(!$p){
				continue;
			}
		?>
		<tr bgcolor="<?php echo $color ?>">
			<td width="80" align="center"><img src="<?=image_url('factions/icons/big/' . $p->faction_id . ".png");?>" width="60" /></td>
			<td width="80" align="center">
				<?=$p->character_theme()->first_image()->small_image();?>
			</td>
			<td width="180" align="left" style="padding: 10px;">
				<b style="font-size:16px">
					<img src="<?=image_url(is_player_online($p->player_id) ? 'on.png' : 'off.png');?>" />
					<?=$p->name;?>
				</b><br />
				<?=$p->anime()->description()->name;?><br />
				<?=$p->graduation()->description($p->anime_id)->name;?>
			</td>
			<td width="240" align="center" class="laranja" style="padding: 10px;">
				<b style="font-size: 14px"><?=$quest_combat->name_br;?></b>
			</td>
			<td width="150" align="center" class="verde">
				<?=date("d/m/Y", strtotime($player_quest_combat_finished->data_ins)) . " &agrave;s " . date("H:i:s", strtotime($player_quest_combat_finished->data_ins));?>
			</td>
		</tr>
		<tr height="4"></tr>
		<?php $first = false; ?>
		<?php endforeach ?>
	</table>
	<?php endforeach ?>
</div>
