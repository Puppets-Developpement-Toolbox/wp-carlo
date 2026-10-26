<?php $pages = carlo_get('pages'); ?>
<section class="[ section ]
                laptop:grid laptop:grid-cols-12">
  <div class="laptop:col-span-10 laptop:col-start-2">
    <h2 class="[ h4 ]"><?= carlo_get('titre') ?></h2>
    <?php if (!empty($pages)): ?>
      <ul>
        <?php foreach ($pages as $page): ?>
          <li><a href="<?= esc_url(get_permalink($page)) ?>"><?= esc_html(get_the_title($page)) ?></a></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>
