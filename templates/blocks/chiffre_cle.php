<section class="section chiffre-cle">
  <h2 class="t2"><?php echo carlo_get('title'); ?></h2>
  <h2 class="t2"><?php echo carlo_get('subtitle'); ?></h2>

  <?php $chiffres = carlo_get('chiffre'); ?>
  <?php foreach ($chiffres as $chiffre) : ?>
    <h3>
      <?php echo $chiffre['title']; ?>
    </h3>
    <?php echo $chiffre['subtitle']; ?>
  <?php endforeach; ?>
</section>