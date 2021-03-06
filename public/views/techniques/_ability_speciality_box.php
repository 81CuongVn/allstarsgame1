<?php
	if (is_a($target, 'PlayerCharacterAbility')) {
		$class	= 'ability';
		$class2	= 'ability';

		if ($player->character_ability_id == $target->character_ability_id) {
			$class	.= ' active';
		}
	} else {
		$class	= 'speciality';
		$class2	= 'speciality';

		if ($player->character_speciality_id == $target->character_speciality_id) {
			$class	.= ' active';
		}
	}
?>
<div oncontextmenu="return false;" style="cursor:pointer" class="ability-speciality-box <?php echo $class ?> ability-speciality-box2" data-id="<?php echo $class2 == "ability" ? $target->character_ability_id : $target->character_speciality_id ?>" data-url="<?php echo make_url('techniques#change_'.$class2) ?>" data-url2="<?php echo 'techniques#change_'.$class2 ?>">
	<div class="content">
		<div class="image">
			<div class="lock"><span class="glyphicon glyphicon-lock"></span></div>
			<?php echo $target->image() ?>
		</div>
		<div class="name"><?php echo $target->description()->name ?></div>
		<div class="description"><?php echo $target->tooltip(null, true) ?></div>
		<div class="details">
			<div style="display: inline-block">
				<img src="<?php echo image_url('icons/for_mana.png') ?>" />
				<span style="font-size: 13px; color: #fff"><?php echo $target->consume_mana ?></span>
			</div>
			<div style="display: inline-block; padding-left: 5px;">
				<img src="<?php echo image_url('icons/esp.png') ?>" />
				<span style="font-size: 13px; color: #fff"><?php echo $target->cooldown ?></span>
			</div>
		</div>
	</div>
</div>