<?php echo partial('shared/title', array('title' => 'guilds.show.title', 'place' => 'guilds.show.title')) ?>
<div style="height: 255px">
	<div>
		<div style="position: relative; width: 684px; height:188px;  left: 25px; background-image:url(<?=image_url('bg-org.jpg');?>)">
			<?php if ($guild->cover_file): ?>
				<div style="position:absolute; top: 10px; left: 10px;"><img src="<?=resource_url('uploads/guilds/' . $guild->cover_file);?>" /></div>
			<?php endif ?>
		</div>
		<!-- <div><?=$guild->name;?></div>
		<div><?=nl2br($guild->description);?></div> -->
	</div><br />
	<?php if ($is_leader): ?>
		<form class="form form-horizontal" method="post" enctype="multipart/form-data">
			<?php if ($errors): ?>
				<div class="alert alert-danger">
					<ul>
						<?php foreach ($errors as $error): ?>
							<li><?php echo $error ?></li>
						<?php endforeach ?>
					</ul>
				</div>
			<?php endif ?>

			<input type="hidden" name="name" value="<?=$guild->name;?>" />
			<input type="hidden" name="description" value="<?=$guild->description;?>" />
			<div style="float: left; width:200px; text-align: center"><label><?php echo t('guilds.show.choose_image') ?></label></div>
			<div style="float: left; width:300px;"><input type="file" name="cover" /><?php echo t('guilds.show.image_note') ?></div>
			<div style="float: left; width:130px;"><input type="submit" class="btn btn-sm btn-primary" value="<?php echo t('guilds.show.upload_data') ?>" /></div>
		</form>
	<?php endif ?>
</div>

<div style="width: 730px; height: 165px; position: relative; left: 24px">
	<div class="h-missoes">
		<div style="width: 341px; text-align: center; padding-top: 12px"><b class="amarelo" style="font-size:13px"><?php echo $guild->name?></b></div>
		<div style="width: 341px; text-align: center; padding-top: 22px; font-size: 12px !important; line-height: 15px;">
			<span class="verde"><?php echo t('guilds.name_leader') ?>: </span> <?php echo $rank_org ? $rank_org->leader_name : '-'?><br />
			<span class="verde"><?php echo t('guilds.total_members') ?>:</span> <?php echo highamount($guild->member_count) ?><br />
			<span class="verde"><?php echo t('guilds.faction') ?>:</span> <?php echo $guild->faction()->description()->name ?><br />
			<span class="verde"><?php echo t('guilds.score') ?>: </span> <?php echo $rank_org ? highamount($rank_org->score) : '-' ?><br />
			<span class="verde"><?php echo t('guilds.rank_faction') ?>: </span> <?php echo $rank_org ? highamount($rank_org->position_faction) : '-' ?>º<br />
			<span class="verde"><?php echo t('guilds.rank_general') ?>: </span> <?php echo $rank_org ? highamount($rank_org->position_general) : '-'?>º<br />
		</div>
	</div>
	<div class="h-missoes">
		<div style="width: 341px; text-align: center; padding-top: 12px"><b class="amarelo" style="font-size:13px"><?php echo t('guilds.missions') ?></b></div>
		<div style="width: 341px; text-align: center; padding-top: 22px; font-size: 12px !important; line-height: 15px;">
			<span class="verde"><?php echo t('guilds.daily') ?>:</span> <?php echo highamount($daily_org->daily_total) ?><br />
			<span class="verde"><?php echo t('guilds.treasure_atual') ?>:</span> <?php echo highamount($guild->treasure_atual) ?><br />
			<span class="verde"><?php echo t('guilds.treasure_total') ?>:</span> <?php echo highamount($guild->treasure_total) ?><br />
		</div>
	</div>
</div>

