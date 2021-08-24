<?php echo partial('shared/title', array('title' => 'luck.index.title', 'place' => 'luck.index.title')) ?>
<?php if (FW_ENV != 'dev') { ?>
	<!-- AASG - Luck -->
	<ins class="adsbygoogle"
		style="display:inline-block;width:728px;height:90px"
		data-ad-client="ca-pub-6665062829379662"
		data-ad-slot="6444098891"></ins>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
	</script><br />
<?php } ?>
<div id="luck-container">
	<div id="daynames">
		<?php for($f = 1; $f <= 7; $f++): ?>
			<div class="dayname"><?php echo t('daynames.' . $f) ?></div>
		<?php endfor ?>
	</div>
	<div id="luck-status">
		<?php for($f = 1; $f <= 7; $f++): ?>
			<div class="day-<?php echo $f ?> day <?php echo $week_data && $week_data[$f] ? 'green' : '' ?>">
				<div class="ball"></div>
				<div class="check"></div>
			</div>
		<?php endfor ?>
	</div>
	<div id="luck-stripes">
		<div id="luck-stripe-1" class="luck-stripe"></div>
		<div id="luck-stripe-2" class="luck-stripe"></div>
		<div id="luck-stripe-3" class="luck-stripe"></div>
		<div id="luck-stripe-4" class="luck-stripe"></div>
	</div>
	<div id="luck-stripes-shadows">
		<div></div>
		<div></div>
		<div></div>
		<div></div>
	</div>
	<div id="luck-mask"></div>
	<div id="luck-types">
		<div class="daily"><?php echo t('luck.index.daily') ?></div>
		<div class="weekly"><?php echo t('luck.index.weekly') ?></div>
	</div>
	<div id="buttons">
		<div class="daily">
			<div class="button" data-type="daily" data-currency="1">
				<span><?php echo t('luck.daily.currency', array('total' => highamount($daily_currency), 'currency' => t('currencies.' . $player->character()->anime_id))) ?></span>
			</div>
			<div class="button" data-type="daily" data-currency="2">
				<span><?php echo t('luck.daily.credits', array('total' => highamount($daily_credits), 'credits' => t('currencies.credits'))) ?></span>
			</div>
		</div>
		<div class="weekly">
			<div class="button" data-type="weekly" data-currency="1">
				<span><?php echo t('luck.weekly.currency', array('total' => highamount($weekly_currency), 'currency' => t('currencies.' . $player->character()->anime_id))) ?></span>
			</div>
			<div class="button" data-type="weekly" data-currency="2">
				<span><?php echo t('luck.weekly.credits', array('total' => highamount($weekly_credits), 'credits' => t('currencies.credits'))) ?></span>
			</div>
		</div>
	</div>
	<div id="luck-button"><span><?php echo t('luck.index.play') ?></span></div>
	<div id="result"></div>
</div>
<br />
<ul class="nav nav-pills nav-justified" id="luck-list-tabs">
	<?php $first = true; ?>
	<?php foreach ($item_type_ids->result() as $item_type_id): ?>
		<?php
			switch($item_type_id->item_type_id){
				case 1:
					$name = t('currencies.' . $player->character()->anime_id);
					break;
				default:
					$name = t('luck.index.names.'.$item_type_id->item_type_id);
					break;
			}
		?>

		<li <?php echo $first ? 'class="active"' : '' ?>><a href="#luck-tab-<?php echo $item_type_id->item_type_id ?>"><?php echo $name ?></a></li>
		<?php $first = false; ?>
	<?php endforeach ?>
</ul>
<br />
<div class="tab-content" id="luck-list-content">
	<?php $first = true; ?>
	<?php foreach ($item_type_ids->result() as $item_type_id): ?>
	<div class="tab-pane<?php echo $first ? ' active' : '' ?>" id="luck-tab-<?php echo $item_type_id->item_type_id ?>">

		<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>">
			<table width="725" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td align="center"><?php echo t('luck.index.header.name') ?></td>
					<td align="center" width="120"><?php echo t('luck.index.header.chance') ?></td>
					<td align="center" width="220"><?php echo t('luck.index.header.won') ?></td>
				</tr>
			</table>
		</div>
		<table width="725" id="luck-reward-list">
			<?php $counter = 0; ?>
			<?php foreach ($reward_list->result() as $choosen_reward): ?>
				<?php if ($choosen_reward->item_type_id != $item_type_id->item_type_id) { continue; } ?>

				<?php
					$color	= $counter++ % 2 ? '091e30' : '173148';
				?>
				<tr bgcolor="<?php echo $color ?>">
					<td align="center">
						<?php
							$message	= '';

							if($choosen_reward->enchant_points) {
								$message	.= highamount($choosen_reward->quantity) . ' ' . t('luck.index.names.8');
							}

							if($choosen_reward->currency) {
								$message	.= highamount($choosen_reward->currency) . ' ' . t('currencies.' . $player->character()->anime_id);
							}
							if($choosen_reward->exp) {
								$message	.= highamount($choosen_reward->exp) . ' ' . t('attributes.attributes.exp2');
							}

							if($choosen_reward->credits) {
								$message	.= highamount($choosen_reward->credits) . ' ' . t('currencies.credits');
							}
							if($choosen_reward->equipment) {
								$message	.= $choosen_reward->equipment . ' ' . t('luck.index.header.equipment');
							}

							if($choosen_reward->item_id) {
								$item		= Item::find_first($choosen_reward->item_id);
								$message	.= $item->description()->name . ' x' . $choosen_reward->quantity;
							}

							$ats	= array(
								'for_atk'		=> t('formula.for_atk'),
								'for_def'		=> t('formula.for_def'),
								'for_crit'		=> t('formula.for_crit'),
								'for_abs'		=> t('formula.for_abs'),
								'for_prec'		=> t('formula.for_prec'),
								'for_init'		=> t('formula.for_init'),
								'for_inc_crit'	=> t('formula.for_inc_crit'),
								'for_inc_abs'	=> t('formula.for_inc_abs')
							);

							foreach ($ats as $key => $value) {
								if($choosen_reward->$key) {
									$message	.= t('luck.index.messages.point', array('count' => highamount($choosen_reward->$key), 'attribute' => $value));
								}
							}
							echo $message;
						?>
					</td>
					<td align="center" width="120"><?php echo $choosen_reward->chance ?>%</td>
					<td align="center" width="220">
						<?php if ($choosen_reward->total): ?>
							<?php echo t('luck.index.won_count', array('count' => $choosen_reward->total)) ?>
						<?php endif ?>
					</td>
				</tr>
				<tr height="4"></tr>
			<?php endforeach ?>
		</table>
	</div>
	<?php $first = false; ?>
	<?php endforeach ?>
</div>
