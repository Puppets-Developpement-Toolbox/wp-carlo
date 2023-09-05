<section class="section contact-map">
  <div class="desc">
    <h2 class="t2">
      <?= carlo_get('title') ?>
    </h2>
    <div class="text">
      <?= carlo_get('subtitle') ?>
    </div>
  </div>
  <div class="map">
    <a href="<?= carlo_get('link') ?>" target="_blank">
      <?= carlo_img(carlo_get('image'), [
        '1744x922',
        '(max-width:1056px)' => '737x737'
      ]) ?>
    </a>
  </div>
</section>
