<style>
	.technique-data {
		width: 250px !important;	
	}
</style>
<?php echo partial('shared/title', array('title' => 'menus.enchant', 'place' => 'menus.enchant')) ?>
<?php if (!$player_tutorial->aprimoramentos) { ?>
	<script type="text/javascript">
		$(function () {
			var tour = new Tour({
				backdrop: true,
				page: 9,
				steps: [{
					element: ".tutorial-aprimoramento",
					title: "Treino Diário",
					content: "Venha treinar diariamente para acumular pontos e criar Joias, usadas para aprimorar a força de seus golpes.",
					placement: "top"
				}, {
					element: ".enchant-golpe",
					title: "Escolha seu Golpe",
					content: "Escolha um golpe que você já tenha Equipado alguma vez e você verá três opções de Encantamento disponíveis.",
					placement: "left"
				}, {
					element: ".enchant-1",
					title: "Encantamento 1",
					content: "Vamos mostrar as Joias necessárias para você fazer o Encantamento número 1 de cada Golpe. Basta clicar na Joia acima e arrastar para o lugar certo.",
					placement: "left",
				}, {
					element: ".enchant-3",
					title: "Encantamento 2 e 3",
					content: "Entretanto, ainda existem mais dois tipos de encantamentos para cada golpe, só que dessa vez você que terá que descobrir a combinação certa! As duas primeiras Joias não mudam, mas a terceira e quarta mudam sempre!",
					placement: "left",
				}]
			});

			tour.restart();
			tour.init(true);
			tour.start(true);
		});
	</script>
<?php } ?>
<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>"><p><?php echo t('enchant.treino_diario') ?></p></div>
<div class="tutorial-aprimoramento">
	<div style="float:left; width:370px">
		<p align="center"><?php echo t('enchant.limite')?></p>
		<?php echo exp_bar($player->enchant_points . '/3000', 3000, 350);?>
	</div>
	<form id="treino-stamina-filter-form" method="post">
		<div style="float:left; width:260px">
			<div align="center">
				<p align="center"><?php echo t('enchant.custo')?></p>
				<img src="<?php echo image_url("icons/for_stamina.png")?>" />
				<select name="sltTreinoStamina" id="sltTreinoStamina" class="form-control input-sm" style="width: auto; display: inline-block;">
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5">5</option>
					<option value="10">10</option>
					<option value="20">20</option>
					<option value="40">40</option>
					<option value="60">60</option>
					<option value="80">80</option>
				</select>
			</div>
		</div>
		<div style="float:left">
			<a class="btn btn-sm btn-primary filter" style="margin-top: 14px"><?php echo t('enchant.treinar')?></a>
		</div>
	</form>
</div>
<div class="msg-container" style="clear:both">
	<div class="msg_top"></div>	
	 <div class="msg_repete">
		<div class="msg" style="background:url(<?php echo image_url('msg/encantamento.png')?>); background-repeat: no-repeat;">
		</div>
		<div class="msgb" style="position:relative; margin-left: 231px; text-align: left; top: -37px">
			<b><?php echo t('enchant.pontos')?></b>
			<div class="content">
				<p><?php echo t('enchant.frase')?></p><br />
				<?php echo exp_bar($player->enchant_points_total . '/50000', 50000, 350);?><br />
				<?php $total_gem = floor($player->enchant_points_total / 2000);?>
				<p class="laranja">
					<?php echo $total_gem >=1 ? t('enchant.frase1')." ". $total_gem . " " . t('enchant.frase2'). " <a class='btn btn-sm btn-success create_gem'>".t('enchant.criar')."</a>" : t('enchant.frase3')?><br />
				</p>
				<?php if(isset($_GET['joia']) && $_GET['joia']){?>
					<div style="position: absolute; float: right; right: 61px; top: 72px;">
						<span class="verde"><?php echo t('enchant.criou')?>:</span> <br />
						<img src="<?php echo image_url("items/".$_GET['joia'].".png")?>" />
					</div>
				<?php }?>	
			</div>
		</div>		
	</div>
	<div class="msg_bot"></div>	
	<div class="msg_bot2"></div>	
