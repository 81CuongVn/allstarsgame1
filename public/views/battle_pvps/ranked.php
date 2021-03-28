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
<?php if ($league) { ?>
	<div class="msg-container-off2">
		<div class="msgb-h2" style="position: relative; margin-left: 24px; text-align: left; top: 36px">
			<div style="padding-left: 10px; float: left;">
				<b>
				<?php
				if (!$league->finished) {
					$daysLeft = ceil((strtotime($league->finish_date) - time()) / 86400);
					echo "Progressão da Liga {$league->league} - Restam {$daysLeft} dia(s)";
				} else {
					echo "Liga {$league->league} Concluída em " . date('d/m/Y H:i:s', strtotime($league->finish_date));
				}
				?>
				</b>
			</div>
			<div style="float: right; position:relative; right: 49px; top: -3px">
				<form id="league-filter-form" method="post">
					<select class="form-control input-sm" name="leagues" id="leagues" style="height: 23px; line-height: 23px; padding: 3px; margin-bottom: -7px;">
						<?php
						foreach ($leagues as $row) {
							echo "<option value=\"{$row->league}\" " . ($row->league == $league->league ? "selected=\"selected\"" : "") . ">Liga {$row->league}</option>";
						}
						?>
					</select>
				</form>	
			</div>
			<div class="content">
				<div>
					<?php for ($rank = 10; $rank >= 0; --$rank) { ?>
						<div style="float: left; padding-left: 5px" class="technique-popover" data-source="#liga-container-<?php echo $rank; ?>" data-title="Rank <?php echo ($rank == 0 ? 'All-Star' : $rank); ?>" data-trigger="click" data-placement="bottom">
							<?php if ($rank == 0) { ?>
								<div style="position: absolute; top: 57px; right: 11px;">
									<img src="<?php echo image_url($player_ranked && $player_ranked->rank <= $rank ? 'ranked/star_on.png' : 'ranked/star_off.png')?>" />
								</div>
								<img src="<?php echo image_url($player_ranked && $player_ranked->rank <= $rank ? 'ranked/bg_on.jpg' : 'ranked/bg_off.jpg')?>" />
							<?php } else { ?>
								<img src="<?php echo image_url($player_ranked && $player_ranked->rank <= $rank ? 'ranked/' . $rank . '_on.jpg' : 'ranked/' . $rank . '_off.jpg')?>" />
							<?php } ?>
						</div>
						<div id="liga-container-<?php echo $rank; ?>" class="technique-container">
							<div style="min-width: 220px;">
								<span class="amarelo" style="font-size:14px"><?php echo t('ranked.promotion');?></span><br /><br />
								<?php if ($rank != 10) { ?>
									<span class="vermelho"><?php echo t('ranked.down');?>:</span> <?php echo highamount($league->down_points($rank)); ?> <?php echo t('ranked.points2');?><br />
								<?php } ?>
								<?php if ($rank != 0) { ?>
									<span class="verde"><?php echo t('ranked.up');?>:</span> <?php echo highamount($league->up_points($rank)); ?> <?php echo t('ranked.points');?><br /><br />
								<?php } else { echo '<br />'; } ?>
								<?php if ($rewards = $league->reward($rank)) { ?>
									<span class="amarelo" style="font-size:14px"><?php echo t('ranked.reward');?></span><br /><br />
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
	<?php if ($best_rank) { ?>
	<div style="width: 730px; height: 185px; position: relative; left: 24px">
		<div class="h-missoes">
			<div style="width: 341px; text-align: center; padding-top: 12px">
				<b class="amarelo" style="font-size:13px">
					<?php if ($player_ranked) { $player_ranked->update(); ?>
						Rank <?php echo ($player_ranked->rank == 0 ? 'All-Star' : $player_ranked->rank)?>
					<?php } else { ?>
						-
					<?php } ?>
				</b>
			</div>
			<div style="width: 341px; text-align: center; padding-top: 22px; font-size: 12px !important; line-height: 15px;">
				<span class="verde"><?php echo t('ranked.total_pontos');?>: </span><?php echo ($player_ranked ? highamount($player_ranked->points()) : '-'); ?><br />
				<span class="verde"><?php echo t('ranked.total_batalhas');?>: </span><?php echo ($player_ranked ? highamount($player_ranked->wins + $player_ranked->losses + $player_ranked->draws) : '-'); ?><br /><br />
				<span class="verde"><?php echo t('ranked.vitorias');?>: </span><?php echo ($player_ranked ? highamount($player_ranked->wins) : '-'); ?> <br />
				<span class="vermelho"><?php echo t('ranked.derrotas');?>: </span><?php echo ($player_ranked ? highamount($player_ranked->losses) : '-'); ?> <br />
				<span><?php echo t('ranked.empates');?>: </span><?php echo ($player_ranked ? highamount($player_ranked->draws) : '-'); ?> <br />
			</div>
		</div>
		<div class="h-missoes">
			<div style="width: 341px; text-align: center; padding-top: 12px"><b class="amarelo" style="font-size:13px"><?php echo t('ranked.resumo');?></b></div>
			<div style="width: 341px; text-align: center; padding-top: 22px; font-size: 12px !important; line-height: 15px;">
				<span class="verde"><?php echo t('ranked.melhor_rank');?>: </span> <?php echo ($best_rank ? ($best_rank->rank == 0 ? "Rank All-Star" : "Rank ". $best_rank->rank) : '-'); ?><br />
				<span class="verde"><?php echo t('ranked.total_batalhas');?>: </span><?php echo ($best_rank ? highamount($ranked_total['total_wins'] + $ranked_total['total_losses'] + $ranked_total['total_draws']) : '-'); ?><br /><br />
				<span class="verde"><?php echo t('ranked.total_de');?> <?php echo t('ranked.vitorias');?>: </span><?php echo ($best_rank ? highamount($ranked_total['total_wins']) : '-'); ?> <br />
				<span class="vermelho"><?php echo t('ranked.total_de');?> <?php echo t('ranked.derrotas');?>: </span><?php echo ($best_rank ? highamount($ranked_total['total_losses']) : '-'); ?><br />
				<span><?php echo t('ranked.total_de');?> <?php echo t('ranked.empates');?>: </span><?php echo ($best_rank ? highamount($ranked_total['total_draws']) : '-'); ?><br />
			</div>
		</div>
	</div>
	<?php } ?>
	<?php if ($player_ranked && !$player_ranked->reward && $league->finished) { ?>
		<div align="center" id="reward-league">
			<a class="btn btn-sm btn-primary reward" data-league="<?php echo $league->league; ?>">
				<?php echo t('ranked.recompesa_do')?> Rank <?php echo ($player_ranked->rank == 0 ? 'All-Star' : $player_ranked->rank)?>
			</a>
		</div>
	<?php } ?>
<?php } ?>