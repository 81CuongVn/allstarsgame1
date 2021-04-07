<div id="finished-message"></div>
<div id="battle-container" data-target="<?=$target_url;?>" data-mana-player="<?=t('formula.for_mana.' . $player->character()->anime_id);?>" data-mana-enemy="<?=t('formula.for_mana.' . $enemy->character()->anime_id);?>" <?php if ($player->battle_pvp_id) { ?>data-ping="<?=$player->id;?>"<?php } ?>>
	<?php if (!$player->battle_npc_id) { ?>
		<?php if ($player->no_talent || $enemy->no_talent) { ?>
			<div style="float: left; height: 70px; padding: 7px; margin-left: 5px;">
				<img src="<?=image_url('items/1715.png');?>" class="technique-popover" style="cursor: help;" data-source="#no_talent-player" data-title="" data-trigger="hover" data-placement="right" />
				<div class="technique-container" id="no_talent-player">
					<div class="technique-data" style="width: 200px; margin: 0; padding: 5px 15px;">
						<?=(($player->no_talent == 1 || $enemy->no_talent == 1) ? t('battles.pvp.no_talent') : t('battles.pvp.no_talent2'));?>
					</div>
				</div>
			</div>
			<div style="float: right; height: 70px; padding: 7px; margin-right: 5px;">
				<img src="<?=image_url('items/1715.png');?>" class="technique-popover" style="cursor: help;" data-source="#no_talent-enemy" data-title="" data-trigger="hover" data-placement="left" />
				<div class="technique-container" id="no_talent-enemy">
					<div class="technique-data" style="width: 200px; margin: 0; padding: 5px 15px;">
						<?=(($player->no_talent == 1 || $enemy->no_talent == 1) ? t('battles.pvp.no_talent') : t('battles.pvp.no_talent2'));?>
					</div>
				</div>	
			</div>
		<?php } ?>	
		<div id="ranking">
			<div class="text"><?=t('battles.stats.text');?></div>
			<div class="wins">
				<?=t('battles.stats.wins');?><br />
				<span><?=highamount($stats->wins);?></span>
			</div>
			<div class="losses">
				<?=t('battles.stats.losses');?><br />
				<span><?=highamount($stats->losses);?></span>
			</div>
			<div class="draws">
				<?=t('battles.stats.draws');?><br />
				<span><?=highamount($stats->draws);?></span>
			</div>
		</div>
	<?php } ?>
	<div class="top"></div>
	<div class="player-container">
		<div class="chains"></div>
		<div id="players">
			<div id="vs">
				<div class="log" style="margin-top: 296px;">
					<?php if (is_array($log)): ?>
						<?php foreach ($log as $entry): ?>
							<div><?=$entry;?></div><hr />
						<?php endforeach ?>
					<?php endif ?>
				</div>
				<div class="log-scroller">
					<span class="up glyphicon glyphicon-chevron-up"></span>
					<span class="down glyphicon glyphicon-chevron-down"></span>
				</div>
				<div class="log-timer"></div>
				<div id="attack-text"></div>
			</div>
			<div id="player" class="player-box" data-faction="<?=$player->faction_id;?>" data-organization="<?=($player->organization_id ? $player->organization()->name : '-');?>" <?=(!$player->battle_npc_id ? ($player_wanted ? 'data-wanted="1" data-wanted-reward="' . ($player->won_last_battle > 100 ? highamount(100 * 250) : highamount($player->won_last_battle * 250)) . ' ' . t('currencies.' . $player->character()->anime_id) . '" data-wanted-type="' . t('wanted.' . $player_wanted->type_death) . '"' : '') : '');?>>
				<div class="modifiers" data-placement="right"></div>
				<div class="image">
					<?=$player->profile_image();?>
				</div>
				<div class="name">
					<?=$player->name;?><br />
					<span style="font-size:11px; font-weight: none; color:#09F">
						<?=($player->headline_id ? $player->headline()->description()->name : '--');?>
					</span>
				</div>
				<div class="bg-attributes-container">
					<div class="bg-attributes">
						<?=partial('shared/fight_panel_activatables', [
							'who' => $player
						]);?>
						<div class="mana">
							<div class="mana-fill"></div>
							<div class="text"></div>
						</div>
						<div class="life">
							<div class="life-fill"></div>
							<div class="text"></div>
						</div>
						<div class="level"><?=$player->level;?></div>
					</div>
				</div>
			</div>
			<div id="enemy" class="player-box" data-faction="<?=$enemy->faction_id;?>" data-organization="<?=($enemy->organization_id ? $enemy->organization()->name : '-');?>" <?=(!$player->battle_npc_id ? ($enemy_wanted ? 'data-wanted="1" data-wanted-reward="' . ($enemy->won_last_battle > 100 ? highamount(100 * 250) : highamount($enemy->won_last_battle * 250)) . ' ' . t('currencies.' . $player->character()->anime_id) . '" data-wanted-type="' . t('wanted.' . $enemy_wanted->type_death) . '"' : '') : '');?>>
				<div class="modifiers" data-placement="left"></div>
				<div class="image">
					<?=$enemy->profile_image();?>
				</div>
				<div class="name">
					<?=$enemy->name;?><br />
					<span style="font-size:11px; font-weight: none; color:#09F">
						<?=(is_a($enemy, 'Player') && $enemy->headline_id ? $enemy->headline()->description()->name : '--');?>
					</span>
				</div>
				<div class="bg-attributes-container">
					<div class="bg-attributes">
						<?=partial('shared/fight_panel_activatables', [
							'who' => $enemy
						]);?>
						<div class="mana">
							<div class="mana-fill"></div>
							<div class="text"></div>
						</div>
						<div class="life">
							<div class="life-fill"></div>
							<div class="text"></div>
						</div>
						<div class="level"><?=$enemy->level;?></div>
					</div>
				</div>
			</div>
			<div class="clearfix"></div>
			<div id="technique-container">
				<div class="technique-list-box">
					<?php foreach ($techniques as $technique): ?>
					<?php $item	= $technique->item() ?>
						<?php
							if($item->is_defensive){
								$class = "defense";
							}else if(!$item->is_defensive && $item->is_generic){
								$class = "attack";
							}else{
								$class = "unique";
							}
						?>
						<div class="item item-type-<?php echo $item->item_type_id ?> <?php echo $class ?> <?php echo $item->is_buff ? 'buff' : 'normal' ?>" id="item-container-<?php echo $item->id ?>" data-item="<?php echo $item->id ?>">
							<img src="<?php echo image_url($item->image(true)) ?>" class="technique-popover" data-source="#technique-content-<?php echo $item->id ?>" data-title="<?php echo $item->description()->name ?>" data-trigger="hover" data-placement="bottom" />
							<div class="modifier-turn-data"></div>
							<div class="technique-container" id="technique-content-<?php echo $item->id ?>">
								<?php echo $item->technique_tooltip(true) ?>
							</div>
						</div>
					<?php endforeach ?>
				</div>
				<div id="skip-turn" data-item="skip">Pular Turno</div>
				<div class="clearfix"></div>
			</div>
		</div>

	</div>
	<div id="divider"></div>
</div>
<?php if ($player->battle_npc_id) { ?>
	<script type="text/javascript">
		$(document).ready(function () {
			draw_battle_hb(<?php echo $enemy->for_life() ?>, <?php echo $enemy->for_life(true) ?>);
			draw_battle_hb(<?php echo $player->for_life() ?>, <?php echo $player->for_life(true) ?>, 'l');

			draw_battle_mb(<?php echo $enemy->for_mana() ?>, <?php echo $enemy->for_mana(true) ?>);
			draw_battle_mb(<?php echo $player->for_mana() ?>, <?php echo $player->for_mana(true) ?>, 'l');
		});
</script>	
<?php } ?>