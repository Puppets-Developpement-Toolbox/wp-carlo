<?php

declare(strict_types=1);

/**
 * Carlo WordPress Introspection API
 *
 * Provides WordPress-specific wrappers around Carlo's introspection system
 */

/**
 * Get all available blocks (sections + components) for WordPress
 */
function carlo_wp_get_available_blocks(): array
{
    $sections = carlo_get_available_sections(true);
    $components = carlo_get_available_components(true);

    return [
        'sections' => $sections,
        'components' => $components,
        'total' => count($sections) + count($components)
    ];
}

/**
 * Get template information with regions and allowed blocks
 */
function carlo_wp_get_template_info(string $template): ?array
{
    $structure = carlo_structure('templates');

    if (!isset($structure[$template])) {
        return null;
    }

    $templateDef = $structure[$template];
    $regions = [];

    // Extract regions
    foreach ($templateDef as $regionKey => $regionDef) {
        if (str_starts_with($regionKey, '_')) {
            continue; // Skip metadata
        }

        $regions[$regionKey] = [
            'label' => $regionKey,
            'blocks' => carlo_wp_extract_region_blocks($regionDef)
        ];
    }

    return [
        'template' => $template,
        'label' => $templateDef['_label'] ?? $template,
        'regions' => $regions
    ];
}

/**
 * Extract block IDs from region definition
 */
function carlo_wp_extract_region_blocks(array $regionDef): array
{
    $blocks = [];

    foreach ($regionDef as $item) {
        if (isset($item['_id'])) {
            // Static block
            $blocks[] = $item['_id'];
        } elseif (isset($item['_blocs'])) {
            // Flexible content - extract all allowed blocks
            foreach ($item['_blocs'] as $bloc) {
                if (isset($bloc['_id'])) {
                    $blocks[] = $bloc['_id'];
                }
            }
        }
    }

    return array_unique($blocks);
}

/**
 * Get page information including current blocks
 */
function carlo_wp_get_page_structure(int $post_id): array
{
    $post = get_post($post_id);
    if (!$post) {
        return ['error' => 'Post not found'];
    }

    // Get template
    $template = match (true) {
        $post->post_type === 'page' => get_page_template_slug($post_id) ?: 'default',
        default => 'type_' . $post->post_type
    };

    // Get template info
    $templateInfo = carlo_wp_get_template_info($template);

    if (!$templateInfo) {
        return ['error' => "Template '{$template}' not found"];
    }

    // Get current blocks from ACF
    $currentBlocks = [];

    foreach ($templateInfo['regions'] as $regionKey => $regionInfo) {
        $regionContent = get_field($regionKey, $post_id);

        if (is_array($regionContent) && isset($regionContent[0]) && is_array($regionContent[0])) {
            $blocks = [];

            foreach ($regionContent[0] as $index => $block) {
                if (isset($block['acf_fc_layout'])) {
                    $blocks[] = [
                        'index' => $index,
                        'id' => $block['acf_fc_layout'],
                        'data' => $block
                    ];
                }
            }

            $currentBlocks[$regionKey] = $blocks;
        }
    }

    return [
        'post_id' => $post_id,
        'title' => $post->post_title,
        'post_type' => $post->post_type,
        'post_status' => $post->post_status,
        'template' => $template,
        'template_info' => $templateInfo,
        'current_blocks' => $currentBlocks
    ];
}

/**
 * Validate if a block can be added to a region
 */
function carlo_wp_validate_block_for_region(string $block_id, int $post_id, string $region): array
{
    // Get template info
    $pageStructure = carlo_wp_get_page_structure($post_id);

    if (isset($pageStructure['error'])) {
        return ['valid' => false, 'message' => $pageStructure['error']];
    }

    // Check if region exists
    if (!isset($pageStructure['template_info']['regions'][$region])) {
        return [
            'valid' => false,
            'message' => "Region '{$region}' does not exist in this template"
        ];
    }

    $allowedBlocks = $pageStructure['template_info']['regions'][$region]['blocks'];

    // If no blocks defined, allow all
    if (empty($allowedBlocks)) {
        return ['valid' => true, 'message' => 'All blocks allowed'];
    }

    // Check if block is in allowed list
    if (in_array($block_id, $allowedBlocks, true)) {
        return ['valid' => true, 'message' => 'Block allowed'];
    }

    return [
        'valid' => false,
        'message' => "Block '{$block_id}' is not allowed in region '{$region}'. Allowed blocks: " . implode(', ', $allowedBlocks)
    ];
}

/**
 * List all available templates
 */
function carlo_wp_list_templates(): array
{
    $structure = carlo_structure('templates');

    if (!is_array($structure)) {
        return [];
    }

    $templates = [];

    foreach ($structure as $key => $def) {
        if (str_starts_with($key, '_')) {
            continue;
        }

        $templates[$key] = [
            'key' => $key,
            'label' => $def['_label'] ?? $key,
            'regions' => array_keys(array_filter(
                $def,
                fn($k) => !str_starts_with($k, '_'),
                ARRAY_FILTER_USE_KEY
            ))
        ];
    }

    return $templates;
}

/**
 * Get simplified list of blocks for AI (lighter format)
 */
function carlo_wp_get_blocks_simple(): array
{
    $sections = carlo_scan_elements('sections');
    $components = carlo_scan_elements('components');

    $result = [];

    foreach ($sections as $id) {
        $info = carlo_get_element_info($id);
        $result[$id] = [
            'type' => 'section',
            'label' => $info['_label'] ?? $id,
            'fields' => array_keys($info['_fields'] ?? [])
        ];
    }

    foreach ($components as $id) {
        $info = carlo_get_element_info($id);
        $result[$id] = [
            'type' => 'component',
            'label' => $info['_label'] ?? $id,
            'fields' => array_keys($info['_fields'] ?? [])
        ];
    }

    return $result;
}
