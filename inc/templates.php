<?php

function carlo_register_templates($page_templates, $wp_theme, $post) {
  $templates = carlo_structure('templates');
  foreach($templates as $template => $definition) {
    if (!isset($page_templates[$template]) && $template !== 'archive') {
      $page_templates[$template] = $definition['_label'];
    }
  }
  return $page_templates;
}

function carlo_acf_template_blocs($template, $definition) {
  $definition = carlo_structure_fields($definition);
  $i = 0;
  $regions = [];
  foreach($definition as $region => $region_blocks) {
    $label = carlo_structure('regions', $region);
    $fields = [];

    foreach((array)$region_blocks as $i => $block) {
      if(is_string($block)) {
        $fields[] = carlo_acf_fields($i, carlo_structure('blocs', $block), "{$template}_{$region}");
      } else {
        $fields[] = [
          'key' => "{$template}_{$region}_{$i}",
          'name' => $i,
          'type' => 'flexible_content',
          'label' => 'Blocs de contenu',
          'display' => 'block',
          'layouts' => array_map(function($block) use($template, $region) {
            return carlo_acf_fields($block, carlo_structure('blocs', $block), "{$template}_{$region}");
          }, $block),
          'button_label' => 'Ajouter un bloc',
        ];
      }
    }

    $regions[] = [
      'key' => "{$template}_{$region}",
      'label' => $label,
      'name' => $region,
      'layout' => 'block',
      'type' => 'group',
      'sub_fields' => $fields
    ];
  }

  if($template === 'archive') {
    $location = [[[
      'param' => 'page_type',
      'operator' => '==',
      'value' => 'posts_page',
    ]]];
  } elseif(str_starts_with($template, 'type_')) {
    $location = [[[
      'param' => 'post_type',
      'operator' => '==',
      'value' => str_replace('type_', '', $template),
    ]]];
  } else {
    $location = [[[
      'param' => 'post_type',
      'operator' => '==',
      'value' => 'page',
    ], [
      'param' => 'page_template',
      'operator' => '==',
      'value' => $template,
    ], [
      'param' => 'page_type',
      'operator' => '!=',
      'value' => 'posts_page',
    ]]];
  }

  acf_add_local_field_group([
    'key' => $template,
    'title' => "Layout {$template}",
    'layout' => 'seamless',
    'active' => true,
    'fields' => $regions,
    'location' => $location,
    'menu_order' => $i++,
    'hide_on_screen' => [
      'the_content', 'discussion', 'comments', 
      'revisions', 'slug', 'author', 'format', 
      'featured_image', 'categories', 'tags', 
      'send-trackbacks'
    ]
  ]);
}