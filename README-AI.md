# Carlo WordPress Plugin - AI Agent Documentation

## Overview for AI Agents

Carlo is a WordPress plugin that implements a YAML-based configuration system for defining content structures, templates, and ACF (Advanced Custom Fields) integration. This documentation provides technical details for AI agents to understand, navigate, and work with the codebase.

## Documentation Structure

This project has three documentation files:

1. **README.md** - User-facing documentation in English with usage examples
2. **README-AI.md** (this file) - Technical documentation for AI agents with code flow details
3. **[Carlo.md](./Carlo.md)** - French documentation on the Carlo templating engine and WordPress driver

## Carlo Architecture

Carlo consists of two main parts:

1. **Carlo Templating Engine** - Provided by `puppets/library`, the core rendering system
2. **WordPress Driver** - This plugin, which integrates Carlo with WordPress and ACF

The templating engine in `puppets/library` provides the core functions (`carlo_render`, `carlo_get`, `carlo_img`) and driver abstractions. This plugin extends the BaseDriver to create CarloWpDriver, which integrates with WordPress and automatically generates ACF field groups from YAML definitions.

### Carlo Drivers (see Carlo.md:76-89)

Carlo can be used in different contexts through drivers:

1. **YmlDriver** - Implemented in `puppets/library`, used for testing components with YAML data files
   - Location: `vendor/puppets/library/src/carlo/YmlDriver.php`
   - Used by the library's component testing system

2. **CarloWpDriver** (this plugin) - WordPress integration driver
   - Location: `inc/carlo.php`
   - Extends BaseDriver from `puppets/library`
   - Automatically loads `structure.yml` from theme root
   - Generates ACF field groups from YAML definitions
   - Integrates with WordPress template hierarchy

For more details on drivers, see [Carlo.md](./Carlo.md) lines 76-89.

## Architecture

### Core Concepts

1. **YAML-Driven Structure**: The entire site structure is defined in a `structure.yml` file located in the theme root
2. **Regions**: Top-level content containers that hold sections
3. **Sections**: Content blocks that can be static or flexible (allowing multiple block types)
4. **Components**: Reusable template parts
5. **ACF Field Generation**: Automatic conversion of YAML definitions to ACF field groups
6. **Template Resolution**: Multi-level template lookup system (child theme → parent theme → plugin namespaces)

### File Organization

```
carlo/
├── carlo.php                 # Plugin bootstrap, loads inc/*.php files
├── inc/
│   ├── carlo.php            # Core: CarloWpDriver, ACF init, region rendering
│   ├── templates.php        # ACF field group generation for templates
│   ├── blocks.php           # ACF field type mapping and field generation
│   ├── assets.php           # Vite asset integration (dev/prod)
│   ├── feature.php          # WordPress feature configuration
│   ├── menus.php            # Menu registration hooks
│   ├── media.php            # Media handling
│   ├── archive.php          # Archive AJAX handlers
│   └── errors.php           # Error handling
├── templates/
│   ├── layout.php           # Main rendering controller
│   ├── archive.php          # Archive template
│   ├── error.php            # 404 error template
│   └── global/
│       ├── html_start.php   # HTML document start
│       └── html_end.php     # HTML document end
└── composer.json            # Dependency: puppets/library
```

## Code Flow

### Plugin Initialization (carlo.php:16-31)

1. Check for `structure.yml` in theme root - if missing, plugin returns early
2. Load all PHP files from `/inc` directory using glob
3. Files are loaded in alphabetical order

### ACF Initialization (inc/carlo.php:94-141)

**Hook**: `acf/init`

**Function**: `carlo_acf_init()`

**Flow**:
1. Retrieve `templates` from structure
2. For each template:
   - Call `carlo_acf_template_blocs($template, $definition, true)`
   - Register template with WordPress
3. Retrieve `types` from structure
4. For each custom post type:
   - Register post type with `register_post_type()` if `wp_args` exists
   - Process templates for the type
   - Process default template or `templates.default`

### ACF Field Group Generation (inc/templates.php:3-132)

**Function**: `carlo_acf_template_blocs($template, $definition, $has_template_condition)`

**Process**:
1. Filter out fields starting with `_` (metadata fields)
2. For each region in the template:
   - Create ACF group field for the region
   - For each block in the region:
     - If block has `_id`: create single block field
     - If block has `_blocs`: create flexible content field with multiple layouts
