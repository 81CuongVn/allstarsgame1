<?php echo partial('shared/title', array('title' => 'ranked.liga', 'place' => 'ranked.liga')) ?>
<?php if (!$player_tutorial->battle_ranked) { ?>
	<script type="text/javascript">
		$(function () {
			var tour = new Tour({
				backdrop: true,
				page: 23,
				steps: [{
					element: ".msg-container",
					title: "Vire um All-Star!",
					content: "Vença batalhas, ganhe pontos e suba de Rank até virar um All-Star! A tarefa será difícil, e tome cuidado para não descer de Rank! Boa sorte!",
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
	echo partial('shared/info', array(
		'id'		=> 1,
		'title'		=> 'battles.ranked.title',
		'message'	=> t('battles.ranked.description')
	));
?>
<?php if ($ranked) { ?>
	<div class="msg-container-off2">
		<div class="msgb-h2" style="position: relative; margin-left: 24px; text-align: left; top: 36px">
			<div style="padding-left: 10px; float: left;">
				<b>
				<?php
				if (!$ranked->finished) {
					$daysLeft = ceil((strtotime($ranked->finish_date) - now()) / 86400);
					echo "Progressão da Liga {$ranked->id} - Restam {$daysLeft} dia(s)";
				} else {
					echo "Liga {$ranked->id} Concluída em " . date('d/m/Y H:i:s', strtotime($ranked->finish_date));
				}
				?>
				</b>
			</div>
			<div style="float: right; position:relative; right: 49px; top: -3px">
				<form id="league-filter-form" method="post">
					<select class="form-control input-sm" name="leagues" id="leagues" style="height: 23px; line-height: 23px; padding: 3px; margin-bottom: -7px;">
						<?php
						foreach ($rankeds as $row) {
							echo "<option value=\"{$row->id}\" " . ($row->id == $ranked->id ? "selected=\"selected\"" : "") . ">Liga {$row->id}</option>";
						}
						?>
					</select>
				</form>
			</div>
			<div class="content">
				<style type="text/css">
					.tier {
						width: 57px;
						height: 156px;
						background-image: url('<?=image_url('ranked/bg_off.jpg');?>');
					}
					.tier.on {
						background-image: url('<?=image_url('ranked/bg_on.jpg');?>');
					}
					.tier .name {
						color: hsl(208deg 56% 46%);
						width: 57px;
						height: 156px;
						writing-mode: vertical-rl;
						transform: rotateX(180deg) scaleX(-1) scale(1.6, 1);
						font-size: 22px;
						text-transform: uppercase;
						padding: 5px 22px 5px 20px;
						text-align: center;
						font-weight: bold;
					}
					.tier.on .name {
						color: hsl(0deg 0% 100% / 85%);
					}
				</style>
				<div>
					<?php foreach ($tiers as $tier) { ?>
						<div style="float: left; padding-left: 5px" class="technique-popover" data-source="#liga-container-<?=$tier->sort;?>" data-title="<?=$tier->description()->name;?>" data-trigger="click" data-placement="bottom">
							<?php if ($tier->sort == 0) { ?>
								<div style="position: absolute; top: 57px; right: 11px;">
									<img src="<?=image_url($player_ranked && $player_ranked->tier()->sort <= $tier->sort ? 'ranked/star_on.png' : 'ranked/star_off.png');?>" />
								</div>
							<?php } ?>
							<!-- <div style="position: absolute; top: 57px; margin-left: -11px;">
								<img src="<?=image_url('ranked/tiers/' . ($tier->sort != 0 ? $tier->id : $tier->id . '-off') . '.png');?>" width="80" height="76" />
							</div> -->
							<div class="tier <?=($player_ranked && $player_ranked->tier()->sort <= $tier->sort ? 'on' : 'off')?>">
								<?php if ($tier->sort != 0) { ?>
									<div class="name"><?=$tier->description()->name;?></div>
								<?php } ?>
							</div>
						</div>
						<div id="liga-container-<?=$tier->sort;?>" class="technique-container">
							<div style="min-width: 220px;">
								<span class="amarelo" style="font-size:14px"><?=t('ranked.promotion');?></span><br /><br />
								<?php if ($tier->min_points) { ?>
									<span class="vermelho"><?=t('ranked.down');?>:</span> <?=highamount($tier->min_points);?> <?=t('ranked.points2');?><br />
								<?php } ?>
								<?php if ($tier->max_points) { ?>
									<span class="verde"><?=t('ranked.up');?>:</span> <?=highamount($tier->max_points);?> <?=t('ranked.points');?><br />
								<?php } ?>
								<?php if ($rewards = $ranked->reward($tier->id)) { ?>
									<br /><span class="amarelo" style="font-size:14px"><?php echo t('ranked.reward');?></span><br /><br />
									<?php if ($rewards->exp) { ?>
										<li><?php echo highamount($rewards->exp); ?> <?php echo t('ranked.exp');?></li><br />
									<?php } ?>
									<?php if ($rewards->exp_user) { ?>
										<li><?php echo highamount($rewards->exp_user); ?> <?php echo t('ranked.exp_account');?></li><br />
									<?php } ?>
									<?php if ($rewards->currency) { ?>
										<li><?php echo highamount($rewards->currency); ?> <?php echo t('currencies.' . $player->character()->anime_id) ?></li><br />
									<?php } ?>
									<?php if ($rewards->credits) { ?>
										<li><?php echo highamount($rewards->credits); ?> <?php echo t('treasure.show.credits')?></li><br />
									<?php } ?>
									<?php if ($rewards->item_id) { ?>
										<li><?php echo highamount($rewards->quantity); ?>x "<?php echo Item::find($rewards->item_id)->description()->name ?>"</li><br />
									<?php } ?>
									<?php if ($rewards->character_theme_id) { ?>
										<li><?php echo t('treasure.show.theme')?> "<?php echo CharacterTheme::find($rewards->character_theme_id)->description()->name ?>"</li><br />
									<?php } ?>
									<?php if ($rewards->character_id) { ?>
										<li><?php echo t('treasure.show.character')?> "<?php echo Character::find($rewards->character_id)->description()->name ?>"</li><br />
									<?php } ?>
									<?php if ($rewards->headline_id) { ?>
										<li><?php echo t('treasure.show.headline')?> "<?php echo Headline::find($rewards->headline_id)->description()->name ?>"</li><br />
									<?php } ?>
								<?php } ?>
							</div>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
	<div style="width: 730px; height: 185px; position: relative; left: 24px">
		<div class="h-missoes">
			<div style="width: 341px; text-align: center; padding-top: 12px">
				<b class="amarelo" style="font-size:13px">
					<?php if ($player_ranked) { ?>
						<?=$player_ranked->tier()->description()->name;?>
					<?php } else { ?>
						-
					<?php } ?>
				</b>
			</div>
			<div style="width: 341px; text-align: center; padding-top: 22px; font-size: 12px !important; line-height: 15px;">
				<span class="verde"><?=t('ranked.total_pontos');?>: </span><?=($player_ranked ? highamount($player_ranked->points) : '-');?><br />
				<span class="verde"><?=t('ranked.total_batalhas');?>: </span><?=($player_ranked ? highamount($player_ranked->wins + $player_ranked->losses + $player_ranked->draws) : '-');?><br /><br />
				<span class="verde"><?=t('ranked.vitorias');?>: </span><?=($player_ranked ? highamount($player_ranked->wins) : '-');?> <br />
				<span class="vermelho"><?=t('ranked.derrotas');?>: </span><?=($player_ranked ? highamount($player_ranked->losses) : '-');?> <br />
				<span><?=t('ranked.empates');?>: </span><?=($player_ranked ? highamount($player_ranked->draws) : '-');?> <br />
			</div>
		</div>
		<div class="h-missoes">
			<div style="width: 341px; text-align: center; padding-top: 12px"><b class="amarelo" style="font-size:13px"><?=t('ranked.resumo');?></b></div>
			<div style="width: 341px; text-align: center; padding-top: 22px; font-size: 12px !important; line-height: 15px;">
				<span class="verde"><?=t('ranked.melhor_rank');?>:</span> <?=($best_rank ? $best_rank->tier()->description()->name : '-'); ?><br />
				<span class="verde"><?=t('ranked.total_batalhas');?>:</span> <?=($best_rank ? highamount($ranked_total->total_wins + $ranked_total->total_losses + $ranked_total->total_draws) : '-');?><br /><br />
				<span class="verde"><?=t('ranked.total_de');?> <?=t('ranked.vitorias');?>:</span> <?=($best_rank ? highamount($ranked_total->total_wins) : '-');?><br />
				<span class="vermelho"><?=t('ranked.total_de');?> <?=t('ranked.derrotas');?>:</span> <?=($best_rank ? highamount($ranked_total->total_losses) : '-');?><br />
				<span><?=t('ranked.total_de');?> <?=t('ranked.empates');?>:</span> <?=($best_rank ? highamount($ranked_total->total_draws) : '-');?><br />
			</div>
		</div>
	</div>

	<?php if ($player_ranked && !$player_ranked->reward && $ranked->finished) { ?>
		<div align="center" id="reward-league">
			<a class="btn btn-sm btn-primary reward" data-league="<?=$ranked->id;?>">
				<?=t('ranked.recompesa_do')?> <?=$player_ranked->tier()->description()->name;?>
			</a>
		</div>
	<?php } ?>
<?php } ?>
