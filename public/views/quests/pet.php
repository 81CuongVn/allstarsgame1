<?=partial('shared/title', [
	'title'	=> 'quests.pet.title',
	'place'	=> 'quests.pet.title'
]);?>
<?php if(!$player_tutorial->missoes_seguidores) { ?>
	<script type="text/javascript">
		$(function () {
			var tour = new Tour({
				backdrop: true,
				page: 15,
				steps: [{
					element: ".msg-container",
					title: "Comande seus Mascotes",
					content: "Mande seus Mascotes em Missões para ganhar diversas recompensas! Fique atento aos Requerimentos, pois isso irá aumentar suas chances de Sucesso!",
					placement: "top"
				},{
					element: ".msg-container",
					title: "Atenção",
					content: "No dia que você criou seu personagem você não irá ter nenhuma Missão de Mascote, mas à meia noite você já irá receber suas dez primeiras missões! Faça quantas puder, pois todo dia você irá receber até ficar com dez novamente.",
					placement: "bottom"
				}]
			});

			tour.restart();
			tour.init(true);
			tour.start(true);
		});
	</script>
<?php } ?>
<?php
echo partial('shared/info', [
	'id'		=> 1,
	'title'		=> 'quests.pets.help_title',
	'message'	=> t('quests.pets.help_description')
]);
?>
<br />
<div id="pet-quests-list-content">
	<?php
	foreach ($quests as $quest) {
		$can_finish	= $quest->pet_wait_can_finish($quest->pet_quest_id);
		$success	= $quest->pet_success($quest->pet_quest_id);
	?>
	<div style="cursor:pointer; width: 236px !important; height: 255px;" class="ability-speciality-box ability">
		<div class="name" style="margin-bottom: -5px;">
			<b class="<?=$quest->quest()->dificulty;?>" style="font-size:14px; position: relative;"><?=$quest->description()->name;?></b>
		</div>
		<div>
			<?php $counter = 1; ?>
			<?php foreach($quest->npc() as $npc){?>
				<div class="technique-popover" style="display:inline-block" data-source="#pet-container-<?php echo $npc->id ?>-1" data-title="Mascotes com os Requerimentos" data-trigger="click" data-placement="bottom">
					<?php
					$petID		= 'pet_id_' . $counter;
					$has_pet	= FALSE;
					$image_item	= "";
					$effect_ids	= "";

					if ($quest->$petID) {
						$has_pet		= TRUE;
						$image_item		= Item::find_first($quest->$petID);
						$player_item	= PlayerItem::find_first($quest->$petID);
						$image			= image_url('items/' . $image_item->description()->image);
					} else
						$image			= image_url('pet_unknown.png' );
					?>
					<?php if($can_finish || $quest->finish_at){?>
						<img src="<?=$image;?>" />
					<?php } else {?>
						<img src="<?=$image;?>" style="cursor: pointer" class="current-quest-change-pet <?=($has_pet ? 'remove-pet' : 'add-pet');?>" data-url="<?=make_url('quests#list_pets');?>" data-quest_id="<?=$quest->pet_quest_id;?>" data-counter="<?=$counter;?>" data-message="<?=t('quests.pet.message');?>" />
					<?php } ?>
				</div>
				<div id="pet-container-<?php echo $npc->id ?>-1" class="technique-container">
					<div class="status-popover-content" style="width: 230px;">
						<ul>
							<?php if($npc->rarity){?>
								<li>
									<b class="verde"><?php echo t('quests.pet.raridade')?></b><br />
									<?php switch($npc->rarity){
										case "common":
											if($image_item){
												$rarity = "<span style='text-decoration: line-through;' class='verde'>".t('quests.pet.comum')."</span>";
											}else{
												$rarity = t('quests.pet.comum');
											}
											break;
										case "rare":
											if($image_item){
												if($image_item->rarity=="common"){
													$rarity = "<span class='laranja'>".t('quests.pet.raro')."</span>";
												}else{
													$rarity = "<span style='text-decoration: line-through;' class='verde'>".t('quests.pet.raro')."</span>";
												}
											}else{
												$rarity = t('quests.pet.raro');
											}
											break;
										case "legendary":
											if($image_item){
												if($image_item->rarity=="common"){
													$rarity = "<span class='vermelho'>".t('quests.pet.lendario')."</span>";
												}elseif($image_item->rarity=="rare"){
													$rarity = "<span class='laranja'>".t('quests.pet.lendario')."</span>";
												}else{
													$rarity = "<span style='text-decoration: line-through;' class='verde'>".t('quests.pet.lendario')."</span>";
												}
											}else{
												$rarity = t('quests.pet.lendario');
											}
											break;
										case "mega":
											if($image_item){
												if($image_item->rarity=="common"){
													$rarity = "<span class='vermelho'>Mega</span>";
												}elseif($image_item->rarity=="rare"){
													$rarity = "<span class='vermelho'>Mega</span>";
												}elseif($image_item->rarity=="legendary"){
													$rarity = "<span class='laranja'>Mega</span>";
												}else{
													$rarity = "<span style='text-decoration: line-through;' class='verde'>Mega</span>";
												}
											}else{
												$rarity = t('quests.pet.lendario');
											}
											break;
									}
									?>
									<?php echo $rarity ?><br /><br />
								</li>
							<?php }?>
							<?php if($npc->effect_ids){?>
								<?php $items = Item::find('item_effect_ids in ('.$npc->effect_ids.') AND rarity !="Mega" GROUP BY item_effect_ids',['cache' => true]);?>

								<li>
									<b class="verde"><?php echo t('quests.pet.efeito')?></b><br />
									<?php if($image_item){?>
										<?php
										$effect_ids  	= explode(',', $image_item->item_effect_ids);
										$effect_ids2  	= explode(',', $npc->effect_ids);
										$effect 		= false;

										foreach($effect_ids2 as $effect_id2){
											if(in_array($effect_id2,$effect_ids)){
												$effect = true;
											}
										}
										?>
										<?php if($effect){?>
											<?php foreach($items as $item){?>
												<span style='text-decoration: line-through;' class='verde'><?php echo $item->tooltip() ?></span>
											<?php }?>
										<?php }else{ ?>
											<?php foreach($items as $item){?>
												<span class='vermelho'><?php echo $item->tooltip() ?></span>
											<?php }?>
										<?php }?>

									<?php }else{?>
										<?php foreach($items as $item){?>
											<?php echo $item->tooltip() ?>
										<?php }?>
									<?php }?>
								</li>
								<br />
							<?php }?>
							<?php if($npc->anime_id){?>
								<li>
									<b class="verde"><?php echo t('quests.pet.anime')?></b><br />
									<?php if($image_item){?>
										<?php if($npc->anime_id == $image_item->description()->anime_id){?>
											<span style='text-decoration: line-through;' class='verde'><?php echo $npc->anime($npc->anime_id)->description()->name ?></span><br /><br />
										<?php }else{ ?>
											<span class="vermelho"><?php echo $npc->anime($npc->anime_id)->description()->name ?></span><br /><br />
										<?php }?>
									<?php }else{?>
										<?php echo $npc->anime($npc->anime_id)->description()->name ?><br /><br />
									<?php }?>
								</li>
							<?php }?>
							<?php if($npc->happiness){?>
								<li>
									<b class="verde"><?php echo t('quests.pet.happiness')?></b><br />
									<?php if($image_item){?>
										<?php if($player_item->happiness >= $npc->happiness){?>
											<span style='text-decoration: line-through;' class='verde'><?php echo $npc->happiness ?></span>
										<?php }else{?>
											<span class='laranja'><?php echo $npc->happiness ?></span>
										<?php }?>
									<?php }else{?>
										<?php echo $npc->happiness ?>
									<?php }?>

								</li>
							<?php }?>
						</ul>
					</div>
				</div>
			<?php $counter++; }?>
		</div>
		<div style="position: relative; top: 10px">
			<div style="float: left; width: 107px;">
				<b class="laranja" style="font-size:14px"><?php echo t('quests.time.header.duration') ?></b><br />
				<?php
				if ($can_finish) {
					if ($success) { ?>
					<span class="verde"><?=t('quests.pet.success');?></span>
				<?php } else { ?>
					<span class="vermelho"><?=t('quests.pet.falhou');?></span>
				<?php
					}
				} else {
					if (!$quest->finish_at && !$quest->completed) {
						foreach ($quest->quest()->durations() as $duration)
							echo $duration->time['string'];
					} else {
						$diff = $quest->pet_wait_diff($quest->pet_quest_id);
				?>
					<span class="quest-timer-container-<?=$quest->pet_quest_id;?>">--:--:--</span>
					<script type="text/javascript">
						$(document).ready(function () {
							create_timer(<?=$diff['hours'];?>, <?=$diff['minutes']; ?>, <?=$diff['seconds'];?>, 'quest-timer-container-<?=$quest->pet_quest_id;?>', null, null, true);
						});
					</script>
				<?php
					}
				}
				?>
			</div>
			<div style="float: left; width: 105px;">
				<b class="laranja" style="font-size:14px">Sucesso</b><br />
				<?php echo $player->quest_pet_calc_success($quest->pet_quest_id);?>%
			</div>
		</div>
		<div style="position: relative; top: 20px">
			<div style="float: left; width: 210px;">
				<b class="laranja" style="font-size:14px"><?php echo t('quests.time.header.reward') ?></b><br />
				<?php if ($quest->quest()->exp) { ?>
					<span class="exp"><?=highamount($quest->quest()->exp);?> Exp<br />
				<?php }?>
				<?php if ($quest->quest()->currency) { ?>
					<span class="currency"><?=highamount($quest->quest()->currency);?> <?=t('currencies.' . $player->character()->anime_id);?></span><br />
				<?php } ?>
				<?php if ($quest->quest()->pet_exp) { ?>
					<span class="exp"><?php echo highamount($quest->quest()->pet_exp) ?> <?php echo t('quests.pet.exp_mascote')?><br />
				<?php }?>
				<?php if($quest->quest()->pet_happiness){?>
					<span class="exp"><?php echo $quest->quest()->pet_happiness ?> <?php echo t('quests.pet.de_felicidade')?><br />
				<?php }?>
				<?php if($quest->quest()->credits){?>
					<li><?php echo $quest->quest()->credits ?> <?php echo t('treasure.show.credits')?></li>
				<?php }?>
				<?php if($quest->quest()->equipment && $quest->quest()->equipment == 1){?>
					<li><?php echo t('treasure.show.equipment1')?></li>
				<?php }?>
				<?php if($quest->quest()->equipment && $quest->quest()->equipment == 2){?>
					<li><?php echo t('treasure.show.equipment2')?></li>
				<?php }?>
				<?php if($quest->quest()->equipment && $quest->quest()->equipment == 3){?>
					<li><?php echo t('treasure.show.equipment3')?></li>
				<?php }?>
				<?php if($quest->quest()->equipment && $quest->quest()->equipment == 4){?>
					<li><?php echo t('treasure.show.equipment4')?></li>
				<?php }?>
				<?php if($quest->quest()->pets  && $quest->quest()->item_id){?>
					<li><?php echo t('treasure.show.pet')?> "<?php echo Item::find($quest->quest()->item_id)->description()->name ?>"</li>
				<?php }?>
				<?php if($quest->quest()->character_theme_id){?>
					<li><?php echo t('treasure.show.theme')?> "<?php echo CharacterTheme::find($quest->quest()->character_theme_id)->description()->name ?>"</li>
				<?php }?>
				<?php if($quest->quest()->character_id){?>
					<li><?php echo t('treasure.show.character')?> "<?php echo Character::find($quest->quest()->character_id)->description()->name ?>"</li>
				<?php }?>
				<?php if($quest->quest()->headline_id){?>
					<li><?php echo t('treasure.show.headline')?> "<?php echo Headline::find($quest->quest()->headline_id)->description()->name ?>"</li>
				<?php }?>
			</div>
			<div style="position: relative; top: 20px;">
				<?php
				$completed = false;
				if (sizeof($quest->npc()) == 1) {
					if ($quest->pet_id_1) {
						$completed = true;
					}
				} elseif (sizeof($quest->npc()) == 2) {
					if ($quest->pet_id_1 && $quest->pet_id_2) {
						$completed = true;
					}
				} elseif (sizeof($quest->npc()) == 3) {
					if ($quest->pet_id_1 && $quest->pet_id_2 && $quest->pet_id_3) {
						$completed = true;
					}
				}
				?>
				<?php if (!$quest->completed && $completed && !$quest->finish_at && !$quest->completed): ?>
					<a href="javascript:;" class="accept btn btn-sm btn-primary" data-id="<?php echo $quest->pet_quest_id ?>"><?php echo t('quests.accept') ?></a>
				<?php else: ?>
					<?php if($can_finish){?>
						<a href="javascript:;" class="btn btn-sm btn-success pet-quest-finish" data-id="<?php echo $quest->pet_quest_id ?>"><?php echo t('quests.finish') ?></a>
					<?php }else{?>
						<a href="javascript:;" class="disabled btn btn-sm btn-danger" data-id="<?php echo $quest->pet_quest_id ?>"><?php echo t('quests.accept') ?></a>
					<?php }?>

				<?php endif ?>
			</div>
		</div>
		<div class="break"></div>
	</div>
	<?php } ?>
</div>