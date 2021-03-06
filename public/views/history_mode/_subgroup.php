<?php
	$npc_list_id	= 'history-mode-subgroup-npcs-' . $subgroup->id;
?>
<div class="history-mode-subgroup-list">
	<div class="msg-container-off">
			<div class="msg-h" style="background:url(<?php echo image_url($subgroup->image())?>); background-repeat: no-repeat;">
			</div>
			<div class="msgb-h" style="position:relative; margin-left: 231px; text-align: left; top: 36px">
				<b>
					<?php echo $subgroup->description()->name ?>
					<a class="btn btn-primary pull-right show-battles" data-target="#<?php echo $npc_list_id ?>" data-show-text="<?php echo t('history_mode.show.show_battles') ?>" data-hide-text="<?php echo t('history_mode.show.hide_battles') ?>"><?php echo t('history_mode.show.show_battles') ?></a>
				</b>
				<div class="content">
					<?php echo $subgroup->description()->description ?><br /><p style="color:#3C3; font-size: 14px; padding-top:5px"><?php echo t('history_mode.show.recompensas')?></p>
					<ul>
						<?php if($subgroup->reward_exp){?>
							<li style="width:100px"><?php echo t('history_mode.show.experiencia')?>: <?php echo $subgroup->reward_exp ?></li>
						<?php }?>
						<?php if($subgroup->reward_currency){?>
							<li style="width:100px"><?php echo t('currencies.' . $player->character()->anime_id) ?>: <?php echo $subgroup->reward_currency ?></li>
						<?php }?>
						<?php if($subgroup->reward_random_equipment_chance){?>
							<li style="width:150px"><?php echo t('history_mode.show.equipamento')?>: <?php echo t('history_mode.show.aleatorio')?></li>
						<?php }?>
						<?php if($subgroup->reward_pet_chance  && $subgroup->reward_item_id){?>
							<li style="width:150px"><?php echo t('history_mode.show.mascote')?>: <?php echo Item::find($subgroup->reward_item_id)->description()->name ?></li>
						<?php }?>
						<?php if($subgroup->reward_character_theme_id){?>
							<li style="width:215px"><?php echo t('history_mode.show.tema')?>: <?php echo CharacterTheme::find($subgroup->reward_character_theme_id)->description()->name ?></li>
						<?php }?>
						<?php if($subgroup->reward_character_id){?>
							<li style="width:150px"><?php echo t('history_mode.show.personagem')?>: <?php echo Character::find($subgroup->reward_character_id)->description()->name ?></li>
						<?php }?>
						<?php if($subgroup->reward_headline_id){?>
							<li style="width:215px"><?php echo t('history_mode.show.titulo')?>: <?php echo Headline::find($subgroup->reward_headline_id)->description()->name ?></li>
						<?php }?>
						<?php if($subgroup->reward_item_chance && $subgroup->reward_item_id){?>
							<li style="width:150px">
								<?php echo t('history_mode.show.item')?>:
								<?php
									$reward	= Item::find($subgroup->reward_item_id);
									$reward->set_anime($player->character()->anime_id);
									echo $reward->description()->name . ($subgroup->reward_quantity ? " x ". $subgroup->reward_quantity : "");
									
									
								?>
							</li>
						<?php }?>
						
					</ul>
				</div>
			</div>
	</div>
	<table cellpadding="0" cellspacing="0" width="690" style="margin-left:20px; z-index:1; margin-top:-18px; display: none" id="<?php echo $npc_list_id ?>">
		<?php $counter = 0; ?>
		<?php foreach ($subgroup->npcs($player) as $npc): 
			  $color	= $counter++ % 2 ? '091e30' : '173148';
		?>
		
			<?php $npc->set_player($player); ?>
			<tr bgcolor="<?php echo $color ?>" height="70">
				<td width="140" align="center"><?php echo $npc->image() ?></td>
				<td width="230" align="center" style="color: #fdc173; font-size:14px"><?php echo $npc->description()->name ?></td>
				<td width="100" align="center"><img src="<?php echo image_url("icons/for_stamina.png" ) ?>" /><span style="font-size:15px; padding-left:5px"><?php echo $npc->stamina_cost ?></span></td>
				<td width="100" align="center"><?php echo t('difficulties.' . $npc->difficulty) ?></td>
				<td width="120" align="center">
					<?php if ($npc->killed()): ?>
						<a class="btn btn-success disabled"><?php echo t('history_mode.show.completed') ?></a>
					<?php else: ?>
						<?php if ($npc->can_battle()): ?>
							<a class="btn btn-primary battle" data-npc="<?php echo $npc->id ?>"><?php echo t('history_mode.show.battle') ?></a>
						<?php else: ?>
							<a class="btn btn-primary disabled"><?php echo t('history_mode.show.battle') ?></a>
						<?php endif ?>
					<?php endif ?>
				</td>
			</tr>
			<tr height="4"></tr>
		<?php endforeach ?>
	</table>
</div>