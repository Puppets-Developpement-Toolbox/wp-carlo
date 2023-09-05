
<ul class="socials" role="list">
  <?php foreach(carlo_get('items') as $item): ?>
    <li>
      <a href="<?= $item['href'] ?>" target="_blank">
        <span class="picto">
          <svg viewBox="0 0 40.236 40.236"><use xlink:href="#svg-<?= strtolower($item['name']) ?>-40x40"></use></svg>
        </span>
      </a>
    </li>
  <?php endforeach ?>
</ul>