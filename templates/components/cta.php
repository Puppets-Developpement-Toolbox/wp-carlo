<?php 
$link = carlo_get('link');
$label = carlo_get('label');
if($label && $link) : ?>
<a href="<?= $link ?>" class="btn">
  <span><?= $label ?></span>
</a>
<?php endif ?>