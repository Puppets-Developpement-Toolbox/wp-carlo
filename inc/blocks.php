<?php

/**
 * remove all attribute's key prefixed by _
 */
function carlo_structure_fields($definition) {
  return array_filter($definition, function($k) {
    return substr($k, 0, 1) !== '_';
  }, ARRAY_FILTER_USE_KEY);
}

function carlo_acf_fields($key, $definition, $parent_key) {
  if(empty($definition)) throw new Exception("le champ {$key} de {$parent_key} n'a pas de définition");
  if(is_array($definition) && !isset($definition['_type'])) {
    $type = 'group';
  } else {
    $type = is_string($definition) ? $definition : $definition['_type'];
  }

  $acf_type = match($type) {
    'text_short' => 'text',
    'group' => 'group',
    'repeater' => 'repeater',
    'wysiwyg' => 'wysiwyg',
    'image' => 'image',
    'url' => 'url',
    'color' => 'color_picker',
    'text' => 'textarea',
    'number' => 'number',
    'select' => 'select',
    'form' => 'select',
    'reference' => 'relationship',
    'boolean' => 'true_false',
    'taxonomy' => 'taxonomy',
    'date' => 'date_picker',
    'datetime' => 'date_time_picker'
  };

  if(isset($definition['_label'])) {
    $label = $definition['_label'];
    unset($definition['_label']);
  } else {
    $label = str_replace('_', ' ', ucfirst($key));
  }

  $acf = [
    'key' => "{$parent_key}_{$key}",
    'label' => $label,
    'name' => $key,
    'type' => $acf_type,
    'required' => !empty($definition['_required']) && $definition['_required'] ? 1 : 0
  ];

  if($type === 'image') {
    $acf['return_format'] = 'id';
  }

  if(isset($definition['_help'])) {
    $acf['instructions'] = $definition['_help'];
  }

  if($type === 'group') {
    $definition = carlo_structure_fields($definition);
    $acf['sub_fields'] = array_map('carlo_acf_fields', array_keys($definition), $definition, array_fill(0, count($definition), "{$parent_key}_{$key}"));
    $acf['layout'] = 'block';
  }

  if($type === 'repeater') {
    $acf['sub_fields'] = array_map(
      'carlo_acf_fields', 
      array_keys($definition['_repeat']), 
      $definition['_repeat'], 
      array_fill(0, count( $definition['_repeat']), "{$parent_key}_{$key}")
    );
    $acf['layout'] = 'block';

    foreach($acf['sub_fields'] as $k => $v) {
      $acf['sub_fields'][$k]['parent_repeater'] = "{$parent_key}_{$key}";
    }

    $acf['button_label'] = $definition['_add_button'] ?? 'Ajouter un élément';
    $acf['min'] = $definition['_min'] ?? null;
    $acf['max'] = $definition['_max'] ?? null;
    if(!isset($acf['min']) && !isset($acf['max']) && isset($definition['_times'])) {
      $acf['min'] = $acf['max'] = $definition['_times'];
    }
  }

  if($type === 'select') {
    $acf['choices'] = $definition['_choices'];
  }

  if($type === 'form') {
    /** @var NF_Database_Models_Form[] */
    $forms = WPCF7_ContactForm::find();
    foreach($forms as $form) $acf['choices'][$form->id()] = $form->title();
  }

  if($type === 'reference') {
    $acf['post_type'] = (array)$definition['_ref_type'];
    $acf['return_format'] = 'object';
    if(isset($definition['_ref_max'])) {
      throw new Exception('Les référence doivent utiliser la clé _max et non _ref_max');
    }
    if(isset($definition['_max'])) $acf['max'] = $definition['_max'];
  }

  if($type === 'taxonomy') {
    $acf['taxonomy'] = $definition['_taxo'];
    $acf['return_format'] = 'object';
    $acf['field_type'] = 'multi_select';
  }

  if($type === 'text') {
    $acf['new_lines'] = 'br';
  }
  

  return $acf;
}

function carlo_acf_init() {
  add_filter('theme_page_templates', 'carlo_register_templates', PHP_INT_MAX, 3);
  $templates = carlo_structure('templates');
  if(is_array($templates)) {
    foreach($templates as $template => $definition) {
      carlo_acf_template_blocs($template, $definition);
    }
  }
  $types = carlo_structure('types');
  if(is_array($types)) {
    foreach($types as $type => $definition) {
      carlo_acf_template_blocs("type_{$type}", $definition['template']);
    }
  }
}

add_action('acf/init', 'carlo_acf_init');
