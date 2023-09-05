<section class="section push <?= carlo_get('deco_color') ?>">
  <div class="text">
    <h2 class="t2">
      <?= carlo_get('title') ?>
    </h2>
    <div class="desc">
    <?= carlo_get('description') ?>
    </div>
    <!-- use template component/cta -->
    <?php carlo_render('components/cta', carlo_get('cta')) ?>
  </div>
  <div class="image">
    <?= carlo_img(carlo_get('image'), '1002x960') ?>
  </div>
</section>