<?=partial('shared/title', [
	'title' => 'battles.pvp.title',
	'place' => 'battles.pvp.title'
]);?>
<?php if (FW_ENV != 'dev') { ?>
	<!-- AASG - Batalhas -->
	<ins class="adsbygoogle"
		style="display:inline-block;width:728px;height:90px"
		data-ad-client="ca-pub-6665062829379662"
		data-ad-slot="5606300570"></ins>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
	</script><br />
<?php } ?>
<?php if (!$player_tutorial->battle_pvp) { ?>
	<script type="text/javascript">
		$(function () {
			var tour = new Tour({
				backdrop: true,
				page: 16,
				steps: [{
					element: ".msg-container",
					title: "Busque seu Oponente",
					content: "Ao entrar na Fila PVP, você gastará 2 de Stamina e terá que esperar achar algum outro jogador que também estará na Fila. Ao encontrar, você deverá aceitar a batalha e esperar que ele faça o mesmo. Se você cancelar e sair da fila, receberá sua Stamina de volta.",
					placement: "top"
	  			}]
			});

			tour.restart();
			tour.init(true);
			tour.start(true);
		});
	</script>
<?php } ?>
<div class="msg-container">
	<div class="msg_top"></div>
	 <div class="msg_repete">
		<div class="msg" style="background:url(<?=image_url('msg/'. $player->character()->anime_id . '-1.png');?>); background-repeat: no-repeat;">
		</div>
		<div class="msgb" style="position:relative; margin-left: 231px; text-align: left; top: -37px">
			<b><?=t('battles.pvp.m1_title');?></b>
			<div class="content">
				<?=t('battles.pvp.m1_message');?>
				<br /><br />
				<?php if ($player->is_pvp_queued) { ?>
					<a href="javascript:;" id="1x-queue-data" class="btn btn-danger btn-lg"><?=t('battles.sair_fila');?></a>
				<?php } else { ?>
					<a id="battle-pvp-enter-queue" href="javascript:void(0);" class="btn btn-primary btn-lg"><?=t('battles.ir_fila');?></a>
				<?php } ?>
			</div>
		</div>
	</div>
	<div class="msg_bot"></div>
	<div class="msg_bot2"></div>
</div><br />

<div style="clear: left" class="titulo-home3">
	<p>Resumo das últimas batalhas</p>
</div>
<form id="pets-filter-form" method="post">
	<input type="hidden" name="page" value="<?=$page;?>" />

	<table width="725" border="0" cellpadding="0" cellspacing="0" class="table table-striped">
		<?php foreach ($battles as $battle) { ?>
			<?php
			$battle->set_player($battle->player_id);
			$p		= $battle->player();
			$e		= $battle->enemy();
			$winner	= $battle->winner();
			?>
			<tr>
				<td align="center" style="width: 140px;">
				<?php if ($p->banned) { ?>
					<b class="branco" style="letter-spacing: 5px; font-size: 15px; text-transform: uppercase; margin-bottom: 5px; display: block;">
						Banido
					</b>
				<?php } ?>
					<img src="<?=image_url($p->small_image(true));?>" alt="<?=$p->character()->description()->name;?>" width="80" /><br /><br />
					<b class="<?=($winner ? ($battle->won == $p->id ? 'verde' : 'vermelho') : '');?>" style="font-size: 16px; <?=(!$winner || $battle->won != $p->id ? 'text-decoration: line-through;': '');?>">
						<?=$p->name;?>
					</b><br />
					<span style="font-size: 11px; font-weight: none; color: #09F;"><?=($p->headline_id ? $p->headline()->description()->name : '--');?></span>
				</td>
				<td align="center" style="width: 125px;">
					<img src="<?=image_url('battle/vs2.png');?>" width="80" /><br /><br />
					<b class="amarelo" style="text-transform: uppercase;"><?=$battle->type()->description()->name;?></b>
					<?php if ($battle->player_ip == $battle->enemy_ip) { ?><br />
						<b class="laranja" style="text-transform: uppercase;">
							Multi detectado
						</b>
					<?php } ?>
				</td>
				<td align="center" style="width: 140px;">
					<?php if ($e->banned) { ?>
						<b class="branco" style="letter-spacing: 5px; font-size: 15px; text-transform: uppercase; margin-bottom: 5px; display: block;">
							Banido
						</b>
					<?php } ?>
					<img src="<?=image_url($e->small_image(true));?>" alt="<?=$e->character()->description()->name;?>" width="80" /><br /><br />
					<b class="<?=($winner ? ($battle->won == $e->id ? 'verde' : 'vermelho') : '');?>" style="font-size: 16px; <?=(!$winner || $battle->won != $e->id ? 'text-decoration: line-through;': '');?>">
						<?=$e->name;?>
					</b><br />
					<span style="font-size: 11px; font-weight: none; color: #09F;"><?=($e->headline_id ? $e->headline()->description()->name : '--');?></span>
				</td>
				<td align="center">
					<b class="amarelo" style="text-transform: uppercase;">Vencedor</b><br />
					<?php
					if ($winner) {
						echo '<span class="verde">' . $winner->name . '</span>';
					} else {
						echo '<span class="branco" style="text-transform: uppercase;">Empate!</span>';
					}
					?><br /><br />
					<b class="cinza" style="text-transform: uppercase;">Inicio</b><br />
					<?=date('d/m/Y à\s H:i:s', strtotime($battle->created_at));?><br /><br />
					<b class="laranja" style="text-transform: uppercase;">Término</b><br />
					<?=date('d/m/Y à\s H:i:s', strtotime($battle->finished_at));?>
				</td>
			</tr>
		<?php }?>
	</table>
    <?=partial('shared/paginator', [
        'pages'     => $pages,
        'current'   => $page + 1
    ]);?>
</form>
