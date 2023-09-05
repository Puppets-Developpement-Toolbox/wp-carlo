<section class="section workflow">
  <h2 class="t2"><?= carlo_get('title') ?></h2>
  <div class="intro">
    <p><?= carlo_get('subtitle') ?></p>
  </div>
  <?php $steps = carlo_get('steps') ?>
  <div class="flow-list" role="list">
    <div class="flow">
      <div class="image">
        <?php $carlo_img_url = get_theme_root_uri().'/carlo/img/' ?>
        <picture>
          <source 
            srcset="<?= $carlo_img_url ?>/workflow-01_mobile.svg" 
            media="(max-width: 500px)"
          >
          <source 
            srcset="<?= $carlo_img_url ?>/workflow-01_desktop.svg" 
            media="(min-width: 501px) and (max-width:1023px)"
          >
          <source 
            srcset="<?= $carlo_img_url ?>/workflow-01_desktop.svg" 
            media="(min-width: 1024px)"
          >
          <img src="<?= $carlo_img_url ?>/workflow-01_mobile.svg" alt="1">
        </picture>
      </div>
      <div class="desc">
        <h3 class="t3">
          <?= $steps[0]['title'] ?>
        </h3>
        <p><?= $steps[0]['subtitle'] ?></p>
      </div>
    </div>
    <div class="flow">
      <div class="image">
        <picture>
          <source 
            srcset="<?= $carlo_img_url ?>/workflow-02_mobile.svg" 
            media="(max-width: 500px)"
          >
          <source 
            srcset="<?= $carlo_img_url ?>/workflow-02_desktop.svg" 
            media="(min-width: 501px) and (max-width:1023px)"
          >
          <source 
            srcset="<?= $carlo_img_url ?>/workflow-02_desktop.svg" 
            media="(min-width: 1024px)"
          >
          <img src="<?= $carlo_img_url ?>/workflow-02_mobile.svg" alt="2">
        </picture>
      </div>
      <div class="desc">
        <h3 class="t3">
          <?= $steps[1]['title'] ?>
        </h3>
        <p><?= $steps[1]['subtitle'] ?></p>
      </div>
    </div>
    <div class="flow">
      <div class="image">
        <picture>
          <source 
            srcset="<?= $carlo_img_url ?>/workflow-03_mobile.svg" 
            media="(max-width: 500px)"
          >
          <source 
            srcset="<?= $carlo_img_url ?>/workflow-03_desktop.svg" 
            media="(min-width: 501px) and (max-width:1023px)"
          >
          <source 
            srcset="<?= $carlo_img_url ?>/workflow-03_desktop.svg" 
            media="(min-width: 1024px)"
          >
          <img src="<?= $carlo_img_url ?>/workflow-03_mobile.svg" alt="3">
        </picture>
      </div>
      <div class="desc">
        <h3 class="t3">
          <?= $steps[2]['title'] ?>
        </h3>
        <p><?= $steps[2]['subtitle'] ?></p>
      </div>
    </div>
    <div class="flow">
      <div class="image">
        <picture>
          <source 
            srcset="<?= $carlo_img_url ?>/workflow-04_mobile.svg" 
            media="(max-width: 500px)"
          >
          <source 
            srcset="<?= $carlo_img_url ?>/workflow-04_desktop.svg" 
            media="(min-width: 501px) and (max-width:1023px)"
          >
          <source 
            srcset="<?= $carlo_img_url ?>/workflow-04_desktop.svg" 
            media="(min-width: 1024px)"
          >
          <img src="<?= $carlo_img_url ?>/workflow-04_mobile.svg" alt="4">
        </picture>
      </div>
      <div class="desc">
        <h3 class="t3">
          <?= $steps[3]['title'] ?>
        </h3>
        <p><?= $steps[3]['subtitle'] ?></p>
      </div>
    </div>
  </div>
  
  <div class="actions">
    <?php carlo_render('components/cta', carlo_get('cta')) ?>
  </div>
</section>