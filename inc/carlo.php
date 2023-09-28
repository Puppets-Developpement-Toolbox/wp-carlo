<?php

use Symfony\Component\Yaml\Yaml;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// une pile stockant les variables locals aux templates ouverts
global $CARLO_ARGS;
$CARLO_ARGS = [];
// une pile qui permet de détecter le niveau de profondeur du template 
// en cours pour charger des données spécifiques au contexte
global $CARLO_TPL_PATH;
$CARLO_TPL_PATH = [];
// une pile stockant les variables contextuelles ajoutées
// par un parent et accessible à dans les enfants
global $CARLO_CONTEXT;
$CARLO_CONTEXT = [];

/**
 * load structure definition from structure.yml 
 */
function carlo_structure($type = null, $name = null) {
  static $structure;
  $result = $structure;

  if(!isset($structure)) {
    $child_theme_structure = get_stylesheet_directory().'/structure.yml';
    $carlo_base_structure = get_template_directory().'/structure.yml';
    $structure_yaml = file_get_contents($carlo_base_structure);
    if($carlo_base_structure !== $child_theme_structure) {
      $structure_yaml .= "\n" . file_get_contents($child_theme_structure);
    }
    $structure = Yaml::parse($structure_yaml);
    $result = $structure;
  }
  if(null !== $type) {
    $result = $result[$type];
    if(null !== $name) {
      $result = $result[$name];
    }
  }
  return $result;
}

/**
 * load and return the template and inject args
 */
function carlo_render($tpl, array $args = []) {
  global $CARLO_ARGS, $CARLO_TPL_PATH, $CARLO_CONTEXT;
  list($type, $name) = explode('/', $tpl.'/');

  if(empty($name)) {
    if(str_starts_with($type, 'type_')) {
      $name = str_replace('type_', '', $type);
      $type = 'types';
    } else {
      $name = $type;
      $type = 'templates';
    }
    if($name !== 'error') {
      $args = array_merge(carlo_structure($type, $name), $args);
      if($type === 'types') {
        // types only have one template
        $args = $args['template'];
      }
    }
  }
  
  $CARLO_TPL_PATH[] = $name;
  $CARLO_CONTEXT[] = [];

  $defaultArgs = [];
  
  $CARLO_ARGS[] = array_merge($defaultArgs, $args);

  carlo_load_file("templates/{$tpl}.php");

  array_pop($CARLO_ARGS);
  array_pop($CARLO_TPL_PATH);
  array_pop($CARLO_CONTEXT);
}

/**
 * return the value of key from local args or context
 */
function carlo_get($key = null) {
  global $CARLO_ARGS;
  $tpl_args = end($CARLO_ARGS);

  if($key === null) return $tpl_args;

  $values = carlo_get_value($key, $tpl_args);

  if($values === '') {
    $values = carlo_context($key);
  }

  return $values;
}

/**
 * load data for the current template
 */
function carlo_load_data($structure) {
  static $data = [];
  if(!isset($data)) {
    // $data = Yaml::parseFile(__DIR__.'/data.yml');
  }

  return array_map(function($fieldname) use($data, $structure) {
    global $CARLO_TPL_PATH;

    $field = $structure[$fieldname];
    if(is_array($field) && !isset($field['type'])) {
      return carlo_load_data($field);
    }
     
    $type = is_string($field) ? $field : $field['type'];
    if ($type =="repeater") {
      return array_fill(0, $field['times']?? $field['min']+3 , carlo_load_data($field['repeat']) ); 
    }

    $try = $CARLO_TPL_PATH  ;
    do {
      $prefix = implode('--', $try);
      if($prefix) $prefix .= '--';
      if(isset($data["{$prefix}{$fieldname}"])) return $data["{$prefix}{$fieldname}"];
      if(isset($data["{$prefix}{$type}"])) return $data["{$prefix}{$type}"];
    }while(array_shift($try));
    $context = $CARLO_TPL_PATH;
    $context[] = $fieldname;
    $fieldpath = implode(' > ', $context);
    throw new Exception("Impossible de trouver du contenu pour le champ {$fieldpath} de type {$type}");
  }, array_combine(array_keys($structure), array_keys($structure)));
}

/**
 * include the given file
 */
function carlo_load_file($path) {
  $abspath = locate_template($path);
  // par sécurité on interdit de charger un fichier hors du projet
  if(empty($abspath)) {
    throw new Exception("Le fichier {$path} n'est pas présent");
  }
  if(WP_DEBUG) echo "<!-- {$abspath} -->\n";
  include $abspath;
  if(WP_DEBUG) echo "<!-- {$abspath} -->\n";

}


/**
 * add a contextual entry
 */
function carlo_context_add($key, $value) {
  global $CARLO_CONTEXT;
  end($CARLO_CONTEXT);
  $contextLastKey = key($CARLO_CONTEXT);
  $CARLO_CONTEXT[$contextLastKey][$key] = $value;
}

/**
 * return the value of key fron current context
 */
