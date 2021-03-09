<script type="text/javascript">       
	$(document).ready(function(){
		$('#my-slide').DrSlider(); //Yes! that's it!
	});
</script> 
<div id="descricao-topo">
	<p><?php echo t('global.descricao', ['game' => GAME_NAME])?></p>
</div>
<div id="banner">
	<div id="my-slide">
		<a href="<?=make_url('users/join');?>"><img data-lazy-src="<?php echo image_url('banner.png') ?>" /></a>
		<a href="https://www.youtube.com/watch?v=CjXpT0RP2ys" target="_blank"><img data-lazy-src="<?php echo image_url('banner2.png') ?>" /></a>
		<a href="<?=make_url('characters/create');?>"><img data-lazy-src="<?php echo image_url('banner3.png') ?>" /></a>
		<a href="<?=make_url('characters/create');?>"><img data-lazy-src="<?php echo image_url('banner4.png') ?>" /></a>
	</div>
</div>

<div id="noticias">
	<div class="noticias-1">
		<ul class="list" style="height: 170px;">
			<?php echo t('global.wait')?>
		</ul>
		<div class="noticias-buttons">
			<div class="prev"></div>
			<div class="next"></div>
		</div>
	</div>
	<div class="noticias-2">
		<div class="players-list" style="height:170px">
			<?php echo t('global.wait')?>
		</div>
		<div class="noticias-buttons">
			<div class="prev"></div>
			<div class="next"></div>
		</div>
	</div>
	<div class="noticias-5">
		<div style="position: absolute; top: 41px; left: 89px;">
			<?php if (sizeof($leagues)): ?>
				<select name="sl-leagues" id="sl-leagues" class="form-control input-sm" style="line-height: 19px;height: 19px;padding: 2px 5px;">
					<?php foreach ($leagues as $league): ?>
						<option value="<?php echo $league->league ?>"><?php echo $league->league ?></option>
					<?php endforeach ?>
				</select>
			<?php endif; ?>
		</div>
		<div class="leagues-list" style="height:135px">
		
		</div>
	</div>
	<div class="noticias-3">
		<div class="tops-list" style="height:170px">
			<?php echo t('global.wait')?>
		</div>			
		<div class="noticias-buttons">
			<div class="prev"></div>
			<div class="next"></div>
		</div>
	</div>
	<div class="noticias-4">
		<div style="position: absolute; top: -33px; left: 153px;">
			<select name="sl-ranks" id="sl-ranks" class="form-control input-sm" style="line-height: 19px;height: 19px;padding: 2px 5px;">
				<option value="players">Personagem</option>
				<option value="achievements">Conquista</option>
				<option value="organizations">Organização</option>
				<option value="accounts">Conta</option>
			</select>
		</div>
		<div class="ranks-list" style="height:170px">
			<?php echo t('global.wait')?>
		</div>	
		<div class="noticias-buttons">
			<div class="prev"></div>
			<div class="next"></div>
		</div>
	</div>
</div>