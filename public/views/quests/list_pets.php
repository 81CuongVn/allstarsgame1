<div id="pet-list">
	<?php
		$others		= '';
		$actives	= '';
	?>
	<?php foreach ($pets as $pet): ?>
		<?php
			$class		= '';
			$attrbiutes	= '';
			$active		= false;


			ob_start();
		?>
		<div class="pet-box <?php echo $class ?>" data-item="<?php echo $pet->id ?>" style="height: 280px !important" data-id="<?php echo $pet->id ?>" data-quest_id="<?php echo $quest_id?>" data-counter="<?php echo $counter?>">
			<div class="content" data-title="<?php echo $pet->description()->name ?>" data-trigger="click" data-placement="bottom">
				<div class="image">
					<div class="lock"><span class="glyphicon glyphicon-lock"></span></div>
					<?php echo $pet->image() ?>
				</div>
				<div class="name <?php echo $pet->rarity ?>" style="height: 33px !important">
					<?php echo $pet->description()->name ?><br />
					<span style="font-size: 11px">(<?php echo $pet->anime_description($pet->description()->anime_id)->name ?>)</span>
				</div>
				<div class="details">
					<div class="pet-tooltip">
						<?php
							$info_pet = $player->happiness_int($pet->id);
							if ($info_pet) {
								$happiness = $info_pet->happiness;
								$exp_pet  =	$info_pet->exp;
							} else {
								$happiness = 0;
								$exp_pet  =	0;
							}
						?>
						<?php echo $player->happiness($pet->id)?><br />
						<?php echo $happiness ?> / 100
						<?php
							switch ($pet->rarity) {
								case "common":
								 	$exp_total = 5000;
								 break;
								 case "rare":
									 $exp_total = 15000;
								 break;
								 case "legendary":
									$exp_total = 50000;
								 break;
							}
						?>
						<?php if ($pet->rarity != "mega") { ?>
							<div style="margin-top:10px">
								<?=pet_exp_bar($exp_pet, $exp_total, 150, highamount($exp_pet) . '/' . highamount($exp_total));?>
							</div>
						<?php } ?>
					</div>
				</div>
				<div>
					<?php echo $pet->tooltip() ?>
				</div>
			</div>
		</div>

		<?php
			if ($active) {
				$actives	.= ob_get_clean();
			} else {
				$others		.= ob_get_clean();
			}
		?>
	<?php
		endforeach
	?>
	<?php echo $actives . $others ?>
	<div class="clearfix"></div><br />
</div>
