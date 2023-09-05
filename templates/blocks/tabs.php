<section class="section partners">
  <h2 class="t2"><?= carlo_get('title'); ?></h2>

  <?php $partners = carlo_get('partners'); ?>
  <div class="partners-nav">
    <?php foreach($partners as $partner => $value) : ?>
      <button class="partner-nav" data-index="<?= $partner ?>">
        <?= carlo_img($value['brand_logo'], '281x169') ?>
      </button>
    <?php endforeach ?>
  </div>
  
  <div class="partners-desc">
    <div class="partners-scroll">
      <?php foreach($partners as $partner => $value) : ?>
        <div class="partner">
          <div class="image">
            <?= carlo_img($value['image'], '1310x686') ?>
          </div>
          <div class="desc">
            <h3>
              <?= $value['title']; ?>
            </h3>
            <?= $value['description']; ?>
          </div>
        </div>
      <?php endforeach ?>
    </div>
  </div>
  
  <div class="actions">
    <?php carlo_render('components/cta', carlo_get('cta')); ?>
  </div>
</section>
