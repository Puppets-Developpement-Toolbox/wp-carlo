<?php

/**
 * add image size
 * @param $sizes string widthxheight or array of string
 */
function carlo_register_img_size($sizes) {
  $sizes = (array)$sizes;
  foreach($sizes as $size_with_crop) {
    list($size, $crop) = explode(':', "{$size_with_crop}:1");
    list($w, $h) = explode('x', $size);
    add_image_size($size_with_crop, $w, $h, !!intval($crop));
  }
}

carlo_register_img_size([
    '630x394',
    '2040x1024',
    '1287x1287',
    '1072x1287',
    '1600x900',
    '70x70',
    '1050x876',
    '435x363'
]);

if(
  !empty($_SERVER['REQUEST_URI']) &&
  str_starts_with($_SERVER['REQUEST_URI'], '/app/uploads') &&
  !empty($_ENV['SFP_URL'])
) {
  wp_redirect("{$_ENV['SFP_URL']}{$_SERVER['REQUEST_URI']}");
  die();
}
