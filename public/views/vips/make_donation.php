<?php echo partial('shared/title', array('title' => 'vips.purchase.title', 'place' => 'vips.purchase.title')) ?>
<?php
	if($is_dbl){
		$timestamp = strtotime($is_dbl->data_end);
		echo partial('shared/info', [
		'id'		=> 4,
		'title'		=> 'vips.make_donation.title',
		'message'	=> t('vips.make_donation.description') . '<span class="laranja">'. date('d/m/Y H:i:s', $timestamp) .'<span>'
		]);
	}
?>
<br />
<ul class="nav nav-pills nav-justified" id="methods-details-tabs">
	<?php $i = 1; foreach($methods as $method => $currency) { ?>
	<li class="<?php echo $i == 1 ? 'active' : ''; ?>">
		<a href="#method-<?php echo $method?>-list" role="tab" data-toggle="tab">
			<img src="<?php echo image_url($method . ".png")?>" width="147"/>
		</a>
	</li>
	<?php $i++; } ?>
</ul>
<br />
<div class="tab-content">
	<?php $i = 1; foreach($methods as $method => $currency) { ?>
	<div id="method-<?php echo $method?>-list" class="tab-pane <?php echo $i == 1 ? 'active' : ''; ?>">
		<?php foreach($plans as $plan):?>
		<div class="ability-speciality-box" data-id="<?php echo $plan->id?>" style="width: 237px !important; height: 260px !important">
			<div>
				<div class="image">
					<img src="<?php echo image_url('stars/'.$plan->id.'.png') ?>" />
		
				</div>
				<div class="name" style="height: 15px !important;">
					<?php echo $plan->name ?>
				</div>
				<div class="description" style="height: 40px !important;">
				<?php echo $plan->description ?><br />
				</div>
				<div class="details">
					<img src="<?php echo image_url("icons/vip-on.png" ) ?>" width="26" height="26"/><span class="amarelo_claro" style="font-size: 16px; margin-left: 5px; top: 2px; position: relative"><?php echo $is_dbl ? '<span class="vermelho" style="text-decoration: line-through; font-size: 12px">'.$plan->coin.'</span><span class="verde"> '. $plan->coin*2 .'</span>' :  $plan->coin?></span>
				</div>
				<div class="button" style="position:relative; top: 15px;">
					<a class="btn btn-sm btn-primary vip_purchase" data-message="<?php echo t('vips.done_donation.you_have') ?> <?php echo $currency; ?> <?php echo $plan->valor ?>, <?php echo t('vips.done_donation.you_have2') ?>" data-mode="<?php echo $plan->id?>" data-valor="<?php echo $method; ?>"><?php echo t('vips.done_donation.donation_by') ?> <?php echo $currency; ?> <?php echo $plan->valor ?></a>
				</div>
			</div>
		</div>
		<?php endforeach?>
	</div>
	<?php $i++; } ?>
</div>	