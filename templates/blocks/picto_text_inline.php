<section class="section picto-text">
  <h2 class="t2">
    <?= carlo_get('title'); ?>
  </h2>
  <?php if ($subtitle = carlo_get('subtitle')) : ?>
    <div class="intro">
      <p><?= $subtitle ?></p>
    </div>
  <?php endif ?>
  
  <div class="picto-text-list" role="list">
    <?php 
    $pictos = carlo_get('pictos');
    $img_size = count($pictos) > 3 ? '120x116' : '250x227';
    foreach($pictos as $picto): ?>
      <div class="picto-text-item">
        <div class="image">
          <?= carlo_img($picto['image'], $img_size) ?>
        </div>
        <h3 class="t3">
        <?= $picto['title'] ?>
        </h3>
        <?= $picto['description'] ?> 
      </div>
    <?php endforeach; ; ?>
  </div>
  
  <div class="actions">
    <?php carlo_render('components/cta', carlo_get('cta')); ?>
  </div>
  
</section>
