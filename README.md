# Carlo WordPress Plugin

A powerful WordPress plugin that provides a YAML-based configuration system for building structured, component-driven websites with Advanced Custom Fields (ACF).

## Documentation

- **[Carlo.md](./Carlo.md)** - Documentation complète en français sur le moteur Carlo et le driver WordPress
- **[README-AI.md](./README-AI.md)** - Technical documentation for AI agents and developers
- **[docs/](./docs/)** - Additional technical documentation and analysis

## Overview

Carlo is a WordPress plugin developed by [Puppets](https://puppets.fr) that transforms how you build WordPress sites by introducing a declarative, YAML-based approach to defining post types, templates, components, and sections. It heavily integrates with ACF to provide a flexible content builder system based on regions that can contain multiple sections.

Carlo is built on top of the Carlo templating engine, which is part of the [Puppets Library](https://github.com/Puppets-Developpement-Toolbox/library).

## Key Features

- **YAML-driven configuration**: Define your entire site structure in a single `structure.yml` file
- **ACF Integration**: Automatic ACF field group generation from YAML definitions
- **Region-based layout system**: Organize content into regions that contain sections
- **Component architecture**: Build reusable components and sections
- **Template flexibility**: Support for page templates and custom post type templates
- **Built-in rendering engine**: Template rendering system with namespacing support
- **Vite integration**: Modern asset pipeline for development and production
- **WordPress features**: Disables Gutenberg for pages, removes comments, and provides opinionated WordPress defaults

## Requirements

- WordPress 6.1+
- PHP 8.0+
- Advanced Custom Fields Pro
- A Carlo-compatible theme with `structure.yml` file

## Installation

1. Install the plugin in your WordPress plugins directory
2. Ensure ACF Pro is installed and activated
3. Create a `structure.yml` file in your theme's root directory
4. Activate the plugin

The plugin will automatically detect if your theme is Carlo-compatible by checking for the `structure.yml` file. If the file doesn't exist, the plugin will not initialize.

## Structure Definition

### Basic Structure

Your theme's `structure.yml` file defines the entire content structure:

```yaml
regions:
  header: "En-tête"
  content: "Contenu"
  footer: "Pied de page"

templates:
  default:
    _label: "Page par défaut"
    content:
      - _id: hero
        # ... hero section definition
      - _blocs:
          - _id: text_section
            # ... section definition
          - _id: image_gallery
            # ... section definition

types:
  project:
    wp_args:
      public: true
      label: "Projets"
    template:
      _label: "Projet"
      content:
        - _id: project_details
          # ... project fields
```

### Regions

Regions are high-level content areas defined in the YAML structure. They act as containers for sections:

```yaml
regions:
  header: "Header Region"
  main: "Main Content"
  sidebar: "Sidebar"
  footer: "Footer Region"
```

Regions are referenced in templates and rendered using `carlo_render_region($template, $region)`.

### Templates

Templates define the layout structure for pages and custom post types:

```yaml
templates:
  homepage:
    _label: "Page d'accueil"
    hero:
      - _id: hero_section
        title: text_short
        subtitle: text
        image: image
    content:
      - _blocs:
          - _id: text_block
          - _id: gallery_block
```

### Custom Post Types

Define custom post types with their own templates:

```yaml
types:
  project:
    wp_args:
      public: true
      label: "Projets"
      supports: ['title', 'thumbnail']
    template:
      _label: "Projet"
      details:
        - _id: project_info
          client: text_short
          year: number
          description: wysiwyg
    templates:
      portfolio:
        _label: "Portfolio View"
        gallery:
          - _id: image_gallery
```

### Field Types

Carlo supports a wide range of field types that map to ACF field types:

- `text_short` → text
- `text` → textarea
- `wysiwyg` → wysiwyg editor
- `image` → image (returns ID)
- `url` → url
- `color` → color picker
- `number` → number
- `choices` → select/checkbox
- `reference` → relationship
- `boolean` → true/false
- `taxonomy` → taxonomy
- `date` → date picker
- `datetime` → datetime picker
- `link` → link
- `embed` → oembed
- `file` → file
- `group` → group
- `repeater` → repeater
- `container` → flexible content

### Field Options

Fields support various options prefixed with `_`:

```yaml
field_name:
  _type: text_short
  _label: "Field Label"
  _help: "Help text shown below field"
  _required: true
  _multi: true  # For images (creates gallery) or choices (creates checkbox)
```

#### Repeater Example

```yaml
team_members:
  _type: repeater
  _label: "Team Members"
  _min: 1
  _max: 10
  _add_button: "Add Team Member"
  _repeat:
    name: text_short
    role: text_short
    photo: image
```

#### Container (Flexible Content) Example

```yaml
content_blocks:
  _type: container
  _min: 1
  _max: 20
  _add_button: "Add Content Block"
  _items:
    - _id: text_block
      content: wysiwyg
    - _id: image_block
      image: image
      caption: text
```

#### Reference Example

```yaml
related_projects:
  _type: reference
  _label: "Related Projects"
  _ref_type: project  # or ['project', 'page']
  _max: 3
```

## Usage

### Rendering Regions

In your theme templates, render regions using:

```php
carlo_render_region($template, $region);
```

Example in `layout.php`:

```php
carlo_render("global/html_start");
?>
<header>
  <?php carlo_render_region('default', 'header'); ?>
</header>
<main>
  <?php carlo_render_region('default', 'content'); ?>
</main>
<footer>
  <?php carlo_render_region('default', 'footer'); ?>
</footer>
<?php
carlo_render('global/html_end');
```

### Rendering Components

Render individual components or sections:

```php
carlo_render('components/button', [
  'text' => 'Click me',
  'url' => '#'
]);
```

### Template Files

Create template files in your theme's `templates/` directory:

```
templates/
├── global/
│   ├── html_start.php
│   └── html_end.php
├── sections/
│   ├── hero.php
│   └── text_block.php
├── components/
│   ├── button.php
│   └── card.php
└── menus/
    └── main.php
```

### Menu Rendering

Carlo provides a custom menu rendering system:

```php
carlo_menu('primary');
```

This extracts menu structure and renders it using your custom template in `templates/menus/{menu-location}.php`.

### Image Handling

Generate responsive images with sources:

```php
$driver->img(
    key: 'hero_image',
    default_size: 'large',
    source_sizes: [
        '(min-width: 1024px)' => 'full',
        '(min-width: 768px)' => 'large'
    ],
    mobile_key: 'hero_image_mobile',
    mobile_source_sizes: [
        '(max-width: 767px)' => 'medium'
    ],
    imgAttrs: ['class' => 'hero-img']
);
```

## File Structure

```
carlo/
├── carlo.php              # Main plugin file
├── inc/
│   ├── carlo.php         # Core driver and ACF integration
│   ├── templates.php     # Template registration
│   ├── blocks.php        # ACF field generation
│   ├── assets.php        # Vite asset handling
│   ├── feature.php       # WordPress feature configuration
│   ├── menus.php         # Menu registration
│   ├── media.php         # Media handling
│   ├── archive.php       # Archive AJAX handlers
│   └── errors.php        # Error handling
├── templates/
│   ├── layout.php        # Main layout controller
│   ├── archive.php       # Archive template
│   ├── error.php         # 404 template
│   └── global/
│       ├── html_start.php
│       └── html_end.php
└── composer.json         # Dependencies (puppets/library)
```

## Dependencies

Carlo depends on the **[puppets/library](https://github.com/Puppets-Developpement-Toolbox/library)** package which provides:
- The core Carlo templating engine (`carlo_render`, `carlo_get`, `carlo_img`)
- The BaseDriver class that CarloWpDriver extends
- YAML structure parsing and validation
- Namespace-based template resolution
- Component rendering system

The library also includes a collection of reusable, generic components based on Carlo, as defined in [these mockups](https://www.figma.com/design/YAGncUCOUdVUay7mKLAH2x/-LIB--Diapsodie?node-id=2-4&p=f&t=QDyYKzHqjUoKvxHK-0).

## ACF Field Groups

Carlo automatically generates ACF field groups based on your YAML structure. Field groups are:

- Saved as JSON in your theme's `/acf` directory
- Registered programmatically on `acf/init` action
- Hidden from the admin interface in production (when `WP_DEBUG` is false)
- Associated with templates using ACF location rules

## WordPress Modifications

Carlo makes several opinionated modifications to WordPress:

- Disables Gutenberg editor for pages
- Completely removes comment functionality
- Removes emoji scripts
- Adds theme support for title-tag and post-thumbnails
- Adds custom TinyMCE style formats
- Adds template column to page list in admin

## Development

### Vite Integration

In development mode (`WP_DEBUG = true`):
- Assets are loaded from Vite dev server
- Hot module replacement is enabled
- Assets are served from `$_ENV["ASSET_BASE_URL"]`

In production:
- Assets are loaded from `dist/.vite/manifest.json`
- Automatic integrity checking
- Optimized bundles

### Template Lookup

Template files are resolved in this order:
1. Child theme `templates/` directory
2. Parent theme `templates/` directory
3. Plugin namespaced directories (via `puppets/library`)

## API Reference

### Core Functions (from puppets/library)

The following functions are provided by the Carlo templating engine in `puppets/library`:

- `carlo_render($template, $data)` - Render a template with data
- `carlo_get($key)` - Get value from current rendering context
- `carlo_img($key, $sizes)` - Generate responsive image HTML with WordPress optimization
- `carlo_structure($key)` - Get structure definition from YAML
- `carlo_driver($driver)` - Set the driver instance
- `carlo_register($structure_path)` - Register a structure YAML file

### WordPress Plugin Functions

Functions added by the wp-carlo plugin:

- `carlo_render_region($template, $region)` - Render a region from structure
- `carlo_menu($location)` - Render a navigation menu
- `carlo_bootstrap()` - Initialize layout rendering
- `carlo_acf_init()` - Initialize ACF field groups
- `carlo_register_templates($post_type, $templates)` - Register templates for post type

### Filters

- `carlo_prerender` - Action before template rendering starts
- `theme_{$post_type}_templates` - Filter available templates for post type

## Advanced Usage

### Custom Post Types with Custom Templates

You can create custom single and archive templates for your custom post types. See [Carlo.md](./Carlo.md) lines 158-277 for detailed examples of creating:
- `single-[my-cpt].php` - Single post template for custom post type
- `archive-[my-cpt].php` - Archive template for custom post type

These allow you to bypass the default page-based approach and create dedicated templates for your custom post types.

## Best Practices

1. **Keep structure.yml organized**: Use consistent naming and group related definitions
2. **Use components**: Break down complex sections into reusable components
3. **Leverage regions**: Organize content logically using regions
4. **Field naming**: Use descriptive field names without prefixes
5. **Version control ACF JSON**: Commit the `/acf` directory to version control
6. **Component definitions**: Create both `.php` and `.yml` files for components (see Carlo.md:132-152)
7. **Register image formats**: Pre-register all image sizes you'll use with `carlo_register_img_size()` (see Carlo.md:154-156)

## Troubleshooting

### Plugin doesn't activate
- Ensure `structure.yml` exists in your theme root
- Check PHP version is 8.0+
- Verify ACF Pro is installed

### Fields not appearing
- Check ACF field groups in admin (enable `WP_DEBUG`)
- Verify YAML syntax is correct
- Clear ACF cache

### Templates not rendering
- Check template file exists in `templates/` directory
- Verify region names match between YAML and template calls
- Enable WordPress debug logging

## License

Proprietary - Puppets Development

## Credits

Developed by [Puppets](https://puppets.fr)

## Support

For support and questions, contact Puppets at https://puppets.fr
