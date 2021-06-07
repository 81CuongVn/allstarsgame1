<ul>
<?php foreach ($effects as $effect): ?>
	<?php
	$direction_player	= '.self';
	$direction_enemy	= '.enemy';
	$effect_base		= 'effects';
	$current_extra		= 0;
	$is_percent			= false;

	if (!isset($fixed_effect)) {
		$fixed_effect	= false;
	}

	if ($fixed_effect) {
		$effect_base		= 'fixed_effects';
	}

	$word	= '';

	if ($effect->effect_type == 'percent') {
		$word		= '%';
		$is_percent	= true;
	}

	$turn_type	= '';
	if ($effect->duration > 1) {
		$turn_type	= t($effect_base . '.turns', ['turns' => $effect->duration]);
	}
	?>
	<ul>
		<?php if ($effect->bleeding): ?>
			<li>
				<?php
				if ($player) {
					$current_extra	= $is_percent ? $player->get_sum_effect('increase_bleeding_damage_percent') : $player->get_sum_effect('increase_bleeding_damage');
				}
				?>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.bleeding.' . $effect->effect_direction . ($effect->bleeding > 0 ? $direction_player : $direction_enemy), ['value' => abs($effect->bleeding  + $current_extra) . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if ($effect->stun): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.stun.' . $effect->effect_direction . ($effect->stun > 0 ? $direction_player : $direction_enemy), ['value' => abs($effect->stun) . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if ($effect->slowness): ?>
			<li>
				<?php
				if ($player) {
					$current_extra	= $is_percent ? $player->get_sum_effect('increase_slowness_damage_percent') : $player->get_sum_effect('increase_slowness_damage');
				}
				?>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.slowness.' . $effect->effect_direction . ($effect->slowness > 0 ? $direction_player : $direction_enemy), ['value' => abs($effect->slowness + $current_extra) . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->confusion): ?>
			<li>
				<?php
					if ($player) {
						$current_extra	= $is_percent ? $player->get_sum_effect('increase_confusion_damage_percent') : $player->get_sum_effect('increase_confusion_damage');
					}
				?>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.confusion.' . $effect->effect_direction . ($effect->confusion > 0 ? $direction_player : $direction_enemy), ['value' => abs($effect->confusion + $current_extra) . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->for_atk): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.for_atk.' . $effect->effect_direction . ($effect->for_atk > 0 ? $direction_player : $direction_enemy), ['value' => abs($effect->for_atk) . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->for_def): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.for_def.' . $effect->effect_direction . ($effect->for_def > 0 ? $direction_player : $direction_enemy), ['value' => abs($effect->for_def) . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->for_crit): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.for_crit.' . $effect->effect_direction . ($effect->for_crit > 0 ? $direction_player : $direction_enemy), ['value' => abs($effect->for_crit)]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->for_crit_inc): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.for_crit_inc.' . $effect->effect_direction . ($effect->for_crit_inc > 0 ? $direction_player : $direction_enemy), ['value' => abs($effect->for_crit_inc)]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->for_abs): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.for_abs.' . $effect->effect_direction . ($effect->for_abs > 0 ? $direction_player : $direction_enemy), ['value' => abs($effect->for_abs)]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->for_abs_inc): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.for_abs_inc.' . $effect->effect_direction . ($effect->for_abs_inc > 0 ? $direction_player : $direction_enemy), ['value' => abs($effect->for_abs_inc)]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->for_prec): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.for_prec.' . $effect->effect_direction . ($effect->for_prec > 0 ? $direction_player : $direction_enemy), ['value' => abs($effect->for_prec)]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->for_init): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.for_init.' . $effect->effect_direction . ($effect->for_init > 0 ? $direction_player : $direction_enemy), ['value' => abs($effect->for_init) . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->for_hit): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.for_hit.' . $effect->effect_direction . ($effect->for_hit > 0 ? $direction_player : $direction_enemy), ['value' => abs($effect->for_hit) . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->attack_speed): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.attack_speed.' . $effect->effect_direction . ($effect->attack_speed > 0 ? $direction_player : $direction_enemy), ['value' => abs($effect->attack_speed) . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->next_mana_cost): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.next_mana_cost.' . $effect->effect_direction . ($effect->next_mana_cost > 0 ? $direction_player : $direction_enemy), ['value' => abs($effect->next_mana_cost) . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->lock_random_technique): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.lock_random_technique.' . $effect->effect_direction . ($effect->lock_random_technique > 0 ? $direction_player : $direction_enemy)) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->copy_last_technique): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.copy_last_technique.' . $effect->effect_direction . ($effect->copy_last_technique > 0 ? $direction_player : $direction_enemy)) ?>
				<?php echo $turn_type ?>
			</li>

			<?php if (isset($player) && $player && ($player->battle_pvp_id || $player->battle_npc_id)): ?>
				<?php
					$copied		= false;
					$copied_id	= false;

					if ($player->battle_pvp_id) {
						$enemy		= $player->battle_pvp()->enemy();
						$copied_id	= SharedStore::G('last_battle_item_of_' . $enemy->id);
					} else {
						$enemy		= $player->get_npc();
						if(!$enemy){
							$enemy		= $player->get_npc_challenge();
						}
						$copied_id	= SharedStore::G('last_battle_npc_item_of_' . $player->id);
					}

					if ($copied_id) {
						$copied	= $enemy->get_technique($copied_id);

						if ($player->battle_pvp_id) {
							$copied	= $copied->item();
						}
					}
				?>
				<?php if ($copied): ?>
					<li>Última técnica: <?php echo $copied->description()->name ?>
				<?php echo $turn_type ?>
			</li>
				<?php endif ?>
			<?php endif ?>
		<?php endif ?>
		<?php if($effect->last_hit_dont_die): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.last_hit_dont_die.' . $effect->effect_direction . ($effect->last_hit_dont_die > 0 ? $direction_player : $direction_enemy)) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->null_next_attack): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.null_next_attack.' . $effect->effect_direction . ($effect->null_next_attack > 0 ? $direction_player : $direction_enemy)) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->turns_attack_to_neutral): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.turns_attack_to_neutral.' . $effect->effect_direction . ($effect->turns_attack_to_neutral > 0 ? $direction_player : $direction_enemy)) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->turns_attack_to_elemental): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.turns_attack_to_elemental.' . $effect->effect_direction . ($effect->turns_attack_to_elemental > 0 ? $direction_player : $direction_enemy)) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->turns_attack_to_fighter): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.turns_attack_to_fighter.' . $effect->effect_direction . ($effect->turns_attack_to_fighter > 0 ? $direction_player : $direction_enemy)) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->turns_attack_to_magic): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.turns_attack_to_magic.' . $effect->effect_direction . ($effect->turns_attack_to_magic > 0 ? $direction_player : $direction_enemy)) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->turns_attack_to_warrior): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.turns_attack_to_warrior.' . $effect->effect_direction . ($effect->turns_attack_to_warrior > 0 ? $direction_player : $direction_enemy)) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->turns_attack_to_ranger): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.turns_attack_to_ranger.' . $effect->effect_direction . ($effect->turns_attack_to_ranger > 0 ? $direction_player : $direction_enemy)) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->removes_stun): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.removes_stun.' . $effect->effect_direction . ($effect->removes_stun > 0 ? $direction_player : $direction_enemy)) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->remove_slowness): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.remove_slowness.' . $effect->effect_direction . ($effect->remove_slowness > 0 ? $direction_player : $direction_enemy)) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->remove_bleeding): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.remove_bleeding.' . $effect->effect_direction . ($effect->remove_bleeding > 0 ? $direction_player : $direction_enemy)) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->remove_confusion): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.remove_confusion.' . $effect->effect_direction . ($effect->remove_confusion > 0 ? $direction_player : $direction_enemy)) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->double_bleeding_chance): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.double_bleeding_chance.' . $effect->effect_direction . ($effect->double_bleeding_chance > 0 ? $direction_player : $direction_enemy)) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->double_stun_chance): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.double_stun_chance.' . $effect->effect_direction . ($effect->double_stun_chance > 0 ? $direction_player : $direction_enemy)) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->double_slowness_chance): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.double_slowness_chance.' . $effect->effect_direction . ($effect->double_slowness_chance > 0 ? $direction_player : $direction_enemy)) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->double_confusion_chance): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.double_confusion_chance.' . $effect->effect_direction . ($effect->double_confusion_chance > 0 ? $direction_player : $direction_enemy)) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->increase_bleeding_duration): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.increase_bleeding_duration.' . $effect->effect_direction . ($effect->increase_bleeding_duration > 0 ? $direction_player : $direction_enemy), ['value' => abs($effect->increase_bleeding_duration) . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->increase_slowness_duration): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.increase_slowness_duration.' . $effect->effect_direction . ($effect->increase_slowness_duration > 0 ? $direction_player : $direction_enemy), ['value' => abs($effect->increase_slowness_duration) . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->increase_confusion_duration): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.increase_confusion_duration.' . $effect->effect_direction . ($effect->increase_confusion_duration > 0 ? $direction_player : $direction_enemy), ['value' => abs($effect->increase_confusion_duration) . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->remove_attack_weakness): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.remove_attack_weakness.' . $effect->effect_direction . ($effect->remove_attack_weakness > 0 ? $direction_player : $direction_enemy)) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->double_strong_effect): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.double_strong_effect.' . $effect->effect_direction . ($effect->double_strong_effect > 0 ? $direction_player : $direction_enemy)) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->heals_life): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.heals_life.' . $effect->effect_direction . ($effect->heals_life > 0 ? $direction_player : $direction_enemy), ['value' => abs($effect->heals_life) . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->heals_mana): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.heals_mana.' . $effect->effect_direction . ($effect->heals_mana > 0 ? $direction_player : $direction_enemy), ['value' => abs($effect->heals_mana) . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->renew_random_cooldown): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.renew_random_cooldown.' . $effect->effect_direction . ($effect->renew_random_cooldown > 0 ? $direction_player : $direction_enemy)) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->next_is_critical): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.next_is_critical.' . $effect->effect_direction . ($effect->next_is_critical > 0 ? $direction_player : $direction_enemy)) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->next_is_absorb): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.next_is_absorb.' . $effect->effect_direction . ($effect->next_is_absorb > 0 ? $direction_player : $direction_enemy)) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->next_is_precise): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.next_is_precise.' . $effect->effect_direction . ($effect->next_is_precise > 0 ? $direction_player : $direction_enemy)) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->next_will_hit): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.next_will_hit.' . $effect->effect_direction . ($effect->next_will_hit > 0 ? $direction_player : $direction_enemy)) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->elemental_damage): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.elemental_damage.' . $effect->effect_direction . ($effect->elemental_damage > 0 ? $direction_player : $direction_enemy), ['value' => $effect->elemental_damage . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->fighter_damage): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.fighter_damage.' . $effect->effect_direction . ($effect->fighter_damage > 0 ? $direction_player : $direction_enemy), ['value' => $effect->fighter_damage . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->magic_damage): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.magic_damage.' . $effect->effect_direction . ($effect->magic_damage > 0 ? $direction_player : $direction_enemy), ['value' => $effect->magic_damage . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->warrior_damage): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.warrior_damage.' . $effect->effect_direction . ($effect->warrior_damage > 0 ? $direction_player : $direction_enemy), ['value' => $effect->warrior_damage . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->ranger_damage): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.ranger_damage.' . $effect->effect_direction . ($effect->ranger_damage > 0 ? $direction_player : $direction_enemy), ['value' => $effect->ranger_damage . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->bleeding_chance): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.bleeding_chance.' . $effect->effect_direction . ($effect->bleeding_chance > 0 ? $direction_player : $direction_enemy), ['value' => $effect->bleeding_chance . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->slowness_chance): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.slowness_chance.' . $effect->effect_direction . ($effect->slowness_chance > 0 ? $direction_player : $direction_enemy), ['value' => $effect->slowness_chance . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->confusion_chance): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.confusion_chance.' . $effect->effect_direction . ($effect->confusion_chance > 0 ? $direction_player : $direction_enemy), ['value' => $effect->confusion_chance . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->stun_chance): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.stun_chance.' . $effect->effect_direction . ($effect->stun_chance > 0 ? $direction_player : $direction_enemy), ['value' => $effect->stun_chance . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->strong_effect): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.strong_effect.' . $effect->effect_direction . ($effect->strong_effect > 0 ? $direction_player : $direction_enemy), ['value' => $effect->strong_effect . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->weak_effect): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.weak_effect.' . $effect->effect_direction . ($effect->weak_effect > 0 ? $direction_player : $direction_enemy), ['value' => $effect->weak_effect . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->heals_by_turn): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.heals_by_turn.' . $effect->effect_direction . ($effect->heals_by_turn > 0 ? $direction_player : $direction_enemy), ['value' => $effect->heals_by_turn . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->mana_through_turns): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.mana_through_turns.' . $effect->effect_direction . ($effect->mana_through_turns > 0 ? $direction_player : $direction_enemy), ['value' => $effect->mana_through_turns . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->exp_reward_extra): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.exp_reward_extra.' . $effect->effect_direction . ($effect->exp_reward_extra > 0 ? $direction_player : $direction_enemy), ['value' => $effect->exp_reward_extra . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->currency_reward_extra): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.currency_reward_extra.' . $effect->effect_direction . ($effect->currency_reward_extra > 0 ? $direction_player : $direction_enemy), ['value' => $effect->currency_reward_extra . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->item_find): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.item_find.' . $effect->effect_direction . ($effect->item_find > 0 ? $direction_player : $direction_enemy), ['value' => $effect->item_find . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->fragment_find): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.fragment_find.' . $effect->effect_direction . ($effect->fragment_find > 0 ? $direction_player : $direction_enemy), ['value' => $effect->fragment_find . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->grimoire_find): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.grimoire_find.' . $effect->effect_direction . ($effect->grimoire_find > 0 ? $direction_player : $direction_enemy), ['value' => $effect->grimoire_find . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->damage_in_bleeding): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.damage_in_bleeding.' . $effect->effect_direction . ($effect->damage_in_bleeding > 0 ? $direction_player : $direction_enemy), ['value' => $effect->damage_in_bleeding . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->damage_in_slowness): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.damage_in_slowness.' . $effect->effect_direction . ($effect->damage_in_slowness > 0 ? $direction_player : $direction_enemy), ['value' => $effect->damage_in_slowness . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->damage_in_stun): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.damage_in_stun.' . $effect->effect_direction . ($effect->damage_in_stun > 0 ? $direction_player : $direction_enemy), ['value' => $effect->damage_in_stun . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->generic_attack_damage): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.generic_attack_damage.' . $effect->effect_direction . ($effect->generic_attack_damage > 0 ? $direction_player : $direction_enemy), ['value' => $effect->generic_attack_damage . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->unique_attack_damage): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.unique_attack_damage.' . $effect->effect_direction . ($effect->unique_attack_damage > 0 ? $direction_player : $direction_enemy), ['value' => $effect->unique_attack_damage . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->attack_half_life): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.attack_half_life.' . $effect->effect_direction . ($effect->attack_half_life > 0 ? $direction_player : $direction_enemy), ['value' => $effect->attack_half_life . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->defense_half_life): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.defense_half_life.' . $effect->effect_direction . ($effect->defense_half_life > 0 ? $direction_player : $direction_enemy), ['value' => $effect->defense_half_life . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->damage_in_confusion): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.damage_in_confusion.' . $effect->effect_direction . ($effect->damage_in_confusion > 0 ? $direction_player : $direction_enemy), ['value' => $effect->damage_in_confusion . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>

		<?php if($effect->bonus_stamina_max): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.bonus_stamina_max.' . $effect->effect_direction . ($effect->bonus_stamina_max > 0 ? $direction_player : $direction_enemy), ['value' => $effect->bonus_stamina_max . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->next_turn_mana): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.next_turn_mana.' . $effect->effect_direction . ($effect->next_turn_mana > 0 ? $direction_player : $direction_enemy), ['value' => $effect->next_turn_mana . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->reduce_critical_damage): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.reduce_critical_damage.' . $effect->effect_direction . ($effect->reduce_critical_damage > 0 ? $direction_player : $direction_enemy), ['value' => (-$effect->reduce_critical_damage) . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->mana_half_life): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.mana_half_life.' . $effect->effect_direction . ($effect->mana_half_life > 0 ? $direction_player : $direction_enemy), ['value' => $effect->mana_half_life . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->bonus_gold_mission): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.bonus_gold_mission.' . $effect->effect_direction . ($effect->bonus_gold_mission > 0 ? $direction_player : $direction_enemy), ['value' => $effect->bonus_gold_mission . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->bonus_exp_mission): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.bonus_exp_mission.' . $effect->effect_direction . ($effect->bonus_exp_mission > 0 ? $direction_player : $direction_enemy), ['value' => $effect->bonus_exp_mission . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->bonus_stamina_heal): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.bonus_stamina_heal.' . $effect->effect_direction . ($effect->bonus_stamina_heal > 0 ? $direction_player : $direction_enemy), ['value' => $effect->bonus_stamina_heal . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->generic_defense): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.generic_defense.' . $effect->effect_direction . ($effect->generic_defense > 0 ? $direction_player : $direction_enemy), ['value' => $effect->generic_defense . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->increase_bleeding_damage): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.increase_bleeding_damage.' . $effect->effect_direction . ($effect->increase_bleeding_damage > 0 ? $direction_player : $direction_enemy), ['value' => $effect->increase_bleeding_damage . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->increase_slowness_damage): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.increase_slowness_damage.' . $effect->effect_direction . ($effect->increase_slowness_damage > 0 ? $direction_player : $direction_enemy), ['value' => $effect->increase_slowness_damage . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->increase_confusion_damage): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.increase_confusion_damage.' . $effect->effect_direction . ($effect->increase_confusion_damage > 0 ? $direction_player : $direction_enemy), ['value' => $effect->increase_confusion_damage . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->no_consume_stamina): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.no_consume_stamina.' . $effect->effect_direction . ($effect->no_consume_stamina > 0 ? $direction_player : $direction_enemy), ['value' => $effect->no_consume_stamina . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->next_turn_life): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.next_turn_life.' . $effect->effect_direction . ($effect->next_turn_life > 0 ? $direction_player : $direction_enemy), ['value' => $effect->next_turn_life . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->pets_find): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.pets_find.' . $effect->effect_direction . ($effect->pets_find > 0 ? $direction_player : $direction_enemy), ['value' => $effect->pets_find . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->low_technique_no_cost): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.low_technique_no_cost.' . $effect->effect_direction . ($effect->low_technique_no_cost > 0 ? $direction_player : $direction_enemy), ['value' => $effect->low_technique_no_cost . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->mid_technique_no_cooldown): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.mid_technique_no_cooldown.' . $effect->effect_direction . ($effect->mid_technique_no_cooldown > 0 ? $direction_player : $direction_enemy), ['value' => $effect->mid_technique_no_cooldown . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->high_technique_half_cost): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.high_technique_half_cost.' . $effect->effect_direction . ($effect->high_technique_half_cost > 0 ? $direction_player : $direction_enemy), ['value' => $effect->high_technique_half_cost . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->double_effect_pets): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.double_effect_pets.' . $effect->effect_direction . ($effect->double_effect_pets > 0 ? $direction_player : $direction_enemy), ['value' => $effect->double_effect_pets . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->enemy_absorb_reduction): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.enemy_absorb_reduction.' . $effect->effect_direction . ($effect->enemy_absorb_reduction > 0 ? $direction_player : $direction_enemy), ['value' => (-$effect->enemy_absorb_reduction) . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->half_weak_damage_chance): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.half_weak_damage_chance.' . $effect->effect_direction . ($effect->half_weak_damage_chance > 0 ? $direction_player : $direction_enemy), ['value' => $effect->half_weak_damage_chance . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->damage_increase_in_confusion): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.damage_increase_in_confusion.' . $effect->effect_direction . ($effect->damage_increase_in_confusion > 0 ? $direction_player : $direction_enemy), ['value' => $effect->damage_increase_in_confusion . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->damage_increase_in_slowness): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.damage_increase_in_slowness.' . $effect->effect_direction . ($effect->damage_increase_in_slowness > 0 ? $direction_player : $direction_enemy), ['value' => $effect->damage_increase_in_slowness . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->kill_with_one_hit): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.kill_with_one_hit.' . $effect->effect_direction . ($effect->kill_with_one_hit > 0 ? $direction_player : $direction_enemy), ['value' => $effect->kill_with_one_hit . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->cancel_regen_mana): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.cancel_regen_mana.' . $effect->effect_direction . ($effect->cancel_regen_mana > 0 ? $direction_player : $direction_enemy), ['value' => $effect->cancel_regen_mana . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->steal_health): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.steal_health.' . $effect->effect_direction . ($effect->steal_health > 0 ? $direction_player : $direction_enemy), ['turns' => 1, 'value' => $effect->steal_health . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->dodge_technique): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.dodge_technique.' . $effect->effect_direction . ($effect->dodge_technique > 0 ? $direction_player : $direction_enemy), ['value' => $effect->dodge_technique . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->remove_mana): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.remove_mana.' . $effect->effect_direction . ($effect->remove_mana > 0 ? $direction_player : $direction_enemy), ['value' => $effect->remove_mana . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
		<?php if($effect->steal_mana): ?>
			<li>
				<?php if (!$fixed_effect): ?>
					<?php echo t($effect_base . '.chance', ['value' => $effect->chance]) ?>
				<?php endif ?>
				<?php echo t($effect_base . '.steal_mana.' . $effect->effect_direction . ($effect->steal_mana > 0 ? $direction_player : $direction_enemy), ['value' => $effect->steal_mana . $word]) ?>
				<?php echo $turn_type ?>
			</li>
		<?php endif ?>
	</ul>
<?php endforeach ?>
</ul>
