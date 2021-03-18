<?php echo partial('shared/title', array('title' => 'support.ticket.title', 'place' => 'support.ticket.title')) ?>
<table>
	<tr>
		<td width="170" align="center" style="min-height: 90px">
			<?php if ($ticket->player_id): ?>
				<?php echo $ticket_player->character_theme()->first_image()->small_image() ?><br />
			<?php else: ?>
				<?php echo t('support.ticket.no_player') ?>
			<?php endif ?>
			<?php if($_SESSION['universal']): ?>
				<div style="line-height: 18px">
                    <?php if ($ticket->player_id): ?>
	                    <?php echo $ticket_player->name ?><br />
                    <?php endif ?>
					<?php echo t('support.ticket.user') . ': ' . $ticket->user_id ?><br />
					<?php if ($ticket->player_id): ?>
						<?php echo t('support.ticket.character') . ': ' . $ticket_player->id ?>
						<br />
						Batalha PVP: <?php echo $ticket_player->battle_pvp_id ? "Sim -> " . $ticket_player->battle_pvp_id : 'Não' ?>
						<br />
						Batalha NPC: <?php echo $ticket_player->battle_npc_id ? "Sim -> " . $ticket_player->battle_npc_id : 'Não' ?>
						<br />
						Nível: <?php echo $ticket_player->level ?>
					<?php endif ?>
				</div>
			<?php endif ?>
		</td>
		<td width="560" style="min-height: 90px">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td width="25%" height="30" align="center"><b class="laranja" style="font-size: 13px;"><?php echo t('support.ticket.email') ?></b></td>
					<td width="25%" align="center"><b class="laranja" style="font-size: 13px;"><?php echo t('support.ticket.category') ?></b></td>
					<td width="25%" align="center"><b class="laranja" style="font-size: 13px;"><?php echo t('support.ticket.status') ?></b></td>
					<td width="25%" align="center"><b class="laranja" style="font-size: 13px;"><?php echo t('support.ticket.last_replied_at') ?></b></td>
				</tr>
				<tr>
					<td width="25%" height="30" align="center"><?php echo $ticket_user->email ?></td>
					<td width="25%" align="center"><?php echo SupportTicketCategory::find_first($ticket->support_ticket_category_id)->name ?></td>
					<td width="25%" align="center"><?php echo SupportTicketStatus::find_first($ticket->support_ticket_status_id)->name ?></td>
					<td width="25%" align="center"><?php echo $ticket->last_replied_at ? date('d/m/Y H:i:s', strtotime($ticket->last_replied_at)) : '--' ?></td>
				</tr>
				<tr>
					<td width="25%" height="30" align="center"><b class="laranja" style="font-size: 13px;"><?php echo t('support.ticket.created_at') ?></b></td>
					<td width="75%" align="center" colspan="2"><b class="laranja" style="font-size: 13px;"><?php echo t('support.ticket.browser') ?></b></td>
					<td width="25%" align="center"><b class="laranja" style="font-size: 13px;"><?php echo t('support.ticket.last_replied_id') ?></b></td>
				</tr>
				<tr>
					<td width="25%" height="30" align="center"><?php echo date('d/m/Y H:i:s', strtotime($ticket->created_at)) ?></td>
					<td width="55%" align="center" colspan="2"><?php echo $ticket->user_agent ?></td>
					<td width="25%" align="center"><?php echo $ticket->last_replied_id ? User::find($ticket->last_replied_id)->name : '--' ?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<?php if ($_SESSION['universal'] && !$_SESSION['orig_user_id']): ?>
    <hr />
	<div align="center" id="support-command-extras">
		<a href="javascript:;" class="btn btn-sm btn-primary alternate" data-id="<?php echo $ticket->id ?>">Acessar Conta</a>
		<?php if ($ticket->player_id): ?>
			<a href="javascript:;" class="btn btn-sm btn-primary fullalternate" data-id="<?php echo $ticket->id ?>">Acessar Conta & Personagem</a>
		<?php endif ?>
	</div>
