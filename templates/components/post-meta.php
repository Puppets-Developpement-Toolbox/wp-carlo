<div class="actu-detail-infos">
  <span class="date">
    <?= get_the_date('d/m/Y') ?>
  </span>
  <?php $cats = get_the_category() ?>
  <?php if(count($cats) > 1) : ?>
    <span class="theme">
      <?=  $cats[1]->name ?>
    </span>
  <?php endif ?>
  <span class="author">
    <?= get_the_author_meta('display_name', get_post()->post_author) ?>
  </span>
</div>
<div class="actu-detail-socials" role="list">
  <a href="https://www.facebook.com/sharer/sharer.php?u=<?= get_permalink() ?>" target="_blank">
    <span class="picto">
      <svg viewBox="0 0 40.236 40.236"><use xlink:href="#svg-facebook-40x40"></use></svg>
    </span>
  </a>
  <a href="http://www.twitter.com/share?url=<?= get_permalink() ?>" target="_blank">
    <span class="picto">
      <svg viewBox="0 0 40.236 40.236"><use xlink:href="#svg-twitter-40x40"></use></svg>
    </span>
  </a>
  <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= get_permalink() ?>" target="_blank">
    <span class="picto">
      <svg viewBox="0 0 40.236 40.236"><use xlink:href="#svg-linkedin-40x40"></use></svg>
    </span>
  </a>
  <!-- <a href="#" target="_blank">
    <span class="picto">
      <svg viewBox="0 0 40.236 40.236"><use xlink:href="#svg-link-40x40"></use></svg>
    </span>
  </a> -->
</div>