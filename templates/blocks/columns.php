<?php 
$columns = carlo_get('columns');
$has_pictos = !empty(array_filter(array_column($columns, 'image')));
?>

<section class="section columns cols-<?= count($columns) ?>">
  <h2 class="t2"><?php echo carlo_get('title'); ?></h2>
  
  <div class="cols">
    <?php foreach($columns as $column): ?>
    <div class="col">
      <?php if(!empty($column['image'])) : ?>
        <div class="image">
          <?= carlo_img($column['image'], '100x100') ?>
        </div>
      <?php endif ?>
      <?php if(!empty($column['title'])) : ?>
        <h3 class="t3">
          <?= $column['title']; ?>
        </h3>
      <?php endif ?>
      <?php if(!empty($column['subtitle'])) : ?>
        <h4 class="t4">
          <?= $column['subtitle']; ?>
        </h4>
      <?php endif; ?>
      
      <?= $column['description']; ?> 
    </div>
    <?php endforeach; ?>
  </div>
</section>