</div>
<br />
<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>"><p><?php echo t('enchant.enchant') ?></p></div>
<div id="technique-list" class="technique-list-box" style="height: 75px; text-align: center;">
	<?php foreach ($gems as $item): ?>
		<?php $quantity = PlayerItem::find_first("item_id=".$item->id." AND player_id=".$player->id)?>
		<div id="technique-dropsource-<?php echo $item->id ?>" style="display: inline-block;  position: relative; margin: 10px; z-index:10000">
			<div class="<?php echo ($quantity && ($quantity->quantity)) && (isset($item_equipped) && $item_equipped) ? "item-content-gem cursor_pointer" : "cursor_error"?>" data-item="<?php echo $item->id ?>" >
				<img src="<?php echo image_url($item->image(true)) ?>" class="technique-popover item-image" data-source="#technique-content-<?php echo $item->id ?>" data-title="<?php echo $item->description()->name ?>" data-trigger="hover" data-placement="bottom" />
				<div class="technique-container" id="technique-content-<?php echo $item->id ?>">
					<?php echo $item->tooltip() ?>
				</div>
				<span class="quantity">x<?php echo $quantity ? $quantity->quantity : 0?></span>
			</div>
		</div>	
	<?php endforeach ?>
</div><br />
<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
	<p><?php echo t('enchant.enchant') ?></p>
</div>
<div id="technique-list" class="technique-list-box" style="text-align: center;">
	<?php
	foreach ($items as $item) {
		$item->set_player($player);
		$item->formula(true);
		
		echo partial('item_enchant', [
			'item'		=> $item,
			'player'	=> $player,
			'type'		=> 'source', 
			'equipped'	=> $item_equipped
		]);
	}
	?>
	<div class="clearfix"></div>
</div><br />
<?php
	if (isset($item_equipped) && $item_equipped) {
		$image						= image_url($item_equipped->image(true));
		$name 						= $item_equipped->description()->name;
		$tooltip					= $item_equipped->tooltip();
		$id 						= $item_equipped->id;
		$item_combinations 			= explode(",", $item_combinations->combination);
		$item_combinations_item_1 	= explode("-", $item_combinations[0]);
		$item_combinations_item_2 	= explode("-", $item_combinations[1]);
		$item_combinations_item_3	= explode("-", $item_combinations[2]);
	} else {
		$image  	= image_url("enchant/sem-golpe.png");
		$name		= t('enchant.escolha');
		$tooltip	= t('enchant.tooltip');
		$id			= 1010;
	}
