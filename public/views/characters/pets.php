<?=partial('shared/title', [
    'title' => 'menus.pets',
    'place' => 'menus.pets'
]);?>
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
<?=partial('shared/info', [
    'id'		=> 1,
    'title'		=> 'quests.pets.title',
    'message'	=> t('quests.pets.description')
]);?><br />
<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
	<p>Filtro de Mascotes</p>
</div>
<form id="pets-filter-form" method="post">
	<input type="hidden" name="page" value="<?=$page;?>" />
    <div class="row filtros">
        <div class="col-xs-4 text-center">
            <b><?=t('rankings.players.header.nome');?></b><br />
            <input type="text" class="form-control input-sm" name="name" value="<?=$name;?>" />
        </div>
        <div class="col-xs-3 text-center">
            <b><?=t('characters.create.labels.anime');?></b><br />
            <select name="anime" class="form-control input-sm select2" style="margin-bottom: 15px;">
                <option value="all" <?=('all' == $anime ? 'selected' : '');?>><?=t('global.all');?></option>
                <?php foreach ($animes as $a) { ?>
                    <option value="<?=$a->id;?>" <?=($a->id == $anime ? 'selected' : '');?>><?=$a->description()->name;?></option>
                <?php } ?>
            </select>
        </div>
        <div class="col-xs-3 text-center">
            <b><?=t('rankings.players.header.raridade');?></b><br />
            <select name="rarity" class="form-control input-sm">
                <option value="all" <?=('all' == $rarity ? 'selected' : '');?>><?=t('global.all');?></option>
                <option value="common" <?=('common' == $rarity ? 'selected' : '');?>>Comum</option>
                <option value="rare" <?=('rare' == $rarity ? 'selected' : '');?>>Raro</option>
                <option value="legendary" <?=('legendary' == $rarity ? 'selected' : '');?>>Lendário</option>
                <option value="mega" <?=('mega' == $rarity ? 'selected' : '');?>>Mega</option>
            </select>
        </div>
        <div class="col-xs-2 text-center">
            <a href="javascript:void(0);" class="btn btn-sm btn-primary filter" style="margin-top: 14px"><?=t('buttons.filtrar');?></a>
        </div>
    </div>
    <div id="pet-list">
        <?php foreach ($pets as $pet) { ?>
            <?php $item = Item::find_first('id = ' . $pet['item_id']); ?>
            <div class="pet-box <?=($pet['equipped'] ? 'active' : '');?>" data-item="<?=$item->id;?>" style="height: 200px !important; cursor: pointer;" data-message="<?=t('quests.pets.message');?>">
				<div class="content technique-popover" data-source="#pet-container-<?=$item->id;?>" data-title="<?=$item->description()->name;?>" data-trigger="click" data-placement="bottom">
					<div class="image">
						<?=$item->image();?>
					</div>
					<div class="name <?=$item->rarity;?>" style="height: 33px !important">
						<?=$item->description()->name;?><br />
						<span style="font-size: 11px">(<?=$item->anime_description()->name;?>)</span>
					</div>
					<div class="details">
						<div class="pet-tooltip">
							<?php 
								$exp_pet	= 0;
								$happiness	= 0;
								$info_pet	= $player->happiness_int($item->id);
								if ($info_pet) {
									$exp_pet	= $info_pet->exp;
									$happiness	= $info_pet->happiness;
								}	
							?>
							<?=$player->happiness($item->id);?><br />
							<?=$happiness;?> / 100
							<?php 
								switch ($item->rarity) {
									case "common":
										$exp_total = 2500;
										break;
									case "rare":
										$exp_total = 7500;
										break;
									case "legendary":
										$exp_total = 20000;
										break;
								}
							?>	
							<?php if ($item->rarity != "mega") { ?>
								<div style="margin-top:10px">
									<?=pet_exp_bar($exp_pet, $exp_total, 150, highamount($exp_pet) . '/' . highamount($exp_total));?>
								</div>
							<?php }?>	
						</div>
					</div>
					<div id="pet-container-<?=$item->id;?>" class="technique-container">
						<div class="status-popover-content"><?=$item->tooltip();?></div>
					</div>
				</div>
			</div>
        <?php } ?>
        <div class="clearfix"></div>
        <?php if (!sizeof($pets)) { ?>
            <div class="alert alert-info">
                <h4>Oops!</h4>
                <p>Hmmm. Parece que não encontramos nenhum mascote por aqui!</p>
            </div>
        <?php } ?>
    </div><br />
    <?=partial('shared/paginator', [
        'pages'     => $pages,
        'current'   => ($page + 1)
    ]);?>
</form>