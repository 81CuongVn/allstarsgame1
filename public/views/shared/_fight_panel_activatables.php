<?php if ($who->character_ability_id || $who->character_speciality_id): ?>
	<div id="activatables" class="activatables-<?php echo $who->id == Player::get_instance()->id ? 'player' : 'enemy' ?>">
		<?php if ($who->character_ability_id): ?>
			<?php
				$ability	= $who->ability();
			?>
			<div class="item left ability" id="infinity-container-<?php echo $ability->id ?>" data-id="<?php echo $ability->id ?>" data-item="ability">
				<div class="technique-popover" data-source="#ability-container-<?php echo $who->id ?>" data-title="<?php echo $ability->description()->name ?>" data-trigger="click" data-placement="bottom"><?php echo $ability->image() ?></div>
				<div id="ability-container-<?php echo $who->id ?>" class="technique-container">
					<?php echo $ability->tooltip($who) ?>
				</div>
			</div>
		<?php endif ?>

		<?php if ($who->character_speciality_id): ?>
			<?php
				$speciality	= $who->speciality();
			?>
			<div class="item right speciality" id="infinity-container-<?php echo $speciality->id ?>" data-id="<?php echo $speciality->id ?>" data-item="speciality">
				<div class="technique-popover buff" data-source="#speciality-container-<?php echo $who->id ?>" data-title="<?php echo $speciality->description()->name ?>" data-trigger="click" data-placement="bottom"><?php echo $speciality->image() ?></div>
				<div id="speciality-container-<?php echo $who->id ?>" class="technique-container">
					<?php echo $speciality->tooltip($who) ?>
				</div>
			</div>
		<?php endif ?>

		<?php if ($pet = $who->get_active_pet()): ?>
			<?php
				$pet_item	= $pet->item();
			?>
			<div class="pet">
				<div class="technique-popover" data-source="#pet-container-<?php echo $who->id ?>" data-title="<?php echo $pet_item->description()->name ?>" data-trigger="click" data-placement="bottom"><?php echo $pet_item->image() ?></div>
				<div id="pet-container-<?php echo $who->id ?>" class="technique-container">
					<?php echo $pet_item->tooltip($who) ?>
				</div>
			</div>
		<?php endif ?>
	</div>
<?php endif ?>
