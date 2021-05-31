<?php echo partial('shared/title', array('title' => 'menus.friend_list', 'place' => 'menus.friend_list')) ?>
<?php
	echo partial('shared/info', [
		'id'		=> 1,
		'title'		=> 'friends.title',
		'message'	=> t('friends.description')
	]);
?>
<br />
<?php
	if($friends){

?>
    <table width="725" border="0" cellpadding="0" cellspacing="0" id="friend-list-player">
        <?php $counter = 0; ?>
        <?php foreach($friends as $p):
                $color	= $counter++ % 2 ? '091e30' : '173148';
        ?>
			<?php
				  if(!$p){
					continue;
				  }
			?>
            <tr bgcolor="<?php echo $color ?>">
                <td width="150" align="center"><?php echo $p->character_theme()->first_image()->small_image() ?></td>
                <td width="150" align="center">
                    <?php if (is_player_online($p->id)): ?>
                        <img src="<?php echo image_url("on.png" ) ?>"/>
                    <?php else: ?>
                        <img src="<?php echo image_url("off.png" ) ?>"/>
                    <?php endif ?>
                    <span style="font-size:14px" class="amarelo"><?php echo $p->name ?></span><br /><span class="azul_claro">Nível <?php echo $p->level ?></span>
                </td>
                <td width="200" align="center">
					<div class="technique-popover" data-source="#gift-container-1-<?php echo $p->id ?>" data-title="<?php echo t('friends.f1');?> <?php echo t('currencies.' . $player->character()->anime_id)?>" data-trigger="click" data-placement="bottom" style="display:inline-block"><img src="<?php echo image_url("icons/currency.png" ) ?>" /></div>
					<div id="gift-container-1-<?php echo $p->id ?>" class="technique-container">
						<div align="center" style="margin:10px">
							<?php echo t('friends.f3');?><br /> <?php echo t('friends.f4');?> <span class="verde">2000 <?php echo t('currencies.' . $player->character()->anime_id)?></span><br /><br />
							<?php if($user->level > 4){?>
								<?php if(sizeof($player->limit_by_day($player->id)) < 1){?>
									<a data-gift="1" data-player="<?php echo $p->id ?>" class="btn btn-sm btn-primary gift"><?php echo t('friends.f2');?> 2000 <?php echo t('currencies.' . $player->character()->anime_id)?></a>
								<?php }else{?>
									<a class="btn btn-sm btn-danger"><?php echo t('friends.f26');?></a>
								<?php }?>
							<?php }else{?>
								<span class="laranja"><?php echo t('friends.f21');?> 4</span>
							<?php }?>
						</div>
					</div>
					<div class="technique-popover" data-source="#gift-container-2-<?php echo $p->id ?>" data-title="<?php echo t('friends.f22');?>" data-trigger="click" data-placement="bottom" style="display:inline-block"><img src="<?php echo image_url("icons/queue-on.png" ) ?>" /></div>
					<div id="gift-container-2-<?php echo $p->id ?>" class="technique-container">
						<div align="center" style="margin:10px">
							<?php echo t('friends.f3');?><br /> <?php echo t('friends.f5');?> <span class="verde"><?php echo t('friends.f6');?></span><br /><br />
							<?php if($user->level > 9){?>
								<?php if(sizeof($player->limit_by_day($player->id)) < 1){?>
									<a data-gift="2" data-player="<?php echo $p->id ?>" class="btn btn-sm btn-primary gift"><?php echo t('friends.f7');?></a>
								<?php }else{?>
									<a class="btn btn-sm btn-danger"><?php echo t('friends.f26');?></a>
								<?php }?>
							<?php }else{?>
								<span class="laranja"><?php echo t('friends.f21');?> 9</span>
							<?php }?>
						</div>
					</div>
					<div class="technique-popover" data-source="#gift-container-3-<?php echo $p->id ?>" data-title="<?php echo t('friends.f23');?>" data-trigger="click" data-placement="bottom" style="display:inline-block"><img src="<?php echo image_url("icons/vip-on.png" ) ?>" /></div>
					<div id="gift-container-3-<?php echo $p->id ?>" class="technique-container">
						<div align="center" style="margin:10px">
							<?php echo t('friends.f3');?><br /> <?php echo t('friends.f5');?> <span class="verde"><?php echo t('friends.f8');?></span><br /><br />
							<?php if($user->level > 19){?>
								<?php if(sizeof($player->limit_by_day($player->id)) < 1){?>
									<a data-gift="3" data-player="<?php echo $p->id ?>" class="btn btn-sm btn-primary gift"><?php echo t('friends.f10');?></a>
								<?php }else{?>
									<a class="btn btn-sm btn-danger"><?php echo t('friends.f26');?></a>
								<?php }?>
							<?php }else{?>
								<span class="laranja"><?php echo t('friends.f21');?> 19</span>
							<?php }?>
						</div>
					</div>
					<div class="technique-popover" data-source="#gift-container-4-<?php echo $p->id ?>" data-title="<?php echo t('friends.f24');?>" data-trigger="click" data-placement="bottom" style="display:inline-block"><img src="<?php echo image_url("icons/theme.png" ) ?>" /></div>
					<div id="gift-container-4-<?php echo $p->id ?>" class="technique-container">
						<div align="center" style="margin:10px">
							<?php echo t('friends.f3');?><br /> <?php echo t('friends.f5');?> <span class="verde"><?php echo t('friends.f9');?></span><br /><br />
							<?php if($user->level > 29){?>
								<?php if(sizeof($player->limit_by_day($player->id)) < 1){?>
									<a data-gift="4"  data-player="<?php echo $p->id ?>" class="btn btn-sm btn-primary gift"><?php echo t('friends.f10');?></a>
								<?php }else{?>
									<a class="btn btn-sm btn-danger"><?php echo t('friends.f26');?></a>
								<?php }?>
							<?php }else{?>
								<span class="laranja"><?php echo t('friends.f21');?> 29</span>
							<?php }?>
						</div>
					</div>
					<div class="technique-popover" data-source="#gift-container-5-<?php echo $p->id ?>" data-title="<?php echo t('friends.f25');?>" data-trigger="click" data-placement="bottom" style="display:inline-block"><img src="<?php echo image_url("icons/pet.png" ) ?>" /></div>
					<div id="gift-container-5-<?php echo $p->id ?>" class="technique-container">
						<div align="center" style="margin:10px">
							<?php echo t('friends.f3');?><br /> <?php echo t('friends.f5');?> <span class="verde"><?php echo t('friends.f12');?></span><br /><br />
							<?php if($user->level > 39){?>
								<?php if(sizeof($player->limit_by_day($player->id)) < 1){?>
									<a data-gift="5" data-player="<?php echo $p->id ?>" class="btn btn-sm btn-primary gift"><?php echo t('friends.f11');?></a>
								<?php }else{?>
									<a class="btn btn-sm btn-danger"><?php echo t('friends.f26');?></a>
								<?php }?>
							<?php }else{?>
								<span class="laranja"><?php echo t('friends.f21');?> 39</span>
							<?php }?>
						</div>
					</div>
				</td>
                <td width="200" align="center">
                	<a class="btn btn-sm btn-primary current-player" data-url="<?php echo make_url('friend_lists#list_status') ?>" data-player_id="<?php echo $p->id?>"><?php echo t('friends.f13');?></a>
                    <a class="btn btn-sm btn-danger kick" data-id="<?php echo $p->id?>"><?php echo t('friends.f14');?></a>
                </td>
            </tr>
            <tr height="4"></tr>
        <?php endforeach ?>
    </table>
<?php
	}else{
?>
<div align="center" style="padding-top: 10px"><b class="laranja" style="font-size:14px;"><?php echo t('friends.nothing3', array('link' => make_url('friend_lists#search'))) ?></b></div>
<?php
	}
?>
