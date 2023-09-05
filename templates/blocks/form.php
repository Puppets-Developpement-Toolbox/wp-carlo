<?php
$form_id = carlo_get('form');
if(!$form_id) return;
?>

<section class="section contact-form <?= carlo_get('deco_color') ?>">
  <div class="desc">
    <h2 class="t2">
      <?= carlo_get('title') ?>
    </h2>
    <div class="text">
      <?= carlo_get('description') ?>
    </div>
  </div>
  <div class="form">
    <?= current(WPCF7_ContactForm::find($form_id))->form_html() ?>
  </div>
</section>