3. Determine location rules:
   - `archive`: matches posts page
   - `type_{post_type}__{template}`: matches custom post type with specific template
   - Default: matches page with specific page template
4. Register ACF field group with `acf_add_local_field_group()`

### Field Type Mapping (inc/blocks.php:18-203)

**Function**: `carlo_acf_fields($key, $definition, $parent_key)`

**Field Type Map** (inc/blocks.php:32-54):
```php
match ($type) {
    "text_short" => "text",
    "group" => "group",
    "container" => "flexible_content",
    "repeater" => "repeater",
    "wysiwyg" => "wysiwyg",
    "image" => "image",
    "url" => "url",
    "color" => "color_picker",
    "text" => "textarea",
    "number" => "number",
    "choices" => "select",
    "form" => "select",
    "reference" => "relationship",
    "boolean" => "true_false",
    "taxonomy" => "taxonomy",
    "date" => "date_picker",
    "link" => "link",
    "embed" => "oembed",
    "file" => "file",
    "datetime" => "date_time_picker"
}
```

**Special Behaviors**:
- Image + `_multi` → gallery
- Choices + `_multi` → checkbox
- Link + `_return_url` → returns URL string instead of array
- Reference: uses `_ref_type` for post types, `_max` for limit
- Taxonomy: uses `_taxo` for taxonomy slug
- Repeater: uses `_repeat` for sub-fields, `_times` for fixed count
- Container: uses `_items` for layouts, `_min/_max` for limits

### Template Rendering (templates/layout.php:1-44)

**Rendering Flow**:
1. Determine template based on WordPress context (inc/templates/layout.php:4-10):
   ```php
   $template = match (true) {
       is_home() => "archive",
       is_404() => "error",
       is_page() => get_page_template_slug() ?: "default",
       is_single() => "type_" . get_post_type(),
       default => "archive",
   };
   ```
2. Fire `carlo_prerender` action hook
3. Render `global/html_start`
4. Check for custom template file in theme: `/templates/{$template}.php`
5. If custom template exists: include it
6. If no custom template: render regions from structure
7. Render `global/html_end`
8. Flush output buffer

### Region Rendering (inc/carlo.php:225-256)

**Function**: `carlo_render_region($template, $region)`

**Process**:
1. Handle custom post types starting with `type_`
2. Get region definition from structure
3. Loop through region sections:
   - If section has `_id`: render static section
   - If section is flexible: get ACF data and render each block
4. For each flexible block:
   - Extract `acf_fc_layout` as `_id`
   - Render with `carlo_render($block['_id'], $block)`

### Menu Rendering (inc/carlo.php:160-223)

**Function**: `carlo_menu($menu)`

**Process**:
1. Add filters to inject data attributes on menu items
2. Call `wp_nav_menu()` with theme location
3. Filter `wp_nav_menu` output:
   - Parse HTML with DOMDocument
   - Extract menu structure recursively
   - Convert to array with: `current`, `name`, `href`, `object_id`, `children`, `classes`
4. Render custom template: `menus/{$theme_location}`
5. Return false to prevent default output

### Asset Loading (inc/assets.php:1-53)

**Production Mode** (`!WP_DEBUG && !is_admin()`):
- Load from Vite manifest: `dist/.vite/manifest.json`
- Use `WordpressViteAssets` library
- Inject `js/main.js` with integrity checking

**Development Mode** (`WP_DEBUG`):
- Load from Vite dev server using `$_ENV["ASSET_BASE_URL"]`
- Inject Vite client and module scripts
- Scripts loaded as ES modules

## YAML Structure Schema

### Root Structure
```yaml
regions:          # Map of region_key: "Label"
templates:        # Map of template definitions
types:            # Map of custom post type definitions
```

### Template Definition
```yaml
template_name:
  _label: "Template Label"  # Required
  region_name:              # Must match key in regions
    - _id: section_id       # Static section
      field_name: field_type
    - _blocs:               # Flexible content
        _max: 10            # Optional: max blocks
        - _id: block_id_1
          field1: type
        - _id: block_id_2
          field2: type
```

