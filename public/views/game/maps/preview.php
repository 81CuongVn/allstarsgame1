<div class="titulo-secao">
	<p><?php echo $map->name?></p>
	<span><a href="<?=make_url('/');?>">Página Principal</a> <b> &gt;&gt; </b> <?php echo $map->name?></span>
</div>
<?php if (FW_ENV != 'dev') { ?>
	<!-- AASG - Exploração -->
	<ins class="adsbygoogle"
		style="display:inline-block;width:728px;height:90px"
		data-ad-client="ca-pub-6665062829379662"
		data-ad-slot="3371212995"></ins>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
	</script><br />
<?php } ?>
<!-- Aqui é o menu da esquerda para não ter que fazer verificações fora da controller do mapa -->
<div style="position:absolute; left:-283px; top:11px">
	<div style="width:242px; height:310px; float: left; text-align: center">
		<?php if(!isset($npc)){?>
			<img src="<?php echo image_url('profile/unknown.jpg') ?>"/>
			<a style="position:relative; top: -35px" class="btn btn-sm btn-danger"><?php echo t('map.nenhum_npc');?></a>
		<?php }else{?>
			<?php echo $npc->profile_image() ?>
			<div align="center" class="nome-personagem"><?php echo $npc->name ?></div>
			<a style="position:relative; top: -22px" class="btn btn-sm btn-success" id="btn-enter-npc-battle" data-type="6"><?php echo t('map.lutar');?></a>
		<?php }?>
	</div>
	<div class="bg_menu_esquerdo">
		<div class="menu_esquerdo_divisao2" style="width: 105px !important">
			<img src="<?php echo image_url('maps/item.png') ?>"/>
		</div>
		<div class="menu_esquerdo_divisao2">
			<b class="amarelo"><?php echo t('map.item');?></b><br />
			<b class="azul_claro">
				<?php if(isset($map_reward)){ ?>
					<?php
						switch($map->anime_id){
							case 1:
								echo "1 Pergaminho<br />";
							break;
							case 2:
								echo "1 Alma<br />";
							break;
							case 9:
								echo "1 Material<br />";
							break;
						}
					?>
				<?php }?>
				<?php if(isset($rewards)){?>
					<?php if($rewards->exp){?>
						<?php echo highamount($rewards->exp) ?> Exp<br />
					<?php }?>
					<?php if($rewards->currency){?>
						<?php echo highamount($rewards->currency) ?> <?php echo t('currencies.' . $player->character()->anime_id) ?><br />
					<?php }?>
					<?php if($rewards->credits){?>
						<?php echo highamount($rewards->credits) ?> <?php echo t('treasure.show.credits')?><br />
					<?php }?>
					<?php if($rewards->equipment && $rewards->equipment == 1){?>
						<?php echo t('treasure.show.equipment1')?><br />
					<?php }?>
					<?php if($rewards->equipment && $rewards->equipment == 2){?>
						<?php echo t('treasure.show.equipment2')?><br />
					<?php }?>
					<?php if($rewards->equipment && $rewards->equipment == 3){?>
						<?php echo t('treasure.show.equipment3')?><br />
					<?php }?>
					<?php if($rewards->equipment && $rewards->equipment == 4){?>
						<?php echo t('treasure.show.equipment4')?><br />
					<?php }?>
					<?php if($rewards->equipment && $rewards->equipment == 5){?>
						<?php echo t('treasure.show.equipment5')?><br />
					<?php }?>
					<?php if($rewards->pets && $rewards->item_id){?>
						<?php echo t('treasure.show.pet')?> "<?php echo Item::find($rewards->item_id)->description()->name ?>"<br />
					<?php }?>
					<?php if($rewards->character_theme_id){?>
						<?php echo t('treasure.show.theme')?> "<?php echo CharacterTheme::find($rewards->character_theme_id)->description()->name ?>"<br />
					<?php }?>
					<?php if($rewards->character_id){?>
						<?php echo t('treasure.show.character')?> "<?php echo Character::find($rewards->character_id)->description()->name ?>"<br />
					<?php }?>
					<?php if($rewards->headline_id){?>
						<?php echo t('treasure.show.headline')?> "<?php echo Headline::find($rewards->headline_id)->description()->name ?>"<br />
					<?php }?>
				<?php }?>
				<?php if(!isset($rewards) && !isset($map_reward)){?>
					<?php echo t('map.nenhum');?>
				<?php }?>
			</b>
		</div>
	</div>
	<div class="bg_menu_esquerdo">
		<div class="menu_esquerdo_divisao2" style="width: 105px !important">
			<img src="<?php echo image_url('maps/step.png') ?>"/>
		</div>
		<div class="menu_esquerdo_divisao2">
			<b class="amarelo"><?php echo t('map.passos');?></b><br />
			<b class="azul_claro"><?php echo $player->steps?></b>
		</div>
	</div>
	<div id="navegation">
		<?php if($map->north){?>
			<a class="direction" data-map="<?php echo $map->north?>"  data-direction="1" style="cursor: pointer"><div id="north" class="directions"></div></a>
		<?php }else{?>
			<div id="north2" class="directions"></div>
		<?php }?>

		<?php if($map->east){?>
			<a class="direction" data-map="<?php echo $map->east?>" data-direction="2" style="cursor: pointer"><div id="east" class="directions"></div></a>
		<?php }else{?>
			<div id="east2" class="directions"></div>
		<?php }?>

		<?php if($map->south){?>
			<a class="direction" data-map="<?php echo $map->south?>" data-direction="3" style="cursor: pointer"><div id="south" class="directions"></div></a>
		<?php }else{?>
			<div id="south2" class="directions"></div>
		<?php }?>

		<?php if($map->west){?>
			<a class="direction" data-map="<?php echo $map->west?>" data-direction="4" style="cursor: pointer"><div id="west" class="directions"></div></a>
		<?php }else{?>
			<div id="west2" class="directions"></div>
		<?php }?>
	</div>
	<br />
	<div id="map-leave" style="text-align: center">
		<a class="btn btn-danger leave" data-id="<?php echo $map->id?>" data-message="<?php echo t('map.sair_msg');?>"><?php echo t('map.sair_mapa');?></a>
	</div>