?>
<div class="enchant">
	<?php 
	if (isset($item_equipped) && $item_equipped) {
		$counter = 1;
		foreach ($item_enchanteds as $item_enchanted) {
			$verification = TRUE;
			$item_enchanted->set_anime($player->character()->anime_id);
			if (!$item_enchanted->is_generic) {
				$item_enchanted->set_character_theme($player->character_theme_id);
			}

			switch ($counter) {
				case 1:
					$verifica_gem = $player_item_gem->gem_1;
					if ($verifica_gem == 0) {
						$verification = FALSE;
					}

					$combination = $item_combinations_item_1;
					if (isset($player_item_gem) && $player_item_gem) {
						if ($player_item_gem->gem_4 || $player_item_gem->gem_3) {
							$verification = FALSE;
						}
					}
					$enchanted = $player_item_gem->enchanted;
					break;

				case 2:
					$combination = $item_combinations_item_2;
					if (isset($player_item_gem) && $player_item_gem) {
						if ($player_item_gem->gem_4) {
							$verification = FALSE;
						}
					}
					$enchanted = $player_item_gem->enchanted;
					break;

				case 3:
					$combination = $item_combinations_item_3;
					$enchanted = $player_item_gem->enchanted;
					break;
			}
	?>
	<div class="ability-speciality-box" style="width: 231px !important; height: 270px !important; padding-bottom: 40px">
		<div class="image">
			<img data-source="#technique-content-<?=$item_enchanted->id;?>" data-title="<?=$item_enchanted->description()->name;?>" data-trigger="hover" data-placement="bottom" class="technique-popover item-image" data-url="<?=make_url('techniques#list_golpes');?>" data-item="<?=$item_enchanted->id;?>" src="<?=image_url($item_enchanted->image(true));?>" />
			<div class="technique-container" id="technique-content-<?php echo $item_enchanted->id ?>">
				<?=$item_enchanted->tooltip();?>
			</div>
		</div>
		<div class="name" style="height: 40px !important;">
			<?=$item_enchanted->description()->name;?>
		</div>
		<div class="description" style="height: auto; font-size:11px">
			<?php $item_combinations = 'item_combinations_item_' . $counter; ?>
			<?php foreach ($$item_combinations as $item_combination) { ?>
				<img class="" data-item="<?=$item_enchanted->id;?>" data-message="Você realmente gostaria de remover esse Treinamento? Seu golpe deixará de ser aprimorado!" src="<?=image_url('items/' . $item_combination . '.png')?>" width="48" />
			<?php } ?>
		</div>
		<div class="button" style="position:relative; top: 15px;">
			<?php $has_gems	= $player->has_gems($item_equipped->id, $counter); ?>
			<?php if ($has_gems && !$enchanted) { ?>
				<a class="btn btn-sm btn-primary enchant_item_gem" data-counter="<?=$counter;?>" data-enchanted="<?=$item_equipped->id;?>" data-item="<?=$item_enchanted->id;?>">
					<?=t('enchant.encantar');?>
				</a>
			<?php } elseif ($enchanted && !sizeof($result) && $verification) { ?>
				<a class="btn btn-sm btn-success">
					<?=t('enchant.encantado');?>
				</a>
			<?php } else { ?>
				<a class="btn btn-danger disabled">
					<?=t('enchant.encantar');?></a>
			<?php } ?>
		</div>
	</div>
	<?php 
			$counter++;
		}
	}
	?>
	<?php /*foreach ($item_combinations as $combination) { ?>
	<div class="ability-speciality-box" style="width: 231px !important; height: 270px !important; padding-bottom: 40px">
		<div class="image">
			<img data-source="#technique-content-<?php echo $item_enchanted->id ?>" data-title="<?php echo $item_enchanted->description()->name; ?>" data-trigger="hover" data-placement="bottom" class="technique-popover item-image" data-url="<?php echo make_url('techniques#list_golpes') ?>" data-item="<?php echo $item_enchanted->id?>" src="<?php echo image_url($item_enchanted->image(true)) ?>" />
			<div class="technique-container" id="technique-content-<?php echo $item_enchanted->id ?>">
				<?php echo $item_enchanted->tooltip() ?>
			</div>
		</div>
		<div class="name" style="height: 40px !important;">
			Dynamic Kick
		</div>
		<div class="description" style="height: auto; font-size:11px">
			<img oncontextmenu="return false;" class="" data-item="1" data-message="Você realmente gostaria de remover esse Treinamento? Seu golpe deixará de ser aprimorado!" src="http://borutogame.com.br/assets/images/items/1870.png" width="48">
			<img oncontextmenu="return false;" class="" data-item="1" data-message="Você realmente gostaria de remover esse Treinamento? Seu golpe deixará de ser aprimorado!" src="http://borutogame.com.br/assets/images/items/1871.png" width="48">
		</div>
		<div class="button" style="position:relative; top: 15px;">
			<a class="btn btn-danger">Aprimorar</a>
		</div>
	</div>
	<?php }*/ ?>
	<div class="clearfix"></div>
