<?php echo partial('shared/title', array('title' => 'menus.grimoire', 'place' => 'menus.grimoire')) ?>
<?php if(!$player_tutorial->escola){?>
<script>
$(function () {
	 $("#conteudo.with-player").css("z-index", 'initial');
	 $(".info").css("z-index", 'initial');
	 $("#background-topo2").css("z-index", 'initial');
	
    var tour = new Tour({
	  backdrop: true,
	  page: 5,
	 
	  steps: [
	  {
		element: ".tutorial-grimoire",
		title: "Encontrando Páginas Perdidas",
		content: "Aprenda novos Golpes adquirindo todos as Páginas Perdidas do golpe em questão. Você tem chance de encontrar Páginas no final de cada Batalha realizada.",
		placement: "top"
	  }
	]});
	//Renicia o Tour
	tour.restart();
	
	// Initialize the tour
	tour.init(true);
	
	// Start the tour
	tour.start(true);
	
});
</script>	
<?php }?>
<?php
	echo partial('shared/info', [
		'id'		=> 1,
		'title'		=> 'grimoire.title',
		'message'	=> t('grimoire.description')
	]);
?>
<br />
<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
	<table width="725" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="80">&nbsp;</td>
		<td width="200" align="center"><?php echo t('rankings.players.header.nome') ?></td>
		<td width="300" align="center"><?php echo t('grimoire.cartas') ?></td>
		<td width="120" align="center"><?php echo t('grimoire.status') ?></td>
	</tr>
	</table>
</div>
<table width="725" border="0" cellpadding="0" cellspacing="0" class="tutorial-grimoire">
	<?php $counter = 0; ?>
	<?php 	foreach($items as $item):

			$color	= $counter++ % 2 ? '091e30' : '173148';
	?>
		<tr bgcolor="<?php echo $color ?>">
			<td width="80" align="center">
				<img src="<?php echo image_url($item->image(true)) ?>" class="technique-popover item-image" data-source="#technique-content-<?php echo $item->id ?>" data-title="<?php echo $item->description()->name ?>" data-trigger="hover" data-placement="bottom" />
				<div class="technique-container" id="technique-content-<?php echo $item->id ?>">
					<?php echo $item->technique_tooltip() ?>
				</div>
			</td>
			<td width="200" align="center">
				<b class="amarelo" style="font-size:14px; position: relative; top: 5px;"><?php echo $item->description()->name ?></b>
			</td>
			<td width="300" align="center">
				<?php

					foreach($player->pages($item->id) as $page):
				?>	
					<?php 
						$player_pages = $player->player_pages($page->id);
						$class="";
						if(!$player_pages){
							$class = "style='opacity:.3'";	
						}
					?>	
					<div class="technique-popover" data-source="#pages-container-<?php echo $page->id ?>" data-title="<?php echo $page->description()->name ?>" data-trigger="click" data-placement="bottom" style="display:inline-block; text-align:center">
						<div><img src="<?php echo image_url('grimoire/'.$page->description()->image)?>" width="40" <?php echo $class?> /></div>
					</div>						
				<?php endforeach ?>		
			</td>
			<td width="120" align="center">
				<?php 
					$player_pages_ok  = $player->player_pages_ok($item->id);
				?>
				<?php if($player_pages_ok){?>
					<?php if($player->has_item($item->id)){?>
						<a href="javascript:;" class="btn btn-success"><?php echo t('grimoire.desbloqueado') ?></a>
					<?php }else{ ?>	
						<a href="javascript:;" class="btn btn-primary player-item-finish" data-id="<?php echo $item->id ?>"><?php echo t('grimoire.desbloquear') ?></a>
					<?php } ?>		
				<?php }else{ ?>	
					<a href="javascript:;" class="disabled btn btn-danger"><?php echo t('grimoire.desbloquear') ?></a>
				<?php } ?>	
			</td>
		</tr>
		<tr height="4"></tr>
	<?php endforeach ?>
</table>