### Custom Post Type Definition
```yaml
post_type_slug:
  wp_args:                  # Passed to register_post_type()
    public: true
    label: "Display Name"
    supports: ['title', 'thumbnail']
  template:                 # Default template
    _label: "Default Template"
    region_name:
      - _id: section_id
        # fields...
  templates:                # Additional templates
    template_slug:
      _label: "Template Label"
      region_name:
        # sections...
```

### Field Definition Patterns

**Simple Field**:
```yaml
field_name: field_type
```

**Field with Options**:
```yaml
field_name:
  _type: field_type
  _label: "Field Label"
  _help: "Help text"
  _required: true
```

**Repeater**:
```yaml
field_name:
  _type: repeater
  _min: 1
  _max: 10
  _add_button: "Add Item"
  _times: 3                 # Exact count (sets both min and max)
  _repeat:
    subfield1: type1
    subfield2: type2
```

**Container (Flexible Content)**:
```yaml
field_name:
  _type: container
  _min: 1
  _max: 20
  _add_button: "Add Block"
  _items:
    - _id: layout_1
      field1: type1
    - _id: layout_2
      field2: type2
```

**Reference**:
```yaml
field_name:
  _type: reference
  _ref_type: post_type      # String or array
  _max: 3                   # Relationship limit
```

**Choices**:
```yaml
field_name:
  _type: choices
  _choices:
    value1: "Label 1"
    value2: "Label 2"
  _multi: true              # Creates checkbox instead of select
```

**Group**:
```yaml
field_name:
  _type: group              # Or omit _type, presence of nested fields implies group
  _label: "Group Label"
  subfield1: type1
  subfield2: type2
```

## Key Functions Reference

### From puppets/library (carlo namespace)

These functions are provided by the `puppets/library` dependency (see [Carlo.md](./Carlo.md) for detailed French documentation):

- `carlo_render(string $template, array $data): string` - Render template with data context (Carlo.md:9-24)
- `carlo_get(string|null $key = null): mixed` - Get value from current rendering context (Carlo.md:26-43)
- `carlo_img(string $key): string` - Generate responsive image HTML with WordPress optimization (Carlo.md:45-74)
- `carlo_structure(string $key)` - Get structure definition from YAML
- `carlo_driver(DriverInterface $driver)` - Set the driver instance
- `carlo_register(string $structure_path)` - Register a structure YAML file

For detailed documentation on the Carlo templating engine and image handling, refer to [Carlo.md](./Carlo.md).

### From carlo.php (inc/carlo.php)

- `carlo_acf_init()`: Initialize ACF field groups (hooked to `acf/init`)
- `carlo_register_templates(string $post_type, array $templates)`: Register templates for post type
- `carlo_menu(string $menu)`: Render navigation menu with custom template
- `carlo_render_region(string $template, string $region)`: Render all sections in a region
- `carlo_bootstrap()`: Include main layout.php template
- `_carlo_filter_nav(string $nav_menu, object $args)`: Filter menu HTML to custom format
- `_carlo_nav_extract_elements(DomNode $node)`: Recursively extract menu structure from DOM
- `_carlo_filter_nav_add_id(array $atts, object $item, object $args)`: Add object ID to menu links

### From templates.php (inc/templates.php)

- `carlo_acf_template_blocs(string $template, array $definition, bool $has_template_condition)`: Generate ACF field group for template
- `carlo_add_template_column(array $cols)`: Add template column to page list
- `carlo_template_column_value(string $column_name, int $post_id)`: Show template in page list

### From blocks.php (inc/blocks.php)

- `carlo_structure_fields(array $definition)`: Remove keys starting with `_`
- `carlo_acf_fields(string $key, mixed $definition, string $parent_key)`: Convert YAML field to ACF field array

## Data Access in Templates

### Getting Field Values

In template files, access ACF data using standard ACF functions:

```php
// Simple field
$title = get_field('title');

// From context data
$title = $data['title'];

// Using carlo_get (from puppets/library)
$title = carlo_get('title');
```

### Template Data Context

When rendering with `carlo_render()`, data is passed as second parameter:

```php
carlo_render('sections/hero', [
    'title' => 'Welcome',
    'subtitle' => 'To our site'
]);
```

In `templates/sections/hero.php`:
```php
<h1><?= $data['title'] ?></h1>
<h2><?= $data['subtitle'] ?></h2>
```

## Template Lookup Resolution (CarloWpDriver)

**File Resolution Order** (inc/carlo.php:5-27):

1. WordPress `locate_template()` checks:
   - Child theme: `templates/{path}`
   - Parent theme: `templates/{path}`
2. Fallback to parent class (BaseDriver) namespace resolution

**Path Building** (inherited from BaseDriver):
- Tests multiple path combinations based on variant and element
- Uses registered namespaces for package-based templates

## WordPress Modifications

### Disabled Features (inc/feature.php)
- Gutenberg for pages (line 6-10)
- Comment system entirely (lines 15-42)
- ACF admin interface in production (lines 45-47)
- Emoji detection scripts (lines 51-52)

### ACF JSON (inc/feature.php:49-58)
- Save location: `{theme}/acf/`
- Load location: `{theme}/acf/`
- Unsets default ACF JSON path

### Theme Support (inc/feature.php:60-63)
- `title-tag`
- `post-thumbnails`

### TinyMCE (inc/feature.php:68-89)
- Adds styleselect button
- Adds custom styles (e.g., "Large paragraphe")

## Archive AJAX (inc/archive.php)

**Endpoints**:
- `wp_ajax_archive_content`
- `wp_ajax_nopriv_archive_content`

**Parameters**:
- `page`: Current page number
- `cat`: Category filter
- `search`: Search query

**Response**: Renders posts using `carlo_render('components/actu-list-element', ['post' => $post])`

## Component Definition (see Carlo.md:132-152)

Components are defined in the theme's `templates/` directory with two files:
- **`.php`** - Implementation (the template code)
- **`.yml`** - Definition (field definitions)

Example: `templates/blocs/quote.php` + `templates/blocs/quote.yml` → component ID: `blocs/quote`

The YAML file defines all input fields needed for the component. Keys prefixed with `_` are internal configuration keys.

For detailed component definition syntax, see [Carlo.md](./Carlo.md) lines 132-152.

## Image Format Registration (see Carlo.md:154-156)

To resize images using WordPress's image engine, all formats must be pre-registered using `carlo_register_img_size()` which takes a list of format strings (e.g., `['100x200', '1600x900:1']`).

For format syntax and details, see:
- [Carlo.md](./Carlo.md) lines 45-74 for `carlo_img()` usage
- [Carlo.md](./Carlo.md) lines 154-156 for format registration
- `inc/media.php` for implementation

## Common Patterns for AI Agents

### Adding a New Section Type

1. Define in `structure.yml`:
   ```yaml
   templates:
     default:
       content:
         - _id: new_section
           field1: text_short
           field2: wysiwyg
   ```

2. Create template file: `templates/sections/new_section.php`:
   ```php
   <section class="new-section">
     <h2><?= carlo_get('field1') ?></h2>
     <div><?= carlo_get('field2') ?></div>
   </section>
   ```

3. Optionally create component definition: `templates/sections/new_section.yml` (see Carlo.md:132-152)

### Adding a Custom Post Type

1. Define in `structure.yml`:
   ```yaml
   types:
     product:
       wp_args:
         public: true
         label: "Products"
         supports: ['title', 'thumbnail']
       template:
         _label: "Product"
         details:
           - _id: product_info
             price: number
             sku: text_short
   ```

2. Create template: `templates/sections/product_info.php`

3. Template automatically registered and ACF fields created

### Modifying Field Types

When changing field types in YAML:
1. Update `structure.yml`
2. Delete ACF JSON files in theme's `/acf` directory (they regenerate)
3. Clear ACF cache (or deactivate/reactivate plugin)

### Debugging

**Enable ACF Admin**:
- Set `WP_DEBUG` to `true` in `wp-config.php`
- ACF admin will appear (inc/feature.php:45-47)

**Check Structure Loading**:
- Call `carlo_structure('templates')` or `carlo_structure('types')`
- Returns parsed YAML structure

**Verify Field Generation**:
- Check ACF field groups in admin when `WP_DEBUG` is enabled
- Look for field group key matching template name

## Error Handling

- Missing namespace: Exception thrown (inc/carlo.php:11)
- Missing field definition: Exception thrown (inc/blocks.php:21-24)
- `_ref_max` usage: Exception thrown, should use `_max` (inc/blocks.php:175-178)
- Missing structure.yml: Plugin returns early, no initialization (carlo.php:22-25)