function carlo_context($key) {
  global $CARLO_CONTEXT;
  $values = call_user_func_array('array_merge', array_values($CARLO_CONTEXT));
  return carlo_get_value($key, $values);
}

function carlo_get_value($key, $values) {
  $parts = explode('.', $key);
  while(($values = $values[array_shift($parts)] ?? '') && count($parts)) {}
  return $values;
}


/**
 * returns html image
 * 
 * exemple :
 * - carlo_img(1) => return img tag for img with id 1 in its original format
 * - carlo_img(1, '70x70') => return same img in 70x70 format
 * - carlo_img(1, ['70x70', '(min-width: 1024px)' => '1600x900']) => return same image with an other dimension for big screen
 * - carlo_img(1, ['70x70', '(min-width: 1024px)' => '1600x900'], 2, [(min-width: 2024px)' => '1600x900']) => return same image and image with id 2 for really big screen
 * - carlo_img(1, ['class' => 'mr-16']) => in any case, if the last argument is an array with class key this is added as img attributes
 */
function carlo_img($id){
  $source_sizes = [];
  $args = func_get_args();

  // remove first arg, it's the id
  array_shift($args);

  $imgAttrs = [];
  $lastArg = end($args);
  if(is_array($lastArg) && array_key_exists('class', $lastArg)) {
    // last arg is img attrs
    $imgAttrs = $lastArg;
    array_pop($args);
  }

  $dimensions = array_shift($args);
  // default size => full size
  if(is_null($dimensions)) {
    $dimensions = 'full';
  }

  if(is_array($dimensions)) {
    $default = $dimensions[0];
    unset($dimensions[0]);
    $source_sizes = $dimensions;
    $dimensions = $default;
  }

  $img = wp_get_attachment_image($id, $dimensions, false, [
    'size' => false,
    ...$imgAttrs
  ]);

  if(!$img) return '';
  if(empty($source_sizes) && empty($args)) return $img;

  $source_html = function($id, array $source_sizes) {
    // dump($id, $source_sizes);
    return implode("\n", array_map(function($media, $size) use($id) {
      return '<source media="'.$media.'" srcset="'.wp_get_attachment_image_src($id, $size)[0].'">';
    }, array_keys($source_sizes), $source_sizes));
  };

  $sources = $source_html($id, $source_sizes);

  for($i = 0; $i < count($args); $i = $i + 2) {
    list($id, $source_sizes) = array_slice($args, $i , 2);
    $sources .= "\n" . $source_html($id, (array)$source_sizes);
  }

  return <<<HTML
  <picture>
    {$sources}
    {$img}
  </picture>
  HTML;
}

/**
 * charge le menu qui va bien
 */
function carlo_menu($menu) {
  add_filter('nav_menu_link_attributes', '_carlo_filter_nav_add_id', 10, 3);
  add_filter('wp_nav_menu', '_carlo_filter_nav', 10, 2);
  wp_nav_menu([
    'theme_location' => $menu,
  ]);
  remove_filter('wp_nav_menu', '_carlo_filter_nav', 10);
  remove_filter('nav_menu_link_attributes', '_carlo_filter_nav_add_id', 10);
}


function _carlo_filter_nav_add_id($atts, $item, $args) {
  $atts['data-object'] = $item->object_id;
  return $atts;
}

function _carlo_filter_nav($nav_menu, $args) {
  $doc = new DOMDocument();
  $charset = get_bloginfo('charset');
  $doc->loadHTML(<<<HTML
    <!DOCTYPE html>
    <html>
    <head><meta charset="{$charset}" /></head>
      <body>{$nav_menu}</body>
    </html>
    HTML
  );
  $items = _carlo_nav_extract_elements($doc);

  carlo_render("menus/{$args->theme_location}", [
    'items' => $items
  ]);
  return false;
}

function _carlo_nav_extract_elements(DomNode $node) {
  $uls = $node->getElementsByTagName('ul');
  if(count($uls) === 0) return [];
  $items = array_map(function(DomNode $node) {
    if($node->nodeName !== 'li') return;
    $classes = $node->attributes->getNamedItem('class')->value;
    return [
      'current' => str_contains($classes, 'current-menu-item'),
      'name' => $node->firstChild->textContent,
      'href' => $node->firstChild->attributes->getNamedItem('href')->nodeValue,
      'object_id' => $node->firstChild->attributes->getNamedItem('data-object')->nodeValue,
      'children' => _carlo_nav_extract_elements($node)
    ];
  }, iterator_to_array($uls[0]->childNodes));
  $items = array_filter($items);
  return $items;
} 


function carlo_render_region($region) {
  $regions = carlo_structure_fields(carlo_get());

  if(!isset($regions[$region])) return;
  $region_blocks = $regions[$region];

  foreach((array)$region_blocks as $i => $block) {
    $content_blocks = get_field($region, get_queried_object_id())[$i] ?? [];
    if(is_string($block)) {
      carlo_render("blocks/{$block}", $content_blocks?:[]);
    } elseif($content_blocks) {
      foreach($content_blocks as $block) {
        carlo_render("blocks/{$block['acf_fc_layout']}", $block);
      }
    }
  }
}
