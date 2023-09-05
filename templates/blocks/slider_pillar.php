<section class="section slider-pillar">
  <div class="desc">
    <h2 class="t2">
      <?= carlo_get('title'); ?>
    </h2>
    <h3 class="t3">
      <?= carlo_get('subtitle'); ?>
    </h3>
  </div>
  <div class="sliders">
    <?php $pillars = carlo_get('pillars'); ?>
    <?php foreach($pillars as $pillar): ?>
      <?php $pillar_texts = $pillar['pillar_texts']; ?>
      <div class="column-slider">
        <div class="col">
          <h4 class="t4">
            <?= $pillar['title']; ?>
          </h4>
        </div>
        <div class="col">
          <div class="line-slider">
            <?php foreach($pillar_texts as $pillar_text): ?>
              <div class="slide">
                <h5 class="t5">
                  <?= $pillar_text['title']; ?>
                </h5>
                <?= $pillar_text['description']; ?>
              </div>
            <?php endforeach;?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

