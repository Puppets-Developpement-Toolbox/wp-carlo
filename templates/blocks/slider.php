<section class="section slider-hero">
  <div class="hero-slider">
    <?php 
    $slides = carlo_get('slides');
    if(is_array($slides)) :
      foreach($slides as $slide): ?>
        <div class="slide">
          <div class="image-full-width">
            <?= carlo_img($slide['image'], '3000x150') ?>
          </div>
          <div class="content">
            <div class="middle">
              <h1 class="t1"> <!-- h1 only for 1st slide, h2 for the others -->
                <span>
                  <?= $slide['content']; ?>
                </span>
              </h1>
              <?php carlo_render('components/cta', $slide['cta']); ?>
            </div>
          </div>
        </div>
      <?php endforeach;
    endif ?>
  </div>
</section>