<?php endif ?>
<hr />
<div id="support-ticket-base-content">
	<b class="azul" style="font-size:14px"><?php echo nl2br($ticket->title) ?></b><br /><br />
	<?php echo nl2br($ticket->content) ?>
	<hr class="inside" />
	<?php if (sizeof($attachments)): ?>
		<p><b><?php echo t('support.ticket.attachments') ?>:</b></p>
		<ul>
			<?php foreach ($attachments as $attachment): ?>
				<li><a href="<?php echo resource_url('uploads/support/' . $attachment->filename) ?>"><span class="glyphicon glyphicon-link"></span>&nbsp;<?php echo $attachment->filename ?></a></li>
			<?php endforeach ?>
		</ul>
		<hr />
	<?php endif ?>
</div>
<?php if (sizeof($replies)): ?>
	<?php foreach ($replies as $reply): ?>
		<?php $reply_attachments = $reply->uploads() ?>
		<div class="support-ticket-reply">
			<span class="laranja"><?php echo t('support.ticket.replied_by', ['user' => $reply->user()->name]) ?> - 
			<?php echo date('d/m/Y H:i:s', strtotime($reply->created_at)) ?></span>
			<div class="content">
				<br /><?php echo nl2br($reply->content) ?>
			</div>
			<hr class="inside" />
			<?php if (sizeof($reply_attachments)): ?>
				<p><b><?php echo t('support.ticket.attachments') ?>:</b></p>
				<ul>
					<?php foreach ($reply_attachments as $attachment): ?>
						<li><a href="<?php echo resource_url('uploads/support/' . $attachment->filename) ?>"><span class="glyphicon glyphicon-link"></span>&nbsp;<?php echo $attachment->filename ?></a></li>
					<?php endforeach ?>
				</ul>
				<hr />
			<?php endif ?>
		</div>
	<?php endforeach ?>
<?php endif ?>
<?php if ($ticket->support_ticket_status_id != 4): ?>
	<form class="form form-horizontal" action="<?php echo make_url('support/reply/' . $ticket->id) ?>" id="support-ticket-reply-form" method="post"  enctype="multipart/form-data">
		<?php if ($_SESSION['universal']): ?>
			<input type="hidden" name="close" value="0" />
		<?php endif ?>
		<div class="form-group row">
			<label class="control-label col-md-12" style="text-align: left"><?php echo t('support.open.form.description') ?></label>
			<br /><br />
			<div class="col-sm-12">
				<textarea name="content" class="form-control input-sm" rows="10" style="resize: none;"></textarea>
			</div>
		</div>
		<hr />
		<p class="laranja"><?php echo t('support.open.attachment_info') ?></p>
		<br /><br />
		<div class="form-group">
			<label class="col-sm-2 control-label"><?php echo t('support.open.form.attachment') ?></label>
			<div class="col-sm-10">
				<input type="file" name="attachments[]" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label"><?php echo t('support.open.form.attachment') ?></label>
			<div class="col-sm-10">
				<input type="file" name="attachments[]" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label"><?php echo t('support.open.form.attachment') ?></label>
			<div class="col-sm-10">
				<input type="file" name="attachments[]" />
			</div>
		</div>
		<div class="clearfix"></div>
		<div align="center" style="margin-top: 20px; position: relative;">
			<?php if ($_SESSION['universal']): ?>
				<input type="button" class="btn btn-sm btn-primary reply-close" value="<?php echo t('support.ticket.reply_close') ?>" />
			<?php endif ?>
			<input type="button" class="btn btn-sm btn-primary reply" value="<?php echo t('support.ticket.reply') ?>" />
		</div>
		<div class="clearfix"></div>
	</form>
<?php elseif($ticket->support_ticket_status_id == 4 && $_SESSION['universal']): ?>
	<div align="center">
		<form action="<?php echo make_url('support/reopen/' . $ticket->id) ?>" id="support-ticket-reply-form" method="post">
			<input type="button" class="btn btn-sm btn-primary reopen" value="<?php echo t('support.ticket.reopen') ?>" />
		</form>
	</div>
<?php endif ?>