<div style="width: 680px; height: 185px; position: relative; left: 24px">
	<div class="h-missoes" style="width: 680px; background-image: url(<?=image_url('bg_guild_level.png')?>);">
		<div style="width: 680px; text-align: center; padding-top: 12px">
			<b class="amarelo" style="font-size:13px">Progresso da Organização</b>
		</div>
		<div style="width: 680px; text-align: center; padding-top: 31px; font-size: 12px !important; line-height: 15px;">
			<table width="100%">
				<tr>
					<td rowspan="2" width="95" style="padding-top: 5px; text-transform: uppercase;">
						<p>Nível</p>
						<div class="amarelo" style="opacity: 0.75; font-size: 50px; line-height: 50px;"><?=$guild->level;?></div>
					</td>
					<td align="center">
						<?php for ($level = 1; $level <= MAX_LEVEL_GUILD; ++$level) {?>
							<div style="display: inline-block; padding-left: 4px;" class="technique-popover" data-source="#guild-level-container-<?=$level;?>" data-title="Nível <?=$level;?>" data-trigger="click" data-placement="top">
								<img src="<?=image_url('icons/star-' . ($guild->level >= $level ? 'on' : 'off') . '.png')?>" style="cursor: pointer;" />
							</div>
							<div id="guild-level-container-<?=$level;?>" class="technique-container">
							<div style="min-width: 230px;">
								<?php if ($rewards = $guild->level_rewards($level)) { ?>
									<?php
									$bonuses	= [
										'for_atk', 'for_def', 'for_crit', 'for_inc_crit', 'for_abs', 'for_inc_abs', 'for_prec', 'for_init',
										'currency_battle', 'exp_battle', 'currency_quest', 'exp_quest', 'npc_battle_count', 'item_drop_increase',
										'luck_discount', 'generic_technique_damage', 'unique_technique_damage', 'defense_technique_extra',
										'life_regen', 'mana_regen', 'stamina_regen'
									];
									$formules	= [
										'for_atk', 'for_def', 'for_crit', 'for_abs', 'for_prec', 'for_init', 'for_inc_crit', 'for_inc_abs',
									];
									$percents	= [
										'currency_battle', 'exp_battle', 'currency_quest', 'exp_quest', 'luck_discount', 'item_drop_increase',
										'defense_technique_extra', 'generic_technique_damage', 'unique_technique_damage',
										'for_crit', 'for_abs', 'for_prec', 'for_inc_crit', 'for_inc_abs',
										'life_regen', 'mana_regen', 'stamina_regen'
									];
									$colors		= [
										'currency_battle', 'exp_battle', 'currency_quest', 'exp_quest', 'item_drop_increase',
										'luck_discount', 'generic_technique_damage', 'unique_technique_damage', 'defense_technique_extra',
										'life_regen', 'mana_regen', 'stamina_regen'
									];
									foreach ($bonuses as $bonus) {
										if ($rewards->$bonus > 0) {
											if (in_array($bonus, $formules)) {
												$translation	= t('global.em') . ' ' . t('formula.' . $bonus);
											} else {
												$translation	= t('equipments.attributes.' . $bonus);
											}

											echo '<li>';
												echo '<span class="' . (in_array($bonus, $colors) ? 'laranja' : 'plus') . '">';
													echo '+' . round($rewards->$bonus, 2) . (in_array($bonus, $percents) ? '%' : '');
												echo '</span>';
												echo  ' ' . $translation;
											echo '</li>';
										}
									}
								?>
								<?php } else { ?>
									<div style="text-align: center;">Sem bonificação.</div>
								<?php } ?>
							</div>
						</div>
						<?php } ?>
					</td>
					<td rowspan="2" width="95" style="padding-top: 5px; text-transform: uppercase;">
						Nível máx.<br />
						<span class="amarelo" style="font-size: 50px; line-height: 50px;"><?=MAX_LEVEL_GUILD;?></span>
					</td>
				</tr>
				<tr>
					<td align="center">
						<?=exp_bar($guild->exp, $guild->level_exp(), 455, highamount($guild->exp) . ' / ' . highamount($guild->level_exp()));?>
						<div class="laranja" style="margin-top: 2px;">
							Complete a barra de experiencia para evoluir a organização e receber bonificações.
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>

<div style="clear: left; float: left;"></div>
<ul class="nav nav-pills" id="guild-details-tabs">
	<li class="active"><a href="#guild-player-list" role="tab" data-toggle="tab"><?php echo t('guilds.show.members') ?></a></li>
	<?php if ($can_accept && $guild->member_count < 8): ?>
		<li>
			<a href="#guild-accept-list" role="tab" data-toggle="tab">
				<?php echo t('guilds.show.accepts') ?>
				<?php if (sizeof($requests)): ?>
					<b>( <?php echo sizeof($requests) ?> )</b>
				<?php endif ?>
			</a>
		</li>
	<?php endif ?>
