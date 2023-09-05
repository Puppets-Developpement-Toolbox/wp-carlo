<div class="section breadcrumb">
  <nav class="breadcrumb-nav">
      <?php 
      $items = carlo_get('items');
      $separator = '<span class="separator"><svg viewBox="0 0 9.773 9.813"><use xlink:href="#svg-chevron-right"></use></svg></span>';
      $links = array_map(function($item) {
        $link = '';
        if(isset($item[1])) $link .= '<a href="'.$item[1].'">';
        else $link .= '<span class="current">';
        $link .= $item[0];
        if(isset($item[1])) $link .= '</a>';
        else $link .= '</span>';
        return $link;
      }, $items);
      echo implode($separator, $links);
    ?>
  </nav>
</div>