<?php echo partial('shared/title', array('title' => 'organizations.show.title', 'place' => 'organizations.show.title')) ?>
<div style="height: 255px">
	<div>
		<div style="position: relative; width: 684px; height:188px;  left: 25px; background-image:url(<?php echo image_url('bg-org.jpg') ?>)">
			<?php if ($organization->cover_file): ?>
				<div style="position:absolute; top: 10px; left: 10px;"><img src="<?php echo resource_url('uploads/organizations/' . $organization->cover_file) ?>" /></div>
			<?php endif ?>
		</div>
		<?php /*<div><?php echo $organization->name ?></div>
		<div><?php echo nl2br($organization->description) ?></div>*/?>
	</div>
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

			<input type="hidden" name="name" value="<?php echo $organization->name ?>" />
			<div style="float: left; width:200px; text-align: center"><label><?php echo t('organizations.show.choose_image') ?></label></div>
			<div style="float: left; width:300px;"><input type="file" name="cover" /><?php echo t('organizations.show.image_note') ?></div>
			<div style="float: left; width:130px;"><input type="submit" class="btn btn-primary" value="<?php echo t('organizations.show.upload_data') ?>" /></div>
		</form>
	<?php endif ?>
</div>
<div style="width: 730px; height: 185px; position: relative; left: 24px">
	<div class="h-missoes">
		<div style="width: 341px; text-align: center; padding-top: 12px"><b class="amarelo" style="font-size:13px"><?php echo $organization->name?></b></div>
		<div style="width: 341px; text-align: center; padding-top: 22px; font-size: 12px !important; line-height: 15px;">
			<span class="verde"><?php echo t('organizations.name_leader') ?>: </span> <?php echo $rank_org ? $rank_org->leader_name : '-'?><br />
			<span class="verde"><?php echo t('organizations.total_members') ?>:</span> <?php echo highamount($organization->member_count) ?><br />
			<span class="verde"><?php echo t('organizations.faction') ?>:</span> <?php echo $organization->faction_id==1? "Herois" : "Vilões" ?><br />
			<span class="verde"><?php echo t('organizations.score') ?>: </span> <?php echo $rank_org ? highamount($rank_org->score) : '-' ?><br />
			<span class="verde"><?php echo t('organizations.rank_faction') ?>: </span> <?php echo $rank_org ? highamount($rank_org->position_faction) : '-' ?>º<br />
			<span class="verde"><?php echo t('organizations.rank_general') ?>: </span> <?php echo $rank_org ? highamount($rank_org->position_general) : '-'?>º<br />
		</div>
	</div>
	<div class="h-missoes">
		<div style="width: 341px; text-align: center; padding-top: 12px"><b class="amarelo" style="font-size:13px"><?php echo t('organizations.missions') ?></b></div>
		<div style="width: 341px; text-align: center; padding-top: 22px; font-size: 12px !important; line-height: 15px;">
			<span class="verde"><?php echo t('organizations.daily') ?>:</span> <?php echo highamount($daily_org->daily_total) ?><br />
			<span class="verde"><?php echo t('organizations.treasure_atual') ?>:</span> <?php echo highamount($organization->treasure_atual) ?><br />
			<span class="verde"><?php echo t('organizations.treasure_total') ?>:</span> <?php echo highamount($organization->treasure_total) ?><br />
		</div>
	</div>
</div>
<div style="clear: left; float: left;"></div>
<ul class="nav nav-pills" id="organization-details-tabs">
	<li class="active"><a href="#organization-player-list" role="tab" data-toggle="tab"><?php echo t('organizations.show.members') ?></a></li>
	<?php if ($can_accept && $organization->member_count < 8): ?>
		<li>
			<a href="#organization-accept-list" role="tab" data-toggle="tab">
				<?php echo t('organizations.show.accepts') ?>
				<?php if (sizeof($requests)): ?>
					<b>( <?php echo sizeof($requests) ?> )</b>
				<?php endif ?>
			</a>
		</li>
	<?php endif ?>
