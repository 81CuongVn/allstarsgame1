<?php echo partial('shared/title', array('title' => 'abilities.index.title', 'place' => 'abilities.index.title')) ?>
<?php foreach($specialities as $speciality){?>
<div class="ability-speciality-box upgrade" data-id="<?php echo $player_speciality->character_speciality_id?>" data-id2="<?php echo $speciality->id?>">
	<div class="content">
		<div class="image">
			<div class="lock"><span class="glyphicon glyphicon-lock"></span></div>
			<?php echo $player_speciality->image() ?>
		</div>
		<div class="name"><?php echo $player_speciality->description()->name ?></div>
		<div class="description"><?php echo $speciality->tooltip(null, true) ?></div>
		<div class="details">
			<div style="display: inline-block">
				<img src="<?php echo image_url('icons/for_mana.png') ?>" />
				<span style="font-size: 13px; color: #fff"><?php echo $speciality->consume_mana ?></span>
			</div>
			<div style="display: inline-block; padding-left: 5px;">
				<img src="<?php echo image_url('icons/esp.png') ?>" />
				<span style="font-size: 13px; color: #fff"><?php echo $speciality->cooldown ?></span>
			</div>
		</div>
	</div>
</div>
<?php }?>
<div class="clearfix"></div>