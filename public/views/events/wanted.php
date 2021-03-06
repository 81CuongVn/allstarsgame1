<?php echo partial('shared/title', array('title' => 'menus.wanted', 'place' => 'menus.wanted')) ?>
<?php if(!$player_tutorial->bijuus){?>
<script>
$(function () {
	 $("#conteudo.with-player").css("z-index", 'initial');
	 $(".info").css("z-index", 'initial');
	 $("#background-topo2").css("z-index", 'initial');
	
    var tour = new Tour({
	  backdrop: true,
	  page: 20,
	 
	  steps: [
	  {
		element: ".msg-container",
		title: "Cace e seja Caçado",
		content: "Ao vencer 10 Batalhas PVP seguidas, você se torna um Jogador Procurado, com um preço por sua cabeça! Além disso, você poderá derrotar outros jogadores do jeito especificado e coletar a recompensa pela cabeça deles!",
		placement: "bottom"
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
		'title'		=> 'wanted.title',
		'message'	=> t('wanted.description')
	]);
?>
<form id="wanteds-filter-form" method="post">
	<input type="hidden" name="page" value="<?php echo $page ?>" />
	<?php 
		if($wanteds){
			foreach ($wanteds as $wanted):
			$player_wanted = PlayerWanted::find_first("player_id=".$wanted->id ." AND death=0");
	?>
			<div class="bg-wanteds" data-id="<?php echo $player_wanted->player_id?>" data-embed="<?php echo $player_wanted->embed()?>">
				<div class="wanteds-foto">
					<img src="<?php echo image_url("wanted/". $wanted->character_id.".jpg" ) ?>" width="139" height="107"/>
				</div>
				<div class="wanteds-info">
					<b style="font-size:16px"><?php echo $wanted->name?></b><br />
					<span>
					<?php 
						echo t('wanted.'.$player_wanted->type_death);
					?>
					</span><br />
					<span style="font-size:14px"><?php echo $wanted->won_last_battle > 100 ? 100 * 250 : $wanted->won_last_battle * 250?> <?php echo t('currencies.' . $player->character()->anime_id) ?></span>
				</div>
			</div>
		<?php endforeach ?>
<div style="clear:both"></div>		
<?php echo partial('shared/paginator', ['pages' => $pages, 'current' => $page + 1]) ?>
	<?php
	}else{
		echo "<div align='center'><b class='laranja' style='font-size:14px;'>".t('wanted.sem_nenhum')."</b></div>";
	}
	?>	
</form>