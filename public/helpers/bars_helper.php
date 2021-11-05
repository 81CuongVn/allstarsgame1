<?php
function exp_bar_windth($v, $m, $w) {
	if (!$m) {
		return 0;
	}

	$r = @(($w / $m) * $v);

    return $r > $w ? $w : $r;
}

function top_exp_bar($player, $user) {
    $width  		 = exp_bar_windth($player->exp, $player->level_exp(), 126);
    $width2		     = exp_bar_windth($user->exp, $user->level_exp(), 126);
    $frame_id   	 = $player->character()->anime_id;

    $has_talents    = $player->has_talents();
    $has_points     = $player->available_training_points();

    $total_talents  = Item::find('item_type_id=6 group by mana_cost');

    $msg_points      = "";
    $msg_talents     = "";

    $check_talents  = false;
    $check_points   = false;

    if ($has_points) {
        $check_points   = true;
        $msg_points     = t('alerts.points', array('link' => make_url('trainings#attributes')));
    }
    if (floor($user->level / 2) != $has_talents && $has_talents < sizeof($total_talents)) {
        $check_talents  = true;
        $msg_talents    = t('alerts.talents', array('link' => make_url('characters#talents')));
    }
    if (!empty($msg_talents) && !empty($msg_points)) {
        $msg_points .= '<br /><br />';
    }

    $alerts = '';
    $message = $msg_points . $msg_talents;
    if ($check_talents || $check_points) {
        $alerts = '
			<div style="position: absolute; right: 10px; z-index: 10000; top: 12px;" class="technique-popover" data-source="#alert-user-container-'.$player->id .'" data-title="'.t('alerts.title').'" data-trigger="click" data-placement="bottom">
                <a href="javascript:void(0);" class="badge pulsate_icons">
					<i class="fa fa-exclamation fa-fw"></i>
				</a>
            </div>
			<div id="alert-user-container-'. $player->id .'" class="technique-container">
                <div class="status-popover-content" style="width: 170px;">
				    ' . $message . '
                </div>
			</div>';
    }

    return '<div class="top-expbar-container">
        <div style="cursor: help;" class="level level-player technique-popover" data-source="#level-container-'.$player->id .'" data-title="Nível do Personagem" data-trigger="click" data-placement="bottom">
            <span>NV</span>
            <div class="number">' . $player->level . '</div>
        </div>
        <div id="level-container-'. $player->id .'" class="technique-container">
            <div class="status-popover-content" style="width: 200px;">
                ' . t('level.player') . '
            </div>
        </div>
        '.$alerts.'
        <div style="cursor: help;" class="level level-user technique-popover" data-source="#level-user-container-'.$player->id .'" data-title="Nível da Conta" data-trigger="click" data-placement="bottom">
            <span>NV</span>
            <div class="number">' . $user->level . '</div>
        </div>
        <div id="level-user-container-'. $player->id .'" class="technique-container">
            <div class="status-popover-content" style="width: 200px;">
                ' . t('level.user') . '
            </div>
        </div>
        <div class="frame" style="background-image: url(' . image_url('top_exp_bar/frame_' . $frame_id . '.png') . ')"></div>
        <div class="top-progress top-progress-player">
            <div class="empty"></div>
            <div class="fill" style="width: ' . $width . 'px"></div>
            <div class="text">' . highamount($player->exp) . '/' . highamount($player->level_exp()) . '</div>
        </div>
        <div class="top-progress top-progress-user">
            <div class="empty"></div>
            <div class="fill" style="width: ' . $width2 . 'px"></div>
            <div class="text">' . highamount($user->exp) . '/' . highamount($user->level_exp()) . '</div>
        </div>
        <div class="graduation">' . $player->graduation()->description($player->character()->anime_id)->name . '</div>
    </div>';
}

function section_bar($text, $anime = null) {
    $anime	= $anime ? $anime : ((rand(1, 100) / 2) == 0 ? rand(1, 40) : 999);

    return '<div class="barra-secao barra-secao-' . $anime . '"><p>' . $text . '</p></div>';
}

function exp_bar($value, $max, $max_width, $text = null) {
    $width              = exp_bar_windth($value, $max, $max_width);
    if (!$text)  $text  = round($value, 2);

    return	'<div class="exp-bar exp-bar-' . $max_width . '" style="width: ' . $max_width . 'px">' .
        '<div class="fill" style="width: ' . $width . 'px"></div>' .
        '<div class="text">' . $text . '</div>' .
    '</div>';
}
function pet_exp_bar($value, $max, $max_width, $text = null) {
    $width              = exp_bar_windth($value, $max, $max_width);
    if (!$text) $text   = $value;

    return	'<div class="pet-exp-bar pet-exp-bar-' . $max_width . '" style="width: ' . $max_width . 'px">' .
        '<div class="fill" style="width: ' . $width . 'px"></div>' .
        '<div class="text">' . $text . '</div>' .
    '</div>';
}
