<?php echo partial('shared/title', array('title' => 'menus.pets', 'place' => 'menus.pets')) ?>
<?php if (!$player_tutorial->pets) { ?>
	<script type="text/javascript">
		$(function () {
			var tour = new Tour({
				backdrop: true,
				page: 3,
				steps: [{
					element: "#pet-list",
					title: "Colecione Mascotes!",
					content: "Mascotes te dão bônus em diversas coisas durante a batalha, e também servem para fazer Missões de Mascotes. Existem 4 Raridades, e você pode evoluir qualquer Mascote até a raridade Mega. Você consegue novos Seguidores em Batalhas, Modo Aventura, Arena do Céu e muito mais!",
					placement: "top"
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
		'title'		=> 'quests.pets.title',
		'message'	=> t('quests.pets.description')
	]);
?>
<br />
<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
	<p>Filtro do Ranking</p>
</div>
<form id="pets-filter-form" method="post">
<input type="hidden" name="page" value="<?php echo $page ?>" />
<table width="725" border="0" cellpadding="0" cellspacing="0" class="filtros">
	<tr>
		<td width="0%" align="center">
		<b><?php echo t('rankings.players.header.nome') ?></b><br />
		<input type="text" class="form-control input-sm" style="max-width: 150px" name="name" value="<?php echo $name ?>" />
		</td>
		<td width="0%" align="center">
		<b><?php echo t('rankings.players.header.description') ?></b><br />	
		<input type="text" class="form-control input-sm" style="max-width: 150px" name="description" value="<?php echo $description ?>" />
		</td>
		<td width="0%" align="center">
		<b><?php echo t('rankings.players.header.raridade') ?></b><br />
		<select name="raridade" class="form-control input-sm" style="max-width: 100px">
			<option value="todos"><?php echo t('global.all') ?></option>
			<option value="common" <?php if ('common' == $raridade): ?>selected="selected"<?php endif ?>>Comum</option>
			<option value="rare" <?php if ('rare' == $raridade): ?>selected="selected"<?php endif ?>>Raro</option>
			<option value="legendary" <?php if ('legendary' == $raridade): ?>selected="selected"<?php endif ?>>Lendário</option>
			<option value="mega" <?php if ('mega' == $raridade): ?>selected="selected"<?php endif ?>>Mega</option>
		</select>
			
		</td>
		<td width="0%" align="center">
		<b><?php echo t('rankings.players.header.active') ?></b><br />
		<select name="active" class="form-control" style="max-width: 100px">
			<option value="0"><?php echo t('global.all') ?></option>
			<option value="1" <?php if ('1' == $active): ?>selected="selected"<?php endif ?>>Adquirido</option>
			<option value="2" <?php if ('2' == $active): ?>selected="selected"<?php endif ?>>Não Adquirido</option>
		</select>
		</td>
		<td width="0%" align="center">
			<a href="javascript:;" class="btn btn-sm btn-primary filter" style="margin-top: 14px"><?php echo t('buttons.filtrar') ?></a>
		</td>
	</tr>
</table>

<br />
<div id="pet-list">
	<?php
		$others		= '';
		$actives	= '';
		
		unset($pets['pages']);
		unset($pets['pets']);
	?>
	<?php foreach ($pets as $pet): ?>
		<?php
			$class		= '';
			$attrbiutes	= '';
			$active		= false;

			if (isset($mine_pets[$pet->id])) {
				$attrbiutes	= 'data-id="' . $mine_pets[$pet->id] . '"';

				if ($active_pet == $pet->id) {
					$class	= 'active';
				}

				$active	= true;
			} else {
				$attrbiutes	= '';
				$class		= 'disabled locked';
			}

			ob_start();
		?>
		<div class="pet-box <?php echo $class ?>" <?php echo $attrbiutes ?> data-item="<?php echo $pet->id ?>" style="height: 200px !important; cursor: pointer;" data-message="<?php echo t('quests.pets.message');?>">
			<div class="content technique-popover" data-source="#pet-container-<?php echo $pet->id ?>" data-title="<?php echo $pet->description()->name ?>" data-trigger="click" data-placement="bottom">
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
							$exp_pet	= 0;
							$happiness	= 0;
							$info_pet	= $player->happiness_int($pet->id);
							if ($info_pet) {
								$exp_pet	= $info_pet->exp;
								$happiness	= $info_pet->happiness;
							}	
						?>
						<?=$player->happiness($pet->id);?><br />
						<?=$happiness;?> / 100
						<?php 
							switch ($pet->rarity) {
								// case "common":
								//  	$exp_total = 2500;
								//  	break;
								 case "rare":
									 $exp_total = 7500;
								 	break;
								 case "legendary":
									$exp_total = 20000;
								 	break;	
								default:
									$exp_total = 2500;
							}
						?>	
						<?php if ($pet->rarity != "mega") { ?>
							<div style="margin-top:10px">
								<?=pet_exp_bar($exp_pet, $exp_total, 150, $exp_pet . '/' . $exp_total);?>
							</div>
						<?php }?>	
					</div>
				</div>
				<div id="pet-container-<?php echo $pet->id ?>" class="technique-container">
					<div class="status-popover-content"><?php echo $pet->tooltip() ?></div>
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
	<?php endforeach ?>
	<?php echo $actives . $others ?>
	<div class="clearfix"></div><br />
</div>
<?php echo partial('shared/paginator', ['pages' => $pages, 'current' => $page + 1]) ?>
</form>