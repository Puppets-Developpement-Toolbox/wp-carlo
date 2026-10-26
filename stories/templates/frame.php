<?php
/**
 * Rendu seul d'un exemple, dans l'iframe du catalogue : CSS et JS du thème (wp_head, wp_footer),
 * sans l'en-tête ni le pied de page du site (global/html_start affiche common/header).
 *
 * @var array $element élément du catalogue (cf. carlo_stories_elements)
 * @var array $story   valeurs de l'exemple
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <?php wp_head(); ?>
</head>
<body <?php body_class('carlo-story'); ?>>
  <!-- Même enveloppe que templates/layout.php : le CSS du thème s'applique comme sur une page. -->
  <main id="main" class="flex-1 flex flex-col">
    <?php
    ob_start();
    try {
        carlo_render($element['id'], carlo_stories_data($element['fields'], $story));
        ob_end_flush();
    } catch (Throwable $e) {
        ob_end_clean();
        printf(
            '<pre style="margin:1rem;padding:1rem;border-left:4px solid #b32d2e;background:#fcf0f1;color:#1e1e1e;white-space:pre-wrap;font:14px/1.5 monospace">%s</pre>',
            esc_html("Erreur de rendu : {$e->getMessage()}\n{$e->getFile()}:{$e->getLine()}")
        );
    }
    ?>
  </main>
  <?php wp_footer(); ?>
</body>
</html>
