<?php
function reputation_level($level) {
    return pow($level, 2);
}
function modifier_level($winner_lvl, $looser_lvl) {
    $diff = ($winner_lvl - $looser_lvl);
    if ($diff > 10) {
        $modifier = 0;
    } else {
        $modifier = 1 - ($diff / 100);
    }

    return $modifier;
}
function earned_reputation($winner_lvl, $looser_lvl) {
    $reputation         =   reputation_level($looser_lvl);
    $reputation         *=  modifier_level($winner_lvl, $looser_lvl);

    return floor($reputation);
}