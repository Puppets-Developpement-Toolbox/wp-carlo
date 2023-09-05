<div class="section actus-results" id="js-results">
  <div class="actus-list" role="list">
    <?php $cards = carlo_get('cards');
    foreach($cards as $card) :
      carlo_render('components/actu-list-element', [
        'post' => $card
      ]);
    endforeach ?>
  </div>
  
  <div class="actions" id="js-actions">
    <div class="loader">
      <div class="sk-circle" id="js-more-loader">
        <div class="sk-circle1 sk-child"></div>
        <div class="sk-circle2 sk-child"></div>
        <div class="sk-circle3 sk-child"></div>
        <div class="sk-circle4 sk-child"></div>
        <div class="sk-circle5 sk-child"></div>
        <div class="sk-circle6 sk-child"></div>
        <div class="sk-circle7 sk-child"></div>
        <div class="sk-circle8 sk-child"></div>
        <div class="sk-circle9 sk-child"></div>
        <div class="sk-circle10 sk-child"></div>
        <div class="sk-circle11 sk-child"></div>
        <div class="sk-circle12 sk-child"></div>
      </div>
    </div>
    
    <?php if (carlo_get('has_more')) : ?>
      <button type="button" class="btn" id="js-more-results" data-action="<?= admin_url('admin-ajax.php') ?>?action=archive_content&page=<?= carlo_get('page') + 1 ?>">
        <span>
          Voir plus
        </span>
      </button>
    <?php endif ?>
  </div>
</div>