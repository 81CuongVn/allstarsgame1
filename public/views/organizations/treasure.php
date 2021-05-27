<?php echo partial('shared/title', array('title' => 'menus.organization_treasure', 'place' => 'menus.organization_treasure')) ?>
<?php
	echo partial('shared/info', [
		'id'		=> 1,
		'title'		=> 'organizations.help_title',
		'message'	=> t('organizations.help_description')
	]);
?>
<br />
<?php foreach ($treasure_list->result() as $treasures): ?>
	<div class="ability-speciality-box" data-id="<?php echo $treasures->id?>" style="width: 237px !important; height: 275px !important">
	<div>
		<div class="image">
			<img src="<?php echo image_url('treasures/'.$treasures->id.'.png') ?>" />

		</div>
		<div class="name legendary" style="height: 30px !important;">
			<?php echo $treasures->name ?><br />
			<span style="font-size:12px;" class="amarelo">( Você ganhou <?php echo highamount($treasures->total); ?> vez(es) )</span>
		</div>
		<div class="description" style="height: 40px !important;">
		<?php if($treasures->exp){?>
			<li><?php echo t('treasure.show.desc')?>: <?php echo highamount($treasures->exp); ?> <?php echo t('treasure.show.exp')?></li>
		<?php }?>
		<?php if($treasures->enchant_points){?>
			<li><?php echo t('treasure.show.desc')?>: <?php echo highamount($treasures->quantity); ?> <?php echo t('treasure.show.enchant')?></li>
		<?php }?>
		<?php if($treasures->currency){?>
			<li><?php echo t('treasure.show.desc')?>: <?php echo highamount($treasures->currency); ?> <?php echo t('currencies.' . $player->character()->anime_id) ?></li>
		<?php }?>
		<?php if($treasures->credits){?>
			<li><?php echo t('treasure.show.desc')?>: <?php echo highamount($treasures->credits); ?> <?php echo t('treasure.show.credits')?></li>
		<?php }?>
		<?php if($treasures->equipment && $treasures->equipment == 1){?>
			<li><?php echo t('treasure.show.desc')?>: <?php echo t('treasure.show.equipment1')?></li>
		<?php }?>
		<?php if($treasures->equipment && $treasures->equipment == 2){?>
			<li><?php echo t('treasure.show.desc')?>: <?php echo t('treasure.show.equipment2')?></li>
		<?php }?>
		<?php if($treasures->equipment && $treasures->equipment == 3){?>
			<li><?php echo t('treasure.show.desc')?>: <?php echo t('treasure.show.equipment3')?></li>
		<?php }?>
		<?php if($treasures->equipment && $treasures->equipment == 4){?>
			<li><?php echo t('treasure.show.desc')?>: <?php echo t('treasure.show.equipment4')?></li>
		<?php }?>
		<?php if ($treasures->equipment && $treasures->equipment == 5) { ?>
			<li><?php echo t('treasure.show.desc')?>: <?php echo t('treasure.show.equipment5')?></li>
		<?php } ?>
		<?php if($treasures->pets  && $treasures->item_id){?>
			<li><?php echo t('treasure.show.desc')?>: <?php echo t('treasure.show.pet')?> "<?php echo Item::find($treasures->item_id)->description()->name ?>"</li>
		<?php }?>
		<?php if($treasures->character_theme_id){?>
			<li><?php echo t('treasure.show.desc')?>: <?php echo t('treasure.show.theme')?> "<?php echo CharacterTheme::find($treasures->character_theme_id)->description()->name ?>"</li>
		<?php }?>
		<?php if($treasures->character_id){?>
			<li><?php echo t('treasure.show.desc')?>: <?php echo t('treasure.show.character')?> "<?php echo Character::find($treasures->character_id)->description()->name ?>"</li>
		<?php }?>
		<?php if($treasures->headline_id){?>
			<li><?php echo t('treasure.show.desc')?>: <?php echo t('treasure.show.headline')?> "<?php echo Headline::find($treasures->headline_id)->description()->name ?>"</li>
		<?php }?>
		<?php if(!$treasures->pets && $treasures->item_id){?>
			<li>
				<?php echo t('treasure.show.desc')?>:
				<?php
					$reward	= Item::find($treasures->item_id);
					$reward->set_anime($player->character()->anime_id);
					echo highamount($treasures->quantity) . " " . $reward->description()->name;
				?>
			</li>
		<?php }?>
		<br />
		</div>
		<div class="details">
			<img src="<?php echo image_url("icons/treasure.png" ) ?>" width="26" height="26" />
			<span class="amarelo_claro" style="font-size: 16px; margin-left: 5px; top: 2px; position: relative">
				<?php echo highamount($total_treasure->treasure_atual); ?> / <?php echo highamount($treasures->treasure_total); ?>
			</span>
		</div>
		<div class="button" style="position:relative; top: 15px;">
			<?php if ($total_treasure->treasure_atual >= $treasures->treasure_total && $can_accept) { ?>
				<button type="button" class="treasures_change btn btn-sm btn-primary" data-mode="<?php echo $treasures->id?>"><?php echo t('treasure.show.change') ?></button>
			<?php }else{ ?>
				<button type="button" class="btn btn-sm btn-danger btn-disabled" disabled><?php echo t('treasure.show.change') ?></button>
			<?php } ?>
		</div>
	</div>
</div>
<?php endforeach;?>
