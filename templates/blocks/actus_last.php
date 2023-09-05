<?php
$posts = get_posts([
  'numberposts' => 3,
  'orderby' => 'date',
  'order' => 'DESC',
  'post_type' => 'post',
  'exclude' => [get_the_ID()]
]);
if(empty($posts)) return;

$common = get_fields( 'options' );

?>

<aside class="section actus-last">
  <div class="desc">
    <?php if($title = get_field('last_news_title', 'options')) : ?>
      <h2 class="t2">
        <?= $title ?>
      </h2>
    <?php endif ?>
    <?php if($description = get_field('last_news_description', 'options')) : ?>
      <p><?= $description ?></p>
    <?php endif ?>
  </div>
  
  <div class="actus-list">
    <?php foreach($posts as $post) : ?>
      <div class="actu">
        <div class="actu-cont">
          <div class="image">
            <?= carlo_img(get_post_thumbnail_id($post), '734x338') ?>
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
              <div class="actions">
                <?php carlo_render('components/cta', [
                  'link' => get_permalink($post),
                  'label' => 'En savoir plus'
                ]) ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  
  <div class="actions">
    <?php carlo_render('components/cta', [
      'link' => get_post_type_archive_link('post'),
      'label' => 'Voir plus d\'articles'
    ]) ?>
  </div>
</aside>