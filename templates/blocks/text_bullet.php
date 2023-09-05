<section class="section text-bullet">
  <h2 class="t2"><?= carlo_get('title') ?></h2>
  <div class="bullet-columns">
    <ul>
      <?php foreach(carlo_get('items') as $item): ?>
        <li><?= $item['item'] ?></li>
      <?php endforeach ?>
    </ul>
  </div>
</section>