<!-- Aqui é o menu da esquerda para não ter que fazer verificações fora da controller do perfil -->
<div style="position: absolute; left:-283px; top: 11px;">
	<?php
	if ($player) {
		$ranking 				= $player->ranking();
		$ranking_achievement 	= $player->ranking_achievement();
		$ranking_guild 			= $player->ranking_guild();
		$ranking_account 		= $player->ranking_account();
	}
	?>
	<div style="width: 242px; height: 250px; text-align: center;">
		<?php if (!$player) { ?>
			<img src="<?=image_url('profile/unknown.jpg');?>"/>
		<?php } else { ?>
			<?=$player->profile_image();?>
		<?php } ?>
		<div align="center" class="nome-personagem">
			<?=($player ? $player->name : '???????');?><br />
			<span class="cinza" style="font-size: 12px">
				<?=($player ? $player->graduation()->description($player->character()->anime_id)->name : '???????????????');?>
			</span>
		</div>
	</div>
	<div style="color: #FFFFFF; width: 240px; text-align: left;">
		<?php
		if ($player) {
			$ability	= $player->ability();
			$speciality	= $player->speciality();
			$pet   		= $player->get_active_pet();
		}
		?>
		<div style="width: 288px; clear: left; float: left; position: relative; left: -26px;">
			<div style="float: left">
				<img src="<?=image_url("battle/left.png" );?>" />
				<?php if (!$player) { ?>
					<div style="position: absolute; top: 33px; left: 17px; z-index: 100">
						<img src="<?=image_url("pet_unknown.png" );?>" />
					</div>
				<?php } else { ?>
					<div class="technique-popover" data-source="#ability-container-<?=$player->id;?>" data-title="<?=$ability->description()->name;?>" data-trigger="click" data-placement="bottom" style="position: absolute; top: 33px; left: 17px; z-index: 100">
						<?=$ability->image();?>
					</div>
					<div id="ability-container-<?=$player->id;?>" class="technique-container">
						<?=$ability->tooltip($player);?>
					</div>
				<?php } ?>
			</div>
			<?php if ($player && $pet) { ?>
				<?php $pet_item = $pet->item() ?>
				<div style="position: absolute; top: 35px; left: 77px;">
					<div class="technique-popover" data-source="#pet-container-<?=$player->id;?>" data-title="<?=$pet_item->description()->name;?>" data-trigger="click" data-placement="bottom">
						<?=$pet_item->image();?>
					</div>
					<div id="pet-container-<?=$player->id;?>" class="technique-container">
						<?=$pet_item->tooltip($player);?>
					</div>
				</div>
			<?php } else { ?>
				<div style="position: absolute; top: 35px; left: 75px;">
					<img src="<?=image_url("battle/center.png" );?>" />
				</div>
			<?php } ?>

			<div style="position: absolute; width: 288px; top: 75px; text-align: center; z-index: 1">
				<b style="font-size: 13px !important">
					Nível <?=($player ? highamount($player->level) : '??');?>
				</b>
			</div>

			<div style="float: right">
				<img src="<?=image_url("battle/right.png");?>" />
				<?php if (!$player) { ?>
					<div style="position: absolute; top: 33px; left: 207px; z-index: 100">
						<img src="<?=image_url("pet_unknown.png" );?>" />
					</div>
				<?php } else { ?>
					<div class="technique-popover buff" data-source="#speciality-container-<?=$player->id;?>" data-title="<?=$speciality->description()->name;?>" data-trigger="click" data-placement="bottom" style="position: absolute; top: 33px; left: 207px; z-index: 100">
						<?=$speciality->image();?>
					</div>
					<div id="speciality-container-<?=$player->id;?>" class="technique-container">
						<?=$speciality->tooltip($player);?>
					</div>
				<?php } ?>
			</div>
		</div>

		<?php if ($player) { ?>
			<select class="form-control input-sm select2">
				<option value="0"><?=t('characters.no_headline') ?></option>
				<?php foreach ($player->user()->headlines() as $user_headline): ?>
					<option value="<?=$user_headline->id;?>" <?=($player->headline_id == $user_headline->headline_id ? 'selected' : '');?>><?=$user_headline->headline()->description()->name;?></option>
				<?php endforeach ?>
			</select>
		<?php } ?>

		<div class="bg_menu_esquerdo">
			<div class="menu_esquerdo_divisao">
				<b class="amarelo"><?=t('global.anime');?></b>
				<b class=""><?=($player ? $player->character()->anime()->description()->name : '-');?></b>
			</div>
			<div class="menu_esquerdo_divisao">
				<b class="amarelo"><?=t('guilds.faction');?></b>
				<b class=""><?=($player ? $player->faction()->description()->name : '-');?></b>
			</div>
		</div>
		<div class="bg_menu_esquerdo">
			<div class="menu_esquerdo_divisao">
				<b class="amarelo"><?=t('global.points');?></b>
				<b class=""><?=($player && $ranking ? highamount($ranking->score) : "-");?></b>
			</div>
			<div class="menu_esquerdo_divisao">
				<b class="amarelo">Rank Geral</b>
				<b class=""><?=($player && $ranking ? highamount($ranking->position_general) . "&ordm;" : "-");?></b>
			</div>
		</div>
		<div class="bg_menu_esquerdo">
			<div class="menu_esquerdo_divisao">
				<b class="amarelo">Rank Facção</b>
				<b class=""><?=($player && $ranking ? highamount($ranking->position_faction) . "&ordm;" : "-");?></b>
			</div>
			<div class="menu_esquerdo_divisao">
				<b class="amarelo">Rank Conta</b>
				<b class=""><?=($player && $ranking_account ? highamount($ranking_account->position_general) . "&ordm;" : "-");?></b>
			</div>
		</div>
		<div class="bg_menu_esquerdo">
			<div class="menu_esquerdo_divisao">
				<b class="amarelo">Rank Org.</b>
				<b class=""><?=($player && $ranking_guild ? highamount($ranking_guild->position_general) . "&ordm;" : "-");?></b>
			</div>
			<div class="menu_esquerdo_divisao">
				<b class="amarelo">Rank Conquista</b>
				<b class=""><?=($player && $ranking_achievement ? highamount($ranking_achievement->position_general) . "&ordm;" : "-");?></b>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