</div>
<div class="enchant" style="display: none;">
	<div class="enchant-golpe">
		<img style="cursor:pointer" data-source="#technique-content-<?php echo $id ?>" data-title="<?php echo $name ?>" data-trigger="hover" data-placement="bottom" class="change_golpe_enchant technique-popover item-image" data-url="<?php echo make_url('techniques#list_golpes') ?>" data-item="<?php echo $id?>" src="<?php echo $image ?>" />
		<div class="technique-container" id="technique-content-<?php echo $id ?>">
			<div style="width: 250px">
				<?php echo $tooltip ?>
			</div>
		</div>
	</div>
	<div class="enchant-item enchant-1 dropzone" data-slot="1">
		<?php if(isset($player_item_gem) && $player_item_gem){?>
			<img class="<?php echo $player_item_gem->gem_1 ? "remove-gem cursor_pointer" : ""?>" data-counter="1" data-item="<?php echo $item_equipped->id ?>" data-message="<?php echo t('enchant.message');?>" src="<?php echo image_url($player_item_gem->gem_1 ? "items/".$player_item_gem->gem_1.".png" : "enchant/".$item_combinations_item_1[0].".png")?>" />
		<?php }else{?>	
			<img src="<?php echo image_url("enchant/sem-gem.png")?>" />
		<?php }?>	
	</div>
	<div class="enchant-item enchant-2 dropzone" data-slot="2">
		<?php if(isset($player_item_gem) && $player_item_gem){?>
			<img class="<?php echo $player_item_gem->gem_2 ? "remove-gem cursor_pointer" : ""?>" data-counter="2" data-item="<?php echo $item_equipped->id ?>" data-message="<?php echo t('enchant.message');?>" src="<?php echo image_url($player_item_gem->gem_2 ? "items/".$player_item_gem->gem_2.".png" : "enchant/".$item_combinations_item_1[1].".png")?>" />
		<?php }else{?>	
			<img src="<?php echo image_url("enchant/sem-gem.png")?>" />
		<?php }?>
	</div>
	<div class="enchant-item enchant-3 dropzone" data-slot="3">
		<?php if(isset($player_item_gem) && $player_item_gem){?>
			<img class="<?php echo $player_item_gem->gem_3 ? "remove-gem cursor_pointer" : ""?>" data-counter="3" data-item="<?php echo $item_equipped->id ?>" data-message="<?php echo t('enchant.message');?>" src="<?php echo image_url($player_item_gem->gem_3 ? "items/".$player_item_gem->gem_3.".png" : "enchant/sem-gem.png")?>" />
		<?php }else{?>	
			<img src="<?php echo image_url("enchant/sem-gem.png")?>" />
		<?php }?>
	</div>
	<div class="enchant-item enchant-4 dropzone" data-slot="4">
		<?php if(isset($player_item_gem) && $player_item_gem){?>
			<img class="<?php echo $player_item_gem->gem_4 ? "remove-gem cursor_pointer" : ""?>" data-counter="4" data-item="<?php echo $item_equipped->id ?>" data-message="<?php echo t('enchant.message');?>" src="<?php echo image_url($player_item_gem->gem_4 ? "items/".$player_item_gem->gem_4.".png" : "enchant/sem-gem.png")?>" />
		<?php }else{?>	
			<img src="<?php echo image_url("enchant/sem-gem.png")?>" />
		<?php }?>
	</div>
	<?php 
		if(isset($item_equipped) && $item_equipped){	
			$counter = 1;
			foreach($item_enchanteds as $item_enchanted){
				$verification = true;
				$item_enchanted->set_anime($player->character()->anime_id);
				if(!$item_enchanted->is_generic){
					$item_enchanted->set_character_theme($player->character_theme_id);
				}
				switch($counter){
					case 1:
						$verifica_gem = $player_item_gem->gem_1;
						if($verifica_gem == 0){
							$verification = false;
						}
						$combination = $item_combinations_item_1;
						if(isset($player_item_gem) && $player_item_gem){
							if($player_item_gem->gem_4 || $player_item_gem->gem_3){
								$verification = false;	
							}
						}
						$enchanted = $player_item_gem->enchanted;
					break;
					
					case 2:
						$combination = $item_combinations_item_2;
						if(isset($player_item_gem) && $player_item_gem){
							if($player_item_gem->gem_4){
								$verification = false;	
							}
						}
						$enchanted = $player_item_gem->enchanted;
					break;
					
					case 3:
						$combination = $item_combinations_item_3;
						$enchanted = $player_item_gem->enchanted;
					break;
				}
			?>
			<div class="item-enchanted-<?php echo $counter?>">
				<img data-source="#technique-content-<?php echo $item_enchanted->id ?>" data-title="<?php echo $item_enchanted->description()->name; ?>" data-trigger="hover" data-placement="bottom" class="technique-popover item-image" data-url="<?php echo make_url('techniques#list_golpes') ?>" data-item="<?php echo $item_enchanted->id?>" src="<?php echo image_url($item_enchanted->image(true)) ?>" />
				<div class="technique-container" id="technique-content-<?php echo $item_enchanted->id ?>">
					<?php echo $item_enchanted->tooltip() ?>
				</div>
				<?php $result = $player->valid_gem_combination($item_equipped->id, $combination, $counter);?>
					<?php if(!sizeof($result) && $verification && !$enchanted){?>
						<a class="btn btn-sm btn-primary enchant_item_gem button_enchant<?php echo $counter?>" data-counter="<?php echo $counter?>" data-enchanted="<?php echo $item_equipped->id?>" data-item="<?php echo $item_enchanted->id?>"><?php echo t('enchant.encantar')?></a>
					<?php }elseif($enchanted && !sizeof($result) && $verification){ ?>
						<a class="btn btn-sm btn-success button_enchant<?php echo $counter?>"><?php echo t('enchant.encantado')?></a>
					<?php }?>
			</div>
		<?php 
				$counter++;
			}
		}
		?>	
</div>
