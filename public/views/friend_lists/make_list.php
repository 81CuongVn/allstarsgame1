<div class="tab-content">
	<ul class="nav nav-pills" id="friend-details-tabs" style="margin-bottom: 5px;">
		<li class="active">
			<a href="#friend-player-list" role="tab" data-toggle="tab">Jogadores</a>
		</li>
		<li>
			<a href="#friend-accept-list" role="tab" data-toggle="tab">
				<?=t('guilds.show.accepts');?>
				<?php if (sizeof($requests)): ?>
					<b>( <?php echo sizeof($requests) ?> )</b>
				<?php endif ?>
			</a>
		</li>
	</ul>
	<div id="friend-player-list" class="tab-pane active">
		<?php if ($players): ?>
			<?php $counter = 0; ?>
			<table width="725" border="0" cellpadding="0" cellspacing="0">
				<?php foreach ($players as $p):
					$color	= $counter++ % 2 ? '091e30' : '173148';
				?>
				<tr id="player-search-item-<?php echo $p->id ?>"  bgcolor="<?php echo $color ?>">
					<td width="150" align="center"><?php echo $p->character_theme()->first_image()->small_image() ?></td>
					<td width="150" align="center">
						<?php if (is_player_online($p->id)): ?>
							<img src="<?php echo image_url("on.png" ) ?>"/>
						<?php else: ?>
							<img src="<?php echo image_url("off.png" ) ?>"/>
						<?php endif ?>
						<span style="font-size:14px" class="amarelo"><?php echo $p->name ?></span><br /><span class="azul_claro">Nível <?php echo $p->level ?></span>
					</td>
					<td width="200" align="center">
					<td width="200" align="center">
						<?php
							$request	= PlayerFriendRequest::find_first('player_id=' . $player->id . ' AND friend_id=' . $p->id);
						?>
						<?php if(!$request){?>
							<a class="btn btn-sm btn-primary send" data-id="<?php echo $p->id ?>">Enviar pedido de amizade</a>
						<?php }else{?>
							<a class="btn btn-sm btn-primary disabled">Solicitação enviada</a>
						<?php }?>

					</td>
				</tr>
				<tr height="4"></tr>
				<?php endforeach ?>
			</table>
		<?php else: ?>
			<div align="center" style="padding-top: 10px"><b class="laranja" style="font-size:14px;"><?php echo t('friends.nothing') ?></b></div>
		<?php endif ?>
	</div>
	<div id="friend-accept-list" class="tab-pane">
	<?php if ($requests): ?>
		<?php $counter = 0; ?>
		<table width="725" border="0" cellpadding="0" cellspacing="0">
		<?php foreach ($requests as $p):
			$color					= $counter++ % 2 ? '091e30' : '173148';
			$instance				= $p->player($p->player_id);
			if ($instance) {
		?>
			<tr id="player-search-item-<?php echo $p->id ?>"  bgcolor="<?php echo $color ?>">
				<td width="150" align="center"><?php echo $instance->character_theme()->first_image()->small_image() ?></td>
				<td width="150" align="center">
					<?php if (is_player_online($instance->id)): ?>
						<img src="<?php echo image_url("on.png" ) ?>"/>
					<?php else: ?>
						<img src="<?php echo image_url("off.png" ) ?>"/>
					<?php endif ?>
					<span style="font-size:14px" class="amarelo"><?php echo $instance->name ?></span><br /><span class="azul_claro">Nível <?php echo $instance->level ?></span>
				</td>
				<td width="200" align="center">
				<td width="200" align="center">
					<a class="btn btn-sm btn-success accept" data-id="<?php echo $instance->id ?>"><?php echo t('guilds.show.accept') ?></a>
					<a class="btn btn-sm btn-danger refuse" data-id="<?php echo $instance->id ?>"><?php echo t('guilds.show.refuse') ?></a>
				</td>
			</tr>
			<tr height="4"></tr>
		<?php } endforeach ?>
		</table>
		<div align="center" style="padding-top: 10px"><a class="btn btn-sm btn-danger remove_all" data-message="<?php echo t('guilds.remove_all')?>"><?php echo t('buttons.remover_all')?></a></div>
	<?php else: ?>
		<div align="center" style="padding-top: 10px"><b class="laranja" style="font-size:14px;"><?php echo t('friends.nothing2') ?></b></div>
	<?php endif ?>
	</div>
</div>
