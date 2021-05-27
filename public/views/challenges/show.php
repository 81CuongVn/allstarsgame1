<?php echo partial('shared/title', array('title' => 'challenges.title', 'place' => 'challenges.title')) ?>
<div class="msg-challenge-on" style="background-image:url(<?php echo image_url('msg/challenges/'.$challenge_active->challenge_id.'.jpg')?>)">
	<div class="msgb" style="position:relative; margin-left: 231px; text-align: left; top: 35px">
		<b>Arena do Ceú - <?php echo $challenge->description()->name?></b>
		<div class="content">
			<div class="challenge_texto">
				<span class="challege_amarelo">Melhor jogador:</span><span class="challege_azul"><img src="<?php echo image_url('icons/crown.png')?>" width="16" style="margin-top:-2px"/> <?php echo $player_best_all->name?> - <?php echo $challenge_best_all->quantity?>º Andar</span>
			</div>
			<div class="challenge_texto">
				<span class="challege_amarelo">Seu maior andar:</span><span class="challege_azul"><?=($challenge_best->quantity ? $challenge_best->quantity . 'º Andar' : '-');?></span>
			</div>
			<div class="challenge_texto">
				<span class="challege_amarelo">Seu andar atual:</span><span class="challege_azul"><?=($challenge_active->quantity ? $challenge_active->quantity . 'º Andar' : '-');?></span>
			</div>
			<div class="challenge_texto">
				<span class="challege_amarelo">Recompensa atual:</span>
			</div>
			<div class="challenge-star">

				<?php
				foreach($rewards as $reward){
				?>
				<div class="challenge-float">
					<div class="andar"><?php echo highamount($reward['quantity']); ?></div>
					<img src="<?php echo image_url(($challenge_active->quantity <= $reward['quantity'] ? 'icons/star2.png' : 'icons/star.png'))?>" class="requirement-popover" data-source="#tooltip-star-<?php echo $reward['quantity']?>" data-title="Premiação do <?php echo $reward['quantity']?>º Andar" data-trigger="hover" data-placement="bottom" />
					<div id="tooltip-star-<?php echo $reward['quantity']?>" class="status-popover-container">
						<div class="status-popover-content">
							<ul>
								<?php if (
									!$reward['exp'] && !$reward['money'] && !$reward['equipments'] &&
									!$reward['pets'] && !$reward['title'] && !$reward['star']
								) { ?>
									<li style="text-align: center;">Nenhuma recompensa</li>
								<?php } ?>
								<?php if ($reward['exp']) { ?>
									<li>Experiência: <span class="branco"><?php echo highamount($reward['exp'])?></span></li>
								<?php } ?>
								<?php if($reward['money']){?>
									<li><?php echo t('currencies.' . $player->character()->anime_id) ?>: <span class="branco"><?php echo highamount($reward['money'])?></span></li>
								<?php }?>
								<?php if($reward['equipments']){?>
									<li>Equipamento: <span class="branco"><?php echo $reward['equipments']?></span></li>
								<?php }?>
								<?php if($reward['pets']){?>
									<li>Mascote: <span class="branco"><?php echo $reward['pets']?></span></li>
								<?php }?>
								<?php if($reward['title']){?>
									<li>Título: <span class="branco"><?php echo $reward['title']?></span></li>
								<?php }?>
								<?php if($reward['star']){?>
									<li>Estrela: <span class="branco"><?php echo $reward['star']?></span></li>
								<?php }?>
							</ul>
						</div>
					</div>
				</div>
				<?php }?>
			</div>
		</div>
	</div>
</div>
<br />
<div>
	<div class="pull-left">
		<?php echo $player->profile_image() ?>
		<div align="center" class="nome-personagem"><?php echo $player->name ?></div>
	</div>
	<div style="float: left; padding-top: 20px">
		<img src="<?php echo image_url('battle/vs2.png')?>" />
	</div>
	<div class="pull-right">
		<?php echo $npc->profile_image() ?>
		<div align="center" class="nome-personagem"><?php echo $npc->name ?></div>
	</div>
	<div class="clearfix"></div>
</div>
<div align="center">
	<a href="javascript:;" id="btn-enter-npc-battle-challenge" data-type="3" class="btn btn-primary btn-lg"><?php echo t('battles.npc.accept') ?></a>
</div>
