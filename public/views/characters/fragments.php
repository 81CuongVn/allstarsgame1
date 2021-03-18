<?php echo partial('shared/title', array('title' => 'menus.fragments', 'place' => 'menus.fragments')) ?>
<?php if(!$player_tutorial->mercado){?>
<script>
$(function () {
	 $("#conteudo.with-player").css("z-index", 'initial');
	 $(".info").css("z-index", 'initial');
	 $("#background-topo2").css("z-index", 'initial');
	
    var tour = new Tour({
	  backdrop: true,
	  page: 6,
	 
	  steps: [
	  {
		element: ".msg-container",
		title: "Consiga Fragmentos das Almas",
		content: "No final de cada Batalha você tem a chance de conseguir entre 1 e 10 Fragmentos das Almas. Além disso, cada Equipamento destruído lhe dará uma certa quantidade de Fragmentos.",
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
<?php if(isset($_GET['message']) && $_GET['message']){?>
	<div class="alert alert-success" role="alert">
		<?php echo base64_decode($_GET['message']);?>
	</div>
<?php }?>
<br />
<br />

<div class="msg-container">
	<div class="msg_top"></div>
	 <div class="msg_repete">
		<div class="msg" style="background:url(<?php echo image_url('msg/fragmentos.png')?>); background-repeat: no-repeat;">
		</div>
		<div class="msgb" style="position:relative; margin-left: 231px; text-align: left; top: -37px">
			<b><?php echo t('fragments.title') ?></b>
			<div class="content"><?php echo t('fragments.descriptions') ?></div>
		</div>
	</div>
	<div class="msg_bot"></div>	
	<div class="msg_bot2"></div>
</div>
<br />
<?php
	$total = $total ? $total->quantity : 0;

$color	= [
				'commom',
				'commom',
			];
$rarities	= [
				'0',
				'1',
			];
			
$prices	= [
				'100',
				'100',
			];
$names	= [
				'Equipamento Aleatório',
				'Mascote Aleatório',
			];
$descriptions	= [
				'Transforme 100 Fragmentos das Almas em um Equipamento aleatório',
				'Transforme 100 Fragmentos das Almas em um Mascote aleatório',
			];									
	
?>
<?php foreach($rarities as $rarity):?>
<div class="ability-speciality-box" data-id="<?php echo $rarity?>" style="width: 350px !important; height: 275px !important">
	<div>
		<div class="image">
			<img src="<?php echo image_url('fragments/'.$rarity.'.png') ?>" />
		</div>
		<div class="name <?php echo $color[$rarity]?>" style="height: 15px !important;">
			<?php echo $names[$rarity]?>
		</div>
		<div class="description" style="height: 40px !important;">
		<?php echo $descriptions[$rarity]?><br />
		</div>
		<div class="details">
			<img src="<?php echo image_url("icons/fragmento.png" ) ?>" width="26" height="26"/><span class="amarelo_claro" style="font-size: 16px; margin-left: 5px; top: 2px; position: relative"><?php echo $total ?> / <?php echo $prices[$rarity]?></span>
		</div>
		<div class="button" style="position:relative; top: 15px;">
			<?php if($total >= $prices[$rarity]){?>
				<a class="fragments_change btn btn-sm btn-primary" data-mode="<?php echo $rarity?>"><?php echo t('fragments.change') ?></a>
			<?php }else{?>
				<a class="btn btn-sm btn-danger"><?php echo t('fragments.change') ?></a>
			<?php }?>	
		</div>
	</div>
</div>
<?php endforeach;?>