</ul>
<div class="tab-content">
	<div id="organization-player-list" class="tab-pane active">
		<br />
		<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
			<table width="730" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td width="140">&nbsp;</td>
				<td width="150" align="center"><?php echo t('organizations.show.header.player') ?></td>
				<td width="80" align="center"><?php echo t('organizations.show.treasure') ?></td>
				<td width="100" align="center">Último Login</td>
				<?php if ($is_leader): ?>
					<td width="80" align="center">
						<?php echo t('organizations.show.header.can_kick') ?>
					</td>
					<td width="80" align="center">
						<?php echo t('organizations.show.header.can_accept') ?>
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
							<?php if ($p->player_id != $organization->player_id): ?>
								<input type="checkbox" class="can-approve" data-id="<?php echo $instance->id ?>" <?php echo $p->can_accept_players ? 'checked="checked"' : '' ?> />
							<?php else: ?>
								<?php echo t('global.word_yes') ?>
							<?php endif ?>
						</td>
						<td width="80" align="center">
							<?php if ($p->player_id != $organization->player_id): ?>
								<input type="checkbox" class="can-kick" data-id="<?php echo $instance->id ?>" <?php echo $p->can_kick_players ? 'checked="checked"' : '' ?> />
							<?php else: ?>
								<?php echo t('global.word_yes') ?>
							<?php endif ?>
						</td>
					<?php endif ?>
					<?php if ($can_kick && $instance->id != $player->id): ?>
						<td width="120" align="center">
							<a class="btn btn-danger kick" data-id="<?php echo $instance->id ?>"><?php echo t('organizations.show.kick') ?></a>
						</td>
					<?php elseif($player->id == $instance->id && $player->id != $organization->player_id): ?>
						<td width="120" align="center"><a class="btn btn-danger leave" data-id="<?php echo $instance->id ?>"><?php echo t('organizations.show.leave') ?></a></td>
					<?php elseif($player->id == $instance->id && $player->id == $organization->player_id && $organization->member_count == 1): ?>
						<td width="120" align="center"><a class="btn btn-danger destroy" data-id="<?php echo $instance->id ?>"><?php echo t('organizations.show.delete') ?></a></td>
					<?php else: ?>
						<td width="120" align="center"></td>
					<?php endif ?>
				</tr>
				<tr height="4"></tr>
			<?php endforeach ?>
		</table>
	</div>
	<?php if ($can_accept && $organization->member_count < 8): ?>
	<div id="organization-accept-list" class="tab-pane">
		<?php if (!$requests): ?>
			<br />
			<div class="alert alert-info"><?php echo t('organizations.show.no_pending') ?></div>
		<?php else: ?>
			<br />
			<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
				<table width="730" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td width="140">&nbsp;</td>
					<td width="150" align="center"><?php echo t('organizations.show.header.player') ?></td>
					<td width="80" align="center"><?php echo t('organizations.show.header.level') ?></td>
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
						$currenct_can_accept	= $organization->can_accept_player($player->id, $p->player_id);
					?>
					<tr bgcolor="<?php echo $colors ?>">
						<td width="140" align="center"><?php echo $instance->character_theme()->first_image()->small_image() ?></td>
						<td width="150" align="center"><span style="font-size:14px" class="amarelo"><?php echo $instance->name ?></span></td>
						<td width="80" align="center"><span class="azul_claro"><?php echo $instance->level ?></span></td>
						<?php if ($currenct_can_accept): ?>
							<td width="120" align="center"><a class="btn btn-success accept" data-id="<?php echo $p->id ?>"><?php echo t('organizations.show.accept') ?></a></td>
							<td width="120" align="center"><a class="btn btn-danger refuse" data-id="<?php echo $p->id ?>"><?php echo t('organizations.show.refuse') ?></a></td>
						<?php else: ?>
							<td width="240" align="center" colspan="2"><?php echo t('organizations.show.accept_unavailable') ?></td>
						<?php endif ?>
					</tr>
					<tr height="4"></tr>
				<?php endforeach ?>
			</table>
			<div align="center" style="padding-top: 10px"><a class="btn btn-danger remove_all" data-message="<?php echo t('organizations.remove_all')?>"><?php echo t('buttons.remover_all')?></a></div>
		<?php endif ?>
	</div>	
	<?php endif ?>
</div>