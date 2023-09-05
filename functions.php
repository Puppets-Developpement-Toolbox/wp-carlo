<?php

foreach ( glob(__DIR__ . '/inc/*.php' ) as $file ) {
  include_once $file; 
}

$child_inc = get_stylesheet_directory().'/inc/*.php';
foreach ( glob($child_inc) as $file ) {
  include_once $file; 
}
