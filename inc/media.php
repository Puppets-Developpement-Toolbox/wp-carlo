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

if(
  !empty($_SERVER['REQUEST_URI']) && 
  str_starts_with($_SERVER['REQUEST_URI'], '/app/uploads') && 
  !empty($_ENV['SFP_URL'])
) {
  wp_redirect("{$_ENV['SFP_URL']}{$_SERVER['REQUEST_URI']}");
  die();
}

