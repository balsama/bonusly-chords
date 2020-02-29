<?php
$target = 542;
$count = 0;
$colors = [];
while ($count < $target) {
    $colors[] = random_color();
    $count++;
}
$colors = implode(', ', $colors);

echo $colors . "\n";

function random_color_part() {
    return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
}

function random_color() {
    return '"#' . random_color_part() . random_color_part() . random_color_part() . '"';
}