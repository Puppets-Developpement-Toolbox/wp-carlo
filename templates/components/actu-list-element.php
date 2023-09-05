<?php
$post = carlo_get('post');
?>
<div class="actu">
  <div class="actu-cont">
    <div class="image">
      <?= carlo_img(get_post_thumbnail_id($post), '734x738') ?>
    </div>
    <div class="desc">
      <div class="middle">
          <?php if($cat = current(get_the_category($post))) : ?>
          <div class="type">
            <?= $cat->name ?>
          </div>
        <?php endif ?>
        <h3 class="t3">
          <?= get_the_title($post) ?>
        </h3>
        <div class="text no-mobile">
          <?= get_the_excerpt($post) ?>
        </div>
        <div class="actions">
          <?php carlo_render('components/cta', [
            'link' => get_the_permalink($post),
            'label' => 'En savoir plus'
          ]) ?>
        </div>
      </div>
    </div>
  </div>
</div>