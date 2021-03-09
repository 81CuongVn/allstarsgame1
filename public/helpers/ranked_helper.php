<?php
function ranked_up_points($rank) {
    $ranks  = [
        10  => 1,
        9   => 2,
        8   => 3,
        7   => 4,
        6   => 5,
        5   => 6,
        4   => 7,
        3   => 8,
        2   => 9,
        1   => 10
    ];
    return (500 / 5) * $ranks[$rank];
}

function ranked_down_points($rank) {
    $base = ranked_up_points($rank + 1);
    return $base * 0.95;
}