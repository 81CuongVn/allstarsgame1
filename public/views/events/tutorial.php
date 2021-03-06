<?php echo partial('shared/title', array('title' => 'menus.tutorial', 'place' => 'menus.tutorial')) ?>
<?php
	echo partial('shared/info', [
		'id'		=> 4,
		'title'		=> 'menus.tutorial',
		'message'	=> 'Visite cada página do jogo para aprender mais sobre ela, porém você poderá visitar o Guia do Jogo a hora que quiser para informações mais detalhadas!'
	]);
?>
<br />
<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>"><p>Complete todas as etapas do Tutorial</p></div>

<div class="h-missoes2">
	<div class="tutorial" style="<?php echo $player_tutorial->status ? 'background-color: #12304b;' : 'background-color: #051727;'?>">
		<div class="image">
			<img src="<?php echo $player_tutorial->status ? image_url('icons/yes.png') : image_url('icons/no.png')?>" />
		</div>
		<div class="texto">	
			<a href="<?=make_url('characters/status');?>">Status</a>
		</div>
	</div>
	<div class="tutorial" style="<?php echo $player_tutorial->habilidades ? 'background-color: #12304b;' : 'background-color: #051727;'?>">
		<div class="image">
			<img src="<?php echo $player_tutorial->habilidades ? image_url('icons/yes.png') : image_url('icons/no.png')?>" />
		</div>
		<div class="texto">	
			<a href="<?=make_url('techniques/abilities_and_specialities');?>">Habilidades</a>
		</div>
	</div>
	<div class="tutorial" style="<?php echo $player_tutorial->talents ? 'background-color: #12304b;' : 'background-color: #051727;'?>">
		<div class="image">
			<img src="<?php echo $player_tutorial->talents ? image_url('icons/yes.png') : image_url('icons/no.png')?>" />
		</div>
		<div class="texto">	
			<a href="<?=make_url('characters/talents');?>">Talentos</a>
		</div>
	</div>
	<div class="tutorial" style="<?php echo $player_tutorial->pets ? 'background-color: #12304b;' : 'background-color: #051727;'?>">
		<div class="image">
			<img src="<?php echo $player_tutorial->pets ? image_url('icons/yes.png') : image_url('icons/no.png')?>" />
		</div>
		<div class="texto">	
			<a href="<?=make_url('characters/pets');?>">Mascotes</a>
		</div>
	</div>
	<div class="tutorial" style="<?php echo $player_tutorial->equips ? 'background-color: #12304b;' : 'background-color: #051727;'?>">
		<div class="image">
			<img src="<?php echo $player_tutorial->equips ? image_url('icons/yes.png') : image_url('icons/no.png')?>" />
		</div>
		<div class="texto">	
			<a href="<?=make_url('equipments');?>">Equipamentos</a>
		</div>
	</div>
	<div class="tutorial" style="<?php echo $player_tutorial->golpes ? 'background-color: #12304b;' : 'background-color: #051727;'?>">
		<div class="image">
			<img src="<?php echo $player_tutorial->golpes ? image_url('icons/yes.png') : image_url('icons/no.png')?>" />
		</div>
		<div class="texto">	
			<a href="<?=make_url('techniques/index');?>">Golpes</a>
		</div>
	</div>
	<div class="tutorial" style="<?php echo $player_tutorial->aprimoramentos ? 'background-color: #12304b;' : 'background-color: #051727;'?>">
		<div class="image">
			<img src="<?php echo $player_tutorial->aprimoramentos ? image_url('icons/yes.png') : image_url('icons/no.png')?>" />
		</div>
		<div class="texto">	
			<a href="<?=make_url('techniques/enchant');?>">Aprimoramentos</a>
		</div>
	</div>
	<div class="tutorial" style="<?php echo $player_tutorial->escola ? 'background-color: #12304b;' : 'background-color: #051727;'?>">
		<div class="image">
			<img src="<?php echo $player_tutorial->escola ? image_url('icons/yes.png') : image_url('icons/no.png')?>" />
		</div>
		<div class="texto">	
			<a href="<?=make_url('techniques/grimoire');?>">Grimório Proibido</a>
		</div>
	</div>
	<div class="tutorial" style="<?php echo $player_tutorial->treinamento ? 'background-color: #12304b;' : 'background-color: #051727;'?>">
		<div class="image">
			<img src="<?php echo $player_tutorial->treinamento ? image_url('icons/yes.png') : image_url('icons/no.png')?>" />
		</div>
		<div class="texto">	
			<a href="<?=make_url('trainings/attributes');?>">Treinamento</a>
		</div>
	</div>
	<div class="tutorial" style="<?php echo $player_tutorial->mercado ? 'background-color: #12304b;' : 'background-color: #051727;'?>">
		<div class="image">
			<img src="<?php echo $player_tutorial->mercado ? image_url('icons/yes.png') : image_url('icons/no.png')?>" />
		</div>
		<div class="texto">	
			<a href="<?=make_url('characters/fragments');?>">Fragmento das Almas</a>
		</div>
	</div>
	<div class="tutorial" style="<?php echo $player_tutorial->missoes_tempo ? 'background-color: #12304b;' : 'background-color: #051727;'?>">
		<div class="image">
			<img src="<?php echo $player_tutorial->missoes_tempo ? image_url('icons/yes.png') : image_url('icons/no.png')?>" />
		</div>
		<div class="texto">	
			<a href="<?=make_url('quests/time');?>">Missões de Tempo</a>
		</div>
	</div>
	<div class="tutorial" style="<?php echo $player_tutorial->missoes_diarias ? 'background-color: #12304b;' : 'background-color: #051727;'?>">
		<div class="image">
			<img src="<?php echo $player_tutorial->missoes_diarias ? image_url('icons/yes.png') : image_url('icons/no.png')?>" />
		</div>
		<div class="texto">	
			<a href="<?=make_url('quests/daily');?>">Missões Diárias</a> 
		</div>
	</div>
	<div class="tutorial" style="<?php echo $player_tutorial->missoes_seguidores ? 'background-color: #12304b;' : 'background-color: #051727;'?>">
		<div class="image">
			<img src="<?php echo $player_tutorial->missoes_seguidores ? image_url('icons/yes.png') : image_url('icons/no.png')?>" />
		</div>
		<div class="texto">	
			<a href="<?=make_url('quests/pet');?>">Missões de Mascotes</a>
		</div>
	</div>
	<div class="tutorial" style="<?php echo $player_tutorial->missoes_pvp ? 'background-color: #12304b;' : 'background-color: #051727;'?>">
		<div class="image">
			<img src="<?php echo $player_tutorial->missoes_pvp ? image_url('icons/yes.png') : image_url('icons/no.png')?>" />
		</div>
		<div class="texto">	
			<a href="<?=make_url('quests/pvp');?>">Missões PVP</a>
		</div>
	</div>
	<div class="tutorial" style="<?php echo $player_tutorial->missoes_conta ? 'background-color: #12304b;' : 'background-color: #051727;'?>">
		<div class="image">
			<img src="<?php echo $player_tutorial->missoes_conta ? image_url('icons/yes.png') : image_url('icons/no.png')?>" />
		</div>
		<div class="texto">	
			<a href="<?=make_url('quests/account');?>">Missões de Conta</a>
		</div>
	</div>
	<div class="tutorial" style="<?php echo $player_tutorial->battle_npc ? 'background-color: #12304b;' : 'background-color: #051727;'?>">
		<div class="image">
			<img src="<?php echo $player_tutorial->battle_npc ? image_url('icons/yes.png') : image_url('icons/no.png')?>" />
		</div>
		<div class="texto">	
			<a href="<?=make_url('battle_npcs');?>">Batalha NPC</a>
		</div>
	</div>
	<div class="tutorial" style="<?php echo $player_tutorial->battle_pvp ? 'background-color: #12304b;' : 'background-color: #051727;'?>">
		<div class="image">
			<img src="<?php echo $player_tutorial->battle_pvp ? image_url('icons/yes.png') : image_url('icons/no.png')?>" />
		</div>
		<div class="texto">	
			<a href="<?=make_url('battle_pvps');?>">Batalha PVP</a>
		</div>
	</div>
	<div class="tutorial" style="<?php echo $player_tutorial->fidelity ? 'background-color: #12304b;' : 'background-color: #051727;'?>">
		<div class="image">
			<img src="<?php echo $player_tutorial->fidelity ? image_url('icons/yes.png') : image_url('icons/no.png')?>" />
		</div>
		<div class="texto">	
			<a href="<?=make_url('events/fidelity');?>">Fidelidade</a>
		</div>
	</div>
	<div class="tutorial" style="<?php echo $player_tutorial->battle_village ? 'background-color: #12304b;' : 'background-color: #051727;'?>">
		<div class="image">
			<img src="<?php echo $player_tutorial->battle_village ? image_url('icons/yes.png') : image_url('icons/no.png')?>" />
		</div>
		<div class="texto">	
			<a href="<?=make_url('events/anime');?>">Batalha de Vila</a>
		</div>
	</div>
	<div class="tutorial" style="<?php echo $player_tutorial->bijuus ? 'background-color: #12304b;' : 'background-color: #051727;'?>">
		<div class="image">
			<img src="<?php echo $player_tutorial->bijuus ? image_url('icons/yes.png') : image_url('icons/no.png')?>" />
		</div>
		<div class="texto">	
			<a href="<?=make_url('events/wanted');?>">Procurados</a>
		</div>
	</div>
	<div class="tutorial" style="<?php echo $player_tutorial->objectives ? 'background-color: #12304b;' : 'background-color: #051727;'?>">
		<div class="image">
			<img src="<?php echo $player_tutorial->objectives ? image_url('icons/yes.png') : image_url('icons/no.png')?>" />
		</div>
		<div class="texto">
			<a href="<?=make_url('events/objectives');?>">Objetivos</a>
		</div>
	</div>
</div>
<br /><br />
<div class="barra-secao barra-secao-<?php echo $player->character()->anime_id ?>"><p>Recompensas do Tutorial</p></div>
<div class="new2">
	<div class="conteudo" style="margin: auto; text-align: center">
		<div class="ev-req2 requirement-popover" data-source="#tooltip-req-exp" data-trigger="hover" data-placement="bottom">
			<img src="<?php echo image_url('events/exp.png')?>"/>
			<div id="tooltip-req-exp" class="status-popover-container">
				<div class="status-popover-content" style="margin: 10px">
					<?php echo t('event.e15');?>
				</div>
			</div>	
		</div>
		<div class="ev-req2 requirement-popover" data-source="#tooltip-req-gold" data-trigger="hover" data-placement="bottom">
			<img src="<?php echo image_url('events/gold.png')?>"/>
			<div id="tooltip-req-gold" class="status-popover-container">
				<div class="status-popover-content" style="margin: 10px">
					<?php echo t('event.e11');?>
				</div>
			</div>	
		</div>
		<div class="ev-req2 requirement-popover" data-source="#tooltip-req-drop"  data-trigger="hover" data-placement="bottom">
			<img src="<?php echo image_url('events/drop.png')?>"/>
			<div id="tooltip-req-drop" class="status-popover-container">
				<div class="status-popover-content" style="margin: 10px">
					<?php echo t('event.e12');?>
				</div>
			</div>	
		</div>
		<div class="ev-req2 requirement-popover" data-source="#tooltip-req-ramen" data-trigger="hover" data-placement="bottom">
			<img src="<?php echo image_url('events/ramen.png')?>"/>
			<div id="tooltip-req-ramen" class="status-popover-container">
				<div class="status-popover-content" style="margin: 10px">
					<?php echo t('event.e13');?>
				</div>
			</div>	
		</div>	
		<div class="ev-req2 requirement-popover" data-source="#tooltip-req-pets" data-trigger="hover" data-placement="bottom">
			<img src="<?php echo image_url('events/pets.png')?>"/>
			<div id="tooltip-req-pets" class="status-popover-container">
				<div class="status-popover-content" style="margin: 10px">
					<?php echo t('event.e14');?>
				</div>
			</div>	
		</div>
		<div class="break"></div>		
	</div>
</div>	
	