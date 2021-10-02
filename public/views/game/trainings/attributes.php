<?php echo partial('shared/title', array('title' => 'attributes.attributes.title', 'place' => 'attributes.attributes.title')) ?>
<?php if (FW_ENV != 'dev') { ?>
	<!-- AASG - Treinamento -->
	<ins class="adsbygoogle"
		style="display:inline-block;width:728px;height:90px"
		data-ad-client="ca-pub-6665062829379662"
		data-ad-slot="5650532609"></ins>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
	</script><br />
<?php } ?>
<?php if (!$player_tutorial->treinamento) { ?>
	<script type="text/javascript">
		$(function () {
			var tour = new Tour({
				backdrop: true,
				page: 8,
				steps: [{
					element: "#training-distribute-container",
					title: "Fique mais Forte!",
					content: "A cada tantos pontos treinados em um Atributo, você irá receber um ponto completo no mesmo. Evolua o seu Nível de Conta para receber mais pontos livres!",
					placement: "top"
				}]
			});

			tour.restart();
			tour.init(true);
			tour.start(true);
		});
	</script>
<?php } ?>
<?php /*<div id="traning-limit-container">
	<?=partial('traning_limit', [
		'player' => $player
	]) ?>
</div><br />
<div class="barra-secao">
	<table width="725">
		<tr>
			<td width="325" class="text-center"><?=t('attributes.attributes.headers.manual_training');?></td>
			<td width="250" class="text-center"><?=t('attributes.attributes.headers.quantity');?></td>
			<td width="150" class="text-center"><?=t('attributes.attributes.headers.status');?></td>
		</tr>
	</table>
</div>
<form id="training-attribute-basic">
	<table width="725">
		<tr>
			<td class="text-center" width="325">
				<?=t('attributes.attributes.will_spend_stamina');?>
				<span class="stamina">--</span>
				<img width="16" src="<?=image_url('icons/for_stamina.png');?>" />
			</td>
			<td align="center" width="250">
				<img src="<?=image_url("icons/for_stamina.png");?>" />
				<select class="form-control input-sm" style="width: auto; display: inline-block;" name="stamina" <?=($player->for_stamina() < 1 ? 'disabled' : '')?>>
				<!-- <select style="width: 65px;" class="form-control input-sm" name="stamina"> -->
					<?php for ($i = 1; $i <= $player->for_stamina(); $i++){ ?>
						<option value="<?=$i;?>"><?=$i;?></option>
					<?php } ?>
				</select>
			</td>
			<td align="center" width="150">
				<?php if ($player->training_points_spent < $player->max_attribute_training()): ?>
					<a class="btn btn-primary btn-sm train"><?php echo t('attributes.attributes.train') ?></a>
				<?php else: ?>
					<a class="btn btn-danger btn-sm disabled"><?php echo t('attributes.attributes.train') ?></a>
				<?php endif ?>
			</td>
		</tr>
	</table>
</form><br />*/ ?>
<div id="training-distribute-container"></div>