</div>
<div class="bg_menu_direita">
	<div class="bg_menu_direita2">
		<div class="menu_esquerdo_divisao2" style="width: 105px !important">
			<img src="<?php echo image_url('maps/map.png') ?>"/>
		</div>
		<div class="menu_esquerdo_divisao2">
			<b class="amarelo"><?php echo t('map.maps');?></b><br />
			<b class="azul_claro"><?php echo sizeof($map_player_total)?> / <?php echo sizeof($map_total)?></b>
		</div>
	</div>
	<div class="bg_menu_direita2">
		<div class="menu_esquerdo_divisao2" style="width: 105px !important">
			<img src="<?php echo image_url('maps/item.png') ?>"/>
		</div>
		<div class="menu_esquerdo_divisao2">
			<b class="amarelo"><?php echo t('map.find');?></b><br />
			<b class="azul_claro"><?php echo $player_stats->total_rewards?></b>
		</div>
	</div>
	<div class="bg_menu_direita2">
		<div class="menu_esquerdo_divisao2" style="width: 105px !important">
			<img src="<?php echo image_url('maps/'.$map->anime_id.'.png') ?>"/>
		</div>
		<div class="menu_esquerdo_divisao2">
			<b class="amarelo"><?php echo t('map.total'.$map->anime_id);?></b><br />
			<b class="azul_claro"><?php echo $player_item ? $player_item->quantity : 0?></b>
		</div>
	</div>
</div>
<div align="center">
	<img src="<?php echo image_url('maps/'.$map->anime_id.'/'.$map->id.'.jpg') ?>"/>
</div>
<br /><br />
<?php if($map->store){?>
<?php
	$stores = MapStore::find("map_id=".$map->id, [
		'reorder'	=> 'ordem asc'
	]);
	$total_item_map = $player_item ? $player_item->quantity : 0;
?>
<?php foreach ($stores as $store): ?>
	<?php
		$has_technique = false;

		if($store->is_technique){
			$has_technique = $player->has_item($store->item_id);
		}
	?>
	<div class="ability-speciality-box" data-id="<?php echo $store->id?>" style="width: 237px !important; height: 230px !important">
	<div>
		<div class="image">
			<img src="<?php echo image_url('maps/store/'.$store->id.'.png') ?>" />
		</div>
		<div class="name legendary" style="height: 30px !important;">
			<?php echo $store->name ?><br />
			<span style="font-size:12px;" class="amarelo"></span>
		</div>
		<div class="details">
			<img src="<?php echo image_url("maps/icons/".$map->anime_id.".png" ) ?>" width="26" height="26" />
			<span class="amarelo_claro" style="font-size: 16px; margin-left: 5px; top: 2px; position: relative"><?php echo $total_item_map ?> / <?php echo $store->map_item_total?></span>
		</div>
		<div class="button" style="position:relative; top: 15px;">
			<?php if($total_item_map >= $store->map_item_total && !$has_technique){ ?>
				<a class="store_change btn btn-primary" data-mode="<?php echo $store->id?>"><?php echo t('treasure.show.change') ?></a>
			<?php }else{?>
				<a class="btn btn-danger"><?php echo $has_technique ? "Aprendido" : t('treasure.show.change') ?></a>
			<?php }?>
		</div>
	</div>
</div>
<?php endforeach;?>
<?php }?>
