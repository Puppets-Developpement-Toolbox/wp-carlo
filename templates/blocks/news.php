<?php 
$max_posts = carlo_get('nb_articles');
$posts = get_posts([
  'numberposts' => $max_posts,
  'orderby' => 'date',
  'order' => 'DESC',
  'post_type' => 'post'
]);
if(empty($posts)) return;
?>

<section class="section actus-list borders-top-bottom <?= carlo_get('deco_color') ?>">
  <h2 class="t2">
    <?= carlo_get('title'); ?>
  </h2>
  <div class="list-actus" role="list">
    <?php foreach($posts as $post) : ?>
      <div class="actu">
        <div class="actu-cont">
          <div class="image">
            <?= carlo_img(get_post_thumbnail_id($post), [
              '684x638',
              '(min-width: 1024px)' => '734x800'
            ]) ?>
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
    <?php endforeach; ?>
  </div>
  <div class="actions">
    <?php carlo_render('components/cta', [
      'link' => get_post_type_archive_link('post'),
      'label' => 'Voir plus d\'articles'
    ]) ?>
  </div>
</section>