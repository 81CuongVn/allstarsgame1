<?php echo partial('shared/title', array('title' => 'menus.events_anime', 'place' => 'menus.events_anime')) ?>
<?php if (!$player_tutorial->battle_village) { ?>
	<script type="text/javascript">
		$(function () {
			var tour = new Tour({
				backdrop: true,
				page: 19,
				steps: [{
					element: ".ev-main",
					title: "Ajude seu Anime!",
					content: "Dois animes serão sorteados a cada três horas para a luta, se ao término das três horas, nenhum anime estiver zerado, será declarado vencedor o que tiver mais pontos."+
					"O anime zerado perde a batalha instantaneamente, porém, uma nova batalha só terá início após as três horas.",
					placement: "top"
				}, {
					element: ".ev-reward",
					title: "Seja Recompensado!",
					content: "O Anime vencedor receberá um bônus de 24 horas de Experiência, Moedas e Chance de conseguir Itens em cada batalha! Aproveite!",
					placement: "left"
				}, {
					element: ".anime-ranking",
					title: "Ranking de Animes!",
					content: "Aqui você poderá acompanhar o ranking da Batalha de Animes!",
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
	'title'		=> 'event.title',
	'message'	=> t('event.description')
]);

if ($activeEvent) {
	$points = ($activeEvent->points_a)  / 2000;
?>
<div class="ev-main">
	<div class="ev-logo">
		<img src="<?=image_url('events/anime/' . $activeEvent->anime_a_id . '.png');?>"/><br />
		<span class="amarelo ev-size"><?=$activeEvent->description($activeEvent->anime_a_id)->name;?></span>
	</div>
	<div class="ev-vs">
		<img src="<?=image_url('battle/vs2.png');?>" width="200"/>
	</div>
	<div class="ev-logo">
		<img src="<?=image_url('events/anime/' . $activeEvent->anime_b_id . '.png');?>" /><br />
		<span class="amarelo ev-size"><?=$activeEvent->description($activeEvent->anime_b_id)->name;?></span>
	</div>
	<div class="ev-barra">
		<span class="ev-anime-a"><?=highamount($activeEvent->points_a);?></span>
	</div>
	<div class="ev-barra-red">
		<div style="width: <?=($points * 512);?>px; height:16px; background-image:url(<?=image_url('events/blue.png');?>)"></div>
	</div>
	<div class="ev-barra-b">
		<span class="ev-anime-b"><?=highamount($activeEvent->points_b);?></span>
	</div>
</div>
<?php } else { ?>
	<div align="center" style="padding: 20px 0;">
		<b class="laranja" style="font-size:14px;">
			No momento não existe nenhuma batalha entre animes em andamento!
		</b>
	</div>
<?php } ?>
<div class="ev-last">
	<div class="titulo-home">
		<p><?=t('event.e1');?></p>
	</div>
	<div class="ev-last-anime">
		<?php if ($lastWinner) { ?>
			<img src="<?=image_url('events/anime/' . $lastWinner->anime_win_id . '.png');?>"/><br />
			<span class="amarelo ev-size"><?=$lastWinner->description($lastWinner->anime_win_id)->name;?></span>
		<?php } else { ?>
		    <img src="<?=image_url('events/anime/0.png');?>"/><br />
		<?php } ?>
	</div>
</div>	
<div class="ev-reward">
	<div class="titulo-home">
		<p><?=t('event.e3');?></p>
	</div>
	<div align="center" class="ev-padding">
		<div class="ev-req requirement-popover" data-source="#tooltip-req-exp" data-title="<?=t('event.e4');?>" data-trigger="hover" data-placement="bottom">
			<img src="<?=image_url('events/exp.png');?>"/>
			<div id="tooltip-req-exp" class="status-popover-container">
				<div class="status-popover-content" style="margin: 10px">
					<?=t('event.e5');?>
				</div>
			</div>	
		</div>
		<div class="ev-req requirement-popover" data-source="#tooltip-req-gold" data-title="<?=t('event.e7');?>" data-trigger="hover" data-placement="bottom">
			<img src="<?=image_url('events/gold.png');?>"/>
			<div id="tooltip-req-gold" class="status-popover-container">
				<div class="status-popover-content" style="margin: 10px">
					<?=t('event.e7');?>
				</div>
			</div>	
		</div>
		<div class="ev-req requirement-popover" data-source="#tooltip-req-drop" data-title="<?=t('event.e8');?>" data-trigger="hover" data-placement="bottom">
			<img src="<?=image_url('events/drop.png');?>"/>
			<div id="tooltip-req-drop" class="status-popover-container">
				<div class="status-popover-content" style="margin: 10px">
					<?=t('event.e9');?>
				</div>
			</div>	
		</div>			
	</div>
</div>
<div style="clear: left" class="titulo-home3">
	<p><?=t('event.e10');?></p>
</div><br />
<?php $counter = 0; foreach ($animes as $anime) { ?>
	<div class="<?=($counter++ == 0 ? 'anime-ranking' : '');?> ability-speciality-box" style="width: 175px !important; height: 220px !important; padding-bottom: 40px">
		<div>
			<div class="image">
				<img src="<?=image_url('events/anime/' . $anime->id . '.png');?>" width="150"/>
			</div>
			<div class="name" style="height: 40px !important;">
				<span class="amarelo">
					<?=$anime->description()->name;?>
				</span>
			</div>
			<div class="description">
				<b class="laranja">Vitórias</b><br />
				<?=highamount($anime->score);?>
			</div>
		</div>
	</div>
<?php } ?>