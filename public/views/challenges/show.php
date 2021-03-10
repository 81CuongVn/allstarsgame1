<?php echo partial('shared/title', array('title' => 'challenges.title', 'place' => 'challenges.title')) ?>
<div class="msg-challenge-on" style="background-image:url(<?php echo image_url('msg/challenges/'.$challenge_active->challenge_id.'.jpg')?>)">
	<div class="msgb" style="position:relative; margin-left: 231px; text-align: left; top: 35px">
		<b>Arena do Ceú - <?php echo $challenge->description()->name?></b>
		<div class="content">
			<div class="challenge_texto">
				<span class="challege_amarelo">Melhor jogador:</span><span class="challege_azul"><img src="<?php echo image_url('icons/crown.png')?>" width="16" style="margin-top:-2px"/> <?php echo $player_best_all->name?> - <?php echo $challenge_best_all->quantity?>º Andar</span>
			</div>
			<div class="challenge_texto">
				<span class="challege_amarelo">Seu maior andar:</span><span class="challege_azul"><?php echo $challenge_best->quantity?>º Andar</span>
			</div>
			<div class="challenge_texto">
				<span class="challege_amarelo">Seu andar atual:</span><span class="challege_azul"><?php echo $challenge_active->quantity?>º Andar</span>
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
								<li>Experiência: <?php echo highamount($reward['exp'])?></li>
								<?php if($reward['money']){?>
									<li><?php echo t('currencies.' . $player->character()->anime_id) ?>: <?php echo highamount($reward['money'])?></li>
								<?php }?>
								<?php if($reward['equipments']){?>	
									<li>Equipamento: <?php echo $reward['equipments']?></li>
								<?php }?>
								<?php if($reward['pets']){?>
									<li>Mascote: <?php echo $reward['pets']?></li>
								<?php }?>
								<?php if($reward['title']){?>	
									<li>Título: <?php echo $reward['title']?></li>
								<?php }?>
								<?php if($reward['star']){?>		
									<li>Estrela: <?php echo $reward['star']?></li>
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