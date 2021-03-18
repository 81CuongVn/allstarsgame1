<?php if (isset($text_only) && $text_only): ?>
	<?php echo partial('shared/effect_tooltip', ['effects' => $effects, 'player' => $player]) ?>	
<?php else: ?>
	<div class="technique-data fix-lines" style="width: 280px">
		<span>Valores para Combate</span>
		<table width="100%">
			<tr>
				<td width="33%" align="center"><img src="<?php echo image_url('icons/for_mana.png') ?>" /></td>
				<td width="33%" align="center"><img src="<?php echo image_url('icons/esp.png') ?>" /></td>
				<td width="33%" align="center"><img src="<?php echo image_url('icons/dur.png') ?>" /></td>
			</tr>
			<tr>
				<td align="center"><?php echo $target->consume_mana ?></td>
				<td align="center"><?php echo $target->cooldown ?></td>
				<td align="center"><?php echo $target->effect_duration ?></td>
			</tr>
		</table>
		<div style="clear:both"></div>		
		<hr />
		<?php echo partial('shared/effect_tooltip', ['effects' => $effects, 'player' => $player]) ?>
		<?php foreach ($effects as $effect): ?>
			<?php if(!$effect->kill_with_one_hit && !$effect->copy_last_technique): ?>
				<hr />
				<span class="glyphicon glyphicon-star"></span>
				Não passa turno ao utilizar	
			<?php endif ?>
		<?php endforeach ?>
	</div>
<?php endif ?>