</ul>
<div class="tab-content">
	<div id="guild-player-list" class="tab-pane active">
		<br />
		<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
			<table width="730" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td width="140">&nbsp;</td>
				<td width="150" align="center"><?php echo t('guilds.show.header.player') ?></td>
				<td width="80" align="center"><?php echo t('guilds.show.treasure') ?></td>
				<td width="100" align="center">Último Login</td>
				<?php if ($is_leader): ?>
					<td width="80" align="center">
						<?php echo t('guilds.show.header.can_kick') ?>
					</td>
					<td width="80" align="center">
						<?php echo t('guilds.show.header.can_accept') ?>
					</td>
				<?php endif ?>
				<td width="120" align="center">Status</td>
			</tr>
			</table>
		</div>
		<table width="730" border="0" cellpadding="0" cellspacing="0">
			<?php $counter = 0; ?>
			<?php foreach ($players as $p):
					$color	= $counter++ % 2 ? '091e30' : '173148';
			?>
				<?php
					$instance	= $p->player();
				?>
				<tr bgcolor="<?php echo $color ?>">
					<td width="140" align="center">
						<?php echo $instance->character_theme()->first_image()->small_image() ?>
					</td>
					<td width="150" align="center">
						<?php if (is_player_online($p->player_id)): ?>
							<img src="<?php echo image_url("on.png" ) ?>"/>
						<?php else: ?>
							<img src="<?php echo image_url("off.png" ) ?>"/>
						<?php endif ?>
						<span style="font-size:14px" class="amarelo"><?php echo $instance->name ?></span><br /><span class="azul_claro">Nível <?php echo $instance->level ?></span></td>
					<td width="80" align="center">
						<img src="<?php echo image_url('icons/treasure.png') ?>" width="16"/> <span style="font-size: 12px"><?php echo highamount($instance->treasure_atual); ?></span>
					</td>
					<td width="100" align="center">
						<?php
							if($instance->last_login){
								$timestamp = strtotime($instance->last_login);
								echo date('d/m/Y H:i:s', $timestamp);
							}else{
								echo "-";
							}
						?>
					</td>
					<?php if ($is_leader): ?>
						<td width="80" align="center">
							<?php if ($p->player_id != $guild->player_id): ?>
								<input type="checkbox" class="can-approve" data-id="<?php echo $instance->id ?>" <?php echo $p->can_accept_players ? 'checked="checked"' : '' ?> />
							<?php else: ?>
								<?php echo t('global.word_yes') ?>
							<?php endif ?>
						</td>
						<td width="80" align="center">
							<?php if ($p->player_id != $guild->player_id): ?>
								<input type="checkbox" class="can-kick" data-id="<?php echo $instance->id ?>" <?php echo $p->can_kick_players ? 'checked="checked"' : '' ?> />
							<?php else: ?>
								<?php echo t('global.word_yes') ?>
							<?php endif ?>
						</td>
					<?php endif ?>
					<?php if ($can_kick && $instance->id != $player->id): ?>
						<td width="120" align="center">
							<a class="btn btn-sm btn-danger kick" data-id="<?php echo $instance->id ?>"><?php echo t('guilds.show.kick') ?></a>
						</td>
					<?php elseif($player->id == $instance->id && $player->id != $guild->player_id): ?>
						<td width="120" align="center"><a class="btn btn-sm btn-danger leave" data-id="<?php echo $instance->id ?>"><?php echo t('guilds.show.leave') ?></a></td>
					<?php elseif($player->id == $instance->id && $player->id == $guild->player_id && $guild->member_count == 1): ?>
						<td width="120" align="center"><a class="btn btn-sm btn-danger destroy" data-id="<?php echo $instance->id ?>"><?php echo t('guilds.show.delete') ?></a></td>
					<?php else: ?>
						<td width="120" align="center"></td>
					<?php endif ?>
				</tr>
				<tr height="4"></tr>
			<?php endforeach ?>
		</table>
	</div>
	<?php if ($can_accept && $guild->member_count < 8): ?>
	<div id="guild-accept-list" class="tab-pane">
		<?php if (!$requests): ?>
			<br />
			<div class="alert alert-info"><?php echo t('guilds.show.no_pending') ?></div>
		<?php else: ?>
			<br />
			<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
				<table width="730" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td width="140">&nbsp;</td>
					<td width="150" align="center"><?php echo t('guilds.show.header.player') ?></td>
					<td width="80" align="center"><?php echo t('guilds.show.header.level') ?></td>
					<td width="120" align="center">

					</td>
					<td width="120" align="center">

					</td>

				</tr>
				</table>
			</div>
			<table width="730" border="0" cellpadding="0" cellspacing="0">
				<?php $counters = 0; ?>
				<?php foreach ($requests as $p):
					  $colors	= $counters++ % 2 ? '091e30' : '173148';
				?>
					<?php
						$instance				= $p->player();
						$currenct_can_accept	= $guild->can_accept_player($player->id, $p->player_id);
					?>
					<tr bgcolor="<?php echo $colors ?>">
						<td width="140" align="center"><?php echo $instance->character_theme()->first_image()->small_image() ?></td>
						<td width="150" align="center"><span style="font-size:14px" class="amarelo"><?php echo $instance->name ?></span></td>
						<td width="80" align="center"><span class="azul_claro"><?php echo $instance->level ?></span></td>
						<?php if ($currenct_can_accept): ?>
							<td width="120" align="center"><a class="btn btn-sm btn-success accept" data-id="<?php echo $p->id ?>"><?php echo t('guilds.show.accept') ?></a></td>
							<td width="120" align="center"><a class="btn btn-sm btn-danger refuse" data-id="<?php echo $p->id ?>"><?php echo t('guilds.show.refuse') ?></a></td>
						<?php else: ?>
							<td width="240" align="center" colspan="2"><?php echo t('guilds.show.accept_unavailable') ?></td>
						<?php endif ?>
					</tr>
					<tr height="4"></tr>
				<?php endforeach ?>
			</table>
			<div align="center" style="padding-top: 10px"><a class="btn btn-sm btn-danger remove_all" data-message="<?php echo t('guilds.remove_all')?>"><?php echo t('buttons.remover_all')?></a></div>
		<?php endif ?>
	</div>
	<?php endif ?>
</div>
