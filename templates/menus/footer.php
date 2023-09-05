<nav class="footer-links">
  <ul role="list">
    <?php foreach(carlo_get('items') as $item): ?>
      <li class="<?= $item->current ? 'active-trail' : '' ?>">
        <a href="<?= $item->href ?>"><?= $item->name ?></a>
      </li>
    <?php endforeach ?>
  </ul>
</nav>