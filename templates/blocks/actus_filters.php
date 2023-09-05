<div class="section actus-filters">
  <button class="btn small open-filters-mobile" data-default="Afficher les filtres" data-active="Masquer les filtres">
    <span>Afficher les filtres</span>
  </button>
  <form id="js-filters-form"> <!-- id="js-filters-form" used by javascript ajax -->
    <fieldset class="filters">
      <div class="js-form-type-radios">
        <input type="radio" name="cat" id="all" value="" checked>
        <label tabindex="0" for="all">Tous les contenus</label>
        
        <?php
        $cats = get_categories();
        foreach($cats as $cat): ?>
          <input type="radio" name="cat" id="cat-<?= $cat->term_id ?>" value="<?= $cat->term_id ?>">
          <label tabindex="0" for="cat-<?= $cat->term_id ?>"><?= $cat->name ?></label>
        <?php endforeach ?>
      </div>
    </fieldset>
  </form>
</div>