<?php echo partial('shared/title', array('title' => 'menus.exploration', 'place' => 'menus.exploration')) ?>
<?php
	echo partial('shared/info', [
		'id'		=> 1,
		'title'		=> 'map.title',
		'message'	=> t('map.description')
	]);
?>
<br />
<?php foreach($map_animes as $map_anime):?>
	<div class="ability-speciality-box" data-id="<?php echo $map_anime->id?>" style="width: 237px !important; height: 290px !important">
		<div>
			<div class="image">
				<img src="<?php echo image_url('maps/buy/'.$map_anime->id.'.png') ?>" />
	
			</div>
			<div class="name" style="height: 15px !important;">
				<?php echo $map_anime->description()->name ?>
			</div>
			<div class="description" style="height: 28px !important;">
			<?php echo $map_anime->description()->description ?><br />
			</div>
			<div class="button" style="position:relative; top: 15px;">
				<?php $map_anime->set_player($player) ?>
				<?php if(!$player->map_id):?>
					<?php if(sizeof($map_anime->limit_by_day($map_anime->anime_id)) < 1):?>
						<a class="btn btn-primary unlock" style="width:160px; position: relative; top: 5px;" data-id="<?php echo $map_anime->id?>" data-mode="1">5 Passos gratuitos</a>
						<a class="btn btn-warning unlock" style="width:160px; position: relative; top: 10px;" data-id="<?php echo $map_anime->id?>" data-mode="2">10 Passos por <?php echo $map_anime->currency_cost?> <?php echo t('currencies.' . $player->character()->anime_id)?></a>
						<a class="btn btn-success unlock" style="width:160px; position: relative; top: 15px;" data-id="<?php echo $map_anime->id?>" data-mode="3">15 Passos por <?php echo $map_anime->credits_cost?> Estrelas</a>
					<?php else: ?>
						<a class="btn btn-danger"><?php echo t('friends.f26')?></a>
					<?php endif ?>			
				<?php else: ?>
					<a class="btn btn-danger"><?php echo t('challenges.challenge_active_error') ?></a>
				<?php endif ?>			
			</div>
		</div>
	</div>
<?php endforeach;?>