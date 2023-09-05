

<header class="main-header">
	<div class="header-top header-section">
    <a href="<?= get_home_url() ?>" class="logo">
      Logo
    </a>

    <nav class="main-nav-list" id="primary-navigation">
      <ul class="menu">
        <?php foreach(carlo_get('items') as $item): ?>
          <li class="<?= $item['current'] ? 'active-trail' : '' ?>">
            <a href="<?= $item['href'] ?>" class="js-roll-bold">
              <span><?= $item['name'] ?></span>
            </a>
            <?php if(count($item['children']) > 0) : ?>
              <div class="subnav">
                <ul>
                  <?php foreach($item['children'] as $child) : ?>
                    <li class="<?= $item['current'] ? 'active-trail' : '' ?>">
                      <a href="<?= $item['href'] ?>" class="js-roll-bold">
                        <span><?= $item['name'] ?></span>
                      </a>
                    </li>
                  <?php endforeach ?>
                </ul>
              </div>
            <?php endif ?>
          </li>
        <?php endforeach ?>
      </ul>
    </nav>
	</div>
</header>