## Hook Points

### Actions
- `carlo_prerender($template)`: Before template rendering (templates/layout.php:12)
- `acf/init`: ACF field group registration (inc/carlo.php:94)
- `after_setup_theme`: Theme support additions (inc/feature.php:60)
- `wp_enqueue_scripts`: Asset loading (inc/assets.php:21)
- `admin_init`: Comment disabling (inc/feature.php:15)
- `admin_menu`: Remove comment menu (inc/feature.php:34)
- `init`: Admin bar modifications (inc/feature.php:38)

### Filters
- `use_block_editor_for_post_type`: Disable Gutenberg (inc/feature.php:6)
- `theme_{$post_type}_templates`: Register templates (inc/carlo.php:145)
- `nav_menu_link_attributes`: Add object ID to links (inc/carlo.php:162)
- `wp_nav_menu`: Custom menu rendering (inc/carlo.php:163)
- `script_loader_tag`: Module script injection (inc/assets.php:33)
- `acf/settings/show_admin`: Hide ACF in prod (inc/feature.php:46)
- `acf/settings/save_json`: ACF JSON save path (inc/feature.php:49)
- `acf/settings/load_json`: ACF JSON load path (inc/feature.php:54)
- `manage_pages_columns`: Add template column (inc/templates.php:136)
- `tiny_mce_before_init`: Add custom styles (inc/feature.php:77)

## Dependencies

### Composer
- **`puppets/library@1.x-dev`**: Core Carlo templating engine
  - Location: `vendor/puppets/library/`
  - Repository: [https://github.com/Puppets-Developpement-Toolbox/library](https://github.com/Puppets-Developpement-Toolbox/library)
  - Provides:
    - Core rendering engine (`carlo_render`, `carlo_get`, `carlo_img`)
    - BaseDriver class (extended by CarloWpDriver)
    - YAML structure parsing and registration
    - Namespace-based template resolution
    - Component rendering system
  - The library also includes reusable components defined in [Figma mockups](https://www.figma.com/design/YAGncUCOUdVUay7mKLAH2x/-LIB--Diapsodie?node-id=2-4&p=f&t=QDyYKzHqjUoKvxHK-0)

### WordPress
- Advanced Custom Fields Pro (ACF)
- WordPress 6.1+
- PHP 8.0+

### NPM (in theme, not plugin)
- `idleberg/wordpress-vite-assets`: Vite manifest parsing (inc/assets.php:2)
- Vite for asset building

## Performance Considerations

1. **Template Caching**: Templates resolved via `locate_template()` are cached by WordPress
2. **ACF Field Groups**: Registered once per request on `acf/init`
3. **YAML Parsing**: Structure parsed once on plugin load (handled by puppets/library)
4. **Output Buffering**: Full page uses output buffering (templates/layout.php:3, 43)

## Security Notes

1. **Direct Access Protection**: All PHP files check for `ABSPATH` or return early
2. **ACF Data**: Uses ACF's built-in sanitization
3. **Menu Rendering**: Uses WordPress's `wp_nav_menu()` for sanitized output
4. **AJAX Endpoints**: No nonce verification in archive.php - should be added for production
5. **Capability Checks**: None present - relies on WordPress and ACF defaults

## AI Agent Task Guidelines

### When Modifying Structure
1. Always backup `structure.yml` before changes
2. Validate YAML syntax
3. Ensure region keys match between `regions` definition and template usage
4. Clear ACF JSON cache after structure changes

### When Adding Features
1. Check if feature can be added via `structure.yml` first
2. If code changes needed, follow existing patterns in `/inc` files
3. Use appropriate hooks (prefer `acf/init` for ACF-related changes)
4. Add template files in theme's `templates/` directory, not plugin

### When Debugging
1. Enable `WP_DEBUG` to see ACF admin
2. Check `carlo_structure()` output for YAML parsing issues
3. Verify template file paths match expected resolution order
4. Check browser console for asset loading issues (Vite)

### Common Pitfalls
1. Using `_ref_max` instead of `_max` for references
2. Forgetting to clear ACF cache after structure changes
3. Template files in wrong directory (must be in `templates/`)
4. Region names with leading `_` (they're filtered out)
5. Missing `_id` on sections (required for rendering)
