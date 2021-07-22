<?php $npc_list_id	= 'history-mode-subgroup-npcs-' . $subgroup->id; ?>
<div class="history-mode-subgroup-list">
	<div class="msg-container-off">
		<div class="msg-h" style="background:url(<?=image_url($subgroup->image());?>); background-repeat: no-repeat;"></div>
		<div class="msgb-h" style="position:relative; margin-left: 231px; text-align: left; top: 36px">
			<b>
				<?=$subgroup->description()->name;?>
				<a class="btn btn-sm btn-primary pull-right show-battles" data-target="#<?=$npc_list_id;?>" data-show-text="<?=t('history_mode.show.show_battles');?>" data-hide-text="<?=t('history_mode.show.hide_battles');?>">
					<?=t('history_mode.show.show_battles');?>
				</a>
			</b>
			<div class="content">
				<?=$subgroup->description()->description;?><br />
				<p style="color: #3C3; font-size: 14px; padding-top: 5px"><?=t('history_mode.show.recompensas');?></p>
				<ul>
					<?php if($subgroup->reward_exp) { ?>
						<li style="width:100px"><?php echo t('history_mode.show.experiencia')?>: <span class="branco"><?php echo highamount($subgroup->reward_exp) ?></span></li>
					<?php } ?>
					<?php if ($subgroup->reward_currency) { ?>
						<li style="width:100px"><?php echo t('currencies.' . $player->character()->anime_id) ?>: <span class="branco"><?php echo highamount($subgroup->reward_currency) ?></span></li>
					<?php } ?>
					<?php if($subgroup->reward_random_equipment_chance){?>
						<li style="width:150px"><?php echo t('history_mode.show.equipamento')?>: <span class="branco"><?php echo t('history_mode.show.aleatorio')?></span></li>
					<?php } ?>
					<?php if($subgroup->reward_pet_chance  && $subgroup->reward_item_id){?>
						<li style="width:150px"><?php echo t('history_mode.show.mascote')?>: <span class="branco"><?php echo Item::find($subgroup->reward_item_id)->description()->name ?></span></li>
					<?php } ?>
					<?php if($subgroup->reward_character_theme_id){?>
						<li style="width:215px"><?php echo t('history_mode.show.tema')?>: <span class="branco"><?php echo CharacterTheme::find($subgroup->reward_character_theme_id)->description()->name ?></span></li>
					<?php } ?>
					<?php if($subgroup->reward_character_id){?>
						<li style="width:150px"><?php echo t('history_mode.show.personagem')?>: <span class="branco"><?php echo Character::find($subgroup->reward_character_id)->description()->name ?></span></li>
					<?php } ?>
					<?php if($subgroup->reward_headline_id){?>
						<li style="width:215px"><?php echo t('history_mode.show.titulo')?>: <span class="branco"><?php echo Headline::find($subgroup->reward_headline_id)->description()->name ?></span></li>
					<?php } ?>
					<?php if ($subgroup->reward_item_chance && $subgroup->reward_item_id) { ?>
						<li style="width:150px">
							<?=t('history_mode.show.item');?>:
							<span class="branco"><?php
								$reward	= Item::find($subgroup->reward_item_id);
								$reward->set_anime($player->character()->anime_id);
								echo $reward->description()->name . ($subgroup->reward_quantity ? " x " . highamount($subgroup->reward_quantity) : "");
							?></span>
						</li>
					<?php }?>

				</ul>
			</div>
		</div>
	</div><br />
	<table cellpadding="0" cellspacing="0" width="690" style="margin-left:20px; z-index:1; margin-top:-18px; display: none" id="<?=$npc_list_id;?>">
		<?php
		$counter = 0;
		foreach ($subgroup->npcs($player) as $npc) {
			$color	= $counter++ % 2 ? '091e30' : '173148';
			$npc->set_player($player);
		?>
			<tr bgcolor="<?=$color;?>" height="70">
				<td width="140" align="center">
					<?=$npc->image();?>
				</td>
				<td width="230" align="center" style="color: #fdc173; font-size: 14px">
					<?=$npc->description()->name;?>
				</td>
				<td width="100" align="center">
					<img src="<?=image_url("icons/for_stamina.png");?>" />
					<span style="font-size:15px; padding-left:5px">
						<?=$npc->staminaCost();?>
					</span>
				</td>
				<td width="100" align="center">
					<?=t('difficulties.' . $npc->difficulty);?>
				</td>
				<td width="120" align="center">
					<?php if ($npc->killed()) { ?>
						<a class="btn btn-sm btn-success disabled">
						<!-- <a class="btn btn-sm btn-success battle" data-npc="<?=$npc->id;?>"> -->
							<?=t('history_mode.show.completed');?>
						</a>
					<?php } else { ?>
						<?php if ($npc->can_battle()) { ?>
							<a class="btn btn-sm btn-primary battle" data-npc="<?=$npc->id;?>">
								<?=t('history_mode.show.battle');?>
							</a>
						<?php } else { ?>
							<a class="btn btn-sm btn-primary disabled">
								<?=t('history_mode.show.battle');?>
							</a>
						<?php } ?>
					<?php } ?>
				</td>
			</tr>
		<?php } ?>
	</table>
</div><br />
