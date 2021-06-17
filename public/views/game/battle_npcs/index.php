<?=partial('shared/title', [
	'title' => 'battles.npc.title',
	'place' => 'battles.npc.title'
]);?>
<?=partial('shared/info', [
	'id'		=> 1,
	'title'		=> 'battles.npcs.title',
	'message'	=> t('battles.npcs.description') . '<br /><br />' . exp_bar($current_npc_count, $max_npc_count, 175, ($current_npc_count . '/' . $max_npc_count))
]);?><br />
<div>
	<div class="pull-left">
		<?=$player->profile_image();?>
		<div align="center" class="nome-personagem">
			<?=$player->name;?><br />
			<span class="cinza" style="font-size: 12px">
				<?=$player->character()->anime()->description()->name;?>
			</span>
		</div>
	</div>
	<div style="float: left; padding-top: 20px">
		<img src="<?=image_url('battle/vs2.png');?>" />
	</div>
	<div class="pull-right">
		<?=$npc->profile_image();?>
		<div align="center" class="nome-personagem">
			<?=$npc->name;?><br />
			<span class="cinza" style="font-size: 12px"><?=$npc->character()->anime()->description()->name;?></span>
		</div>
		<div align="center">
			<select id="character_id" class="form-control input-sm select2" name="character_id" style="width:180px">
				<?php foreach ($animes as $anime): ?>
					<optgroup label="<?=$anime->description()->name;?>">
					<?php foreach ($anime->characters($_SESSION['universal'] ? '' : ' AND active = 1') as $character): ?>
						<?php if ($character->id == $player->character_id) { continue; } ?>
						<option value="<?=$character->id;?>"><?=$character->description()->name;?></option>
					<?php endforeach; ?>
					</optgroup>
				<?php endforeach; ?>
			</select><br />
			<a class="btn btn-sm btn-primary change-oponent" data-message="<?=t('battles.message');?>"><?=t('battles.trocar');?></a>
		</div>
	</div>
	<div class="clearfix"></div>
</div>
<div align="center">
	<?php if ($current_npc_count >= $max_npc_count): ?>
		<button type="button" class="btn btn-lg btn-warning btn-disabled" disabled><?=t('battles.npc.accept');?></button>
	<?php else: ?>
		<a href="javascript:void(0);" id="btn-enter-npc-battle" data-type="1" class="btn btn-lg btn-primary"><?=t('battles.npc.accept');?></a>
	<?php endif; ?>
</div>
