<section class="section intro-page <?= carlo_get('deco_color') ?>">
  <div class="image">
    <?= carlo_img(carlo_get('image'), [
      '654x888',
      '(min-width: 1024px)' => '1135x408'
    ]) ?>
  </div>
  <div class="content">
    <h1 class="t1">
      <span>
        <?= get_the_title(get_queried_object_id()) ?>
      </span>
    </h1>
    <div class="desc">
      <?= carlo_get('description') ?>
    </div>
  </div>
</section>


