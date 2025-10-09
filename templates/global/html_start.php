<!DOCTYPE html>
<html <?php language_attributes(); ?> class="scroll-smooth">
	<head>
	   <meta name="viewport" content="width=device-width, initial-scale=1">
		<meta charset="<?php bloginfo("charset"); ?>" />
		<?php wp_head(); ?>
	</head>
  <body <?php body_class('flex flex-col'); ?>>

		<?php carlo_render("global/svg"); ?>
		<?php carlo_render("common/header"); ?>
