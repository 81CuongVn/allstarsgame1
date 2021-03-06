<?php
function exp_bar_windth($v, $m, $w) {
    $r = @($w / $m) * $v;

    return (int)($r > $w ? $w : $r);
}

function top_exp_bar($player, $user) {
    $width		 = exp_bar_windth($player->exp, $player->level_exp(), 126);
    $width2		 = exp_bar_windth($user->exp, $user->level_exp(), 126);
    $frame_id	 = $player->character()->anime_id;

    $has_talents = $player->has_talents();
    $has_points  = $player->training_points_spent;

    $msg_points  = "";
    $msg_talents = "";

    $check_talents  = FALSE;
    $check_points   = FALSE;

    if($has_points != $user->level){
        $check_points   = TRUE;
        $msg_points     = t('alerts.points', array('link' => make_url('trainings#attributes')));
    }
    if(floor($user->level / 2) != $has_talents && $has_talents < 23){
        $check_talents  = TRUE;
        $msg_talents    = t('alerts.talents', array('link' => make_url('characters#talents')));
    }
    if (!empty($msg_talents) && !empty($msg_points))
        $msg_points .= '<br /><br />';

    if($check_talents || $check_points){
        $alerts = '
			<div style="position: absolute; right: 36px; z-index: 10000; top: 12px;" class="technique-popover" data-source="#alert-user-container-'.$player->id .'" data-title="'.t('alerts.title').'" data-trigger="click" data-placement="bottom"><a href="" class="badge">!</a></div>
			<div id="alert-user-container-'. $player->id .'" class="technique-container">
				<div style="margin: 10px">
					 ' . $msg_points . $msg_talents . '
				</div>
			</div>
			';
    }else{
        $alerts = '';
    }
    $percentUser = floor(($user->exp / $user->level_exp()) * 100);
    $percentPlayer = floor(($player->exp / $player->level_exp()) * 100);
    return '<div class="top-expbar-container">
      <div class="level technique-popover" data-source="#level-container-'.$player->id .'" data-title="Nível do Personagem" data-trigger="click" data-placement="bottom">
        <span>NV</span>
        <div class="number">' . $player->level . '</div>
      </div>
			<div id="level-container-'. $player->id .'" class="technique-container">
					<div style="margin: 10px">'.t('level.player').'</div>
			</div>
			'.$alerts.'
      <div class="level level-user technique-popover" data-source="#level-user-container-'.$player->id .'" data-title="Nível da Conta" data-trigger="click" data-placement="bottom">
        <span>NV</span>
        <div class="number">' . $user->level . '</div>
      </div>
			<div id="level-user-container-'. $player->id .'" class="technique-container">
					<div style="margin: 10px">'.t('level.user').'</div>
			</div>
			<!--
			<div class="light light-player" style="margin-left: ' . (100 + $width) . 'px"></div>
			<div class="light light-user" style="margin-right: ' . (210 - $width2) . 'px"></div>
			-->
			<div class="frame" style="background-image: url(' . image_url('top_exp_bar/frame_' . $frame_id . '.png') . ')"></div>
			<div class="top-progress top-progress-player">
				<div class="empty"></div>
				<div class="fill" style="width: ' . $width . 'px">
				</div>
				<div class="text">' . highamount($player->exp) . '/' . highamount($player->level_exp()) . '</div>
			</div>
			<div class="top-progress top-progress-user">
				<div class="empty"></div>
				<div class="fill" style="width: ' . $width2 . 'px">
				</div>
				<div class="text">' . highamount($user->exp) . '/' . highamount($user->level_exp()) . '</div>
			</div>
			<!--<div class="top-effect anipng" data-animation="' . image_url('top_exp_bar/frames.png') . '" data-animation-width="' . $width . '" data-animation-height="29" data-frames="42"></div>-->
			<div class="graduation">' . t('top.graduation', ['grad' => $player->graduation()->description($player->character()->anime_id)->name]) . '</div>
		</div>';
}

function section_bar($text, $anime = null) {
    $anime	= $anime ? $anime : rand(1, 6);

    return '<div class="barra-secao barra-secao-' . $anime . '"><p>' . $text . '</p></div>';
}

function exp_bar($value, $max, $max_width, $text = null) {
    $width	= exp_bar_windth($value, $max, $max_width);

    if(!$text) {
        $text	= round($value, 1);
    }

    return	'<div class="exp-bar exp-bar-' . $max_width . '" style="width: ' . $max_width . 'px">' .
        '<div class="fill" style="width: ' . $width . 'px"></div>' .
        '<div class="text">' . $text . '</div>' .
        '</div>';
}
function pet_exp_bar($value, $max, $max_width, $text = null) {
    $width	= exp_bar_windth($value, $max, $max_width);

    if(!$text) {
        $text	= $value;
    }

    return	'<div class="pet-exp-bar pet-exp-bar-' . $max_width . '" style="width: ' . $max_width . 'px">' .
        '<div class="fill" style="width: ' . $width . 'px"></div>' .
        '<div class="text">' . $text . '</div>' .
        '</div>';
}