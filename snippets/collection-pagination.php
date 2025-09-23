<?php
// Validate required collection parameter
if (!isset($collection) || !$collection) {
  if (kirby()->option('debug')) {
    throw new Exception('Collection parameter is required for collection-pagination snippet');
  } else {
    // In production, fail gracefully
    return;
  }
}

// Validate that collection has pagination method
if (!method_exists($collection, 'pagination')) {
  if (kirby()->option('debug')) {
    throw new Exception('Collection must be a paginated collection (use ->paginate() method)');
  } else {
    return;
  }
}

// Get plugin configuration with proper fallbacks
$config = kirby()->option('shallowred.collection-manager', []);
$paginationConfig = is_array($config['pagination'] ?? null) ? $config['pagination'] : [];
$texts = is_array($config['texts'] ?? null) ? $config['texts'] : [];

// Set default values with validation
$range = isset($range) ? (int) $range : (int) ($paginationConfig['range'] ?? 10);
$range = max(1, min($range, 50)); // Clamp between 1 and 50

$cssClasses = array_merge([
  'nav' => 'collection-pagination',
  'item' => 'collection-pagination__item',
  'icon' => 'collection-pagination__icon',
], is_array($paginationConfig['cssClasses'] ?? null) ? $paginationConfig['cssClasses'] : []);

// Text configuration with fallbacks and HTML escaping
$firstPageText = esc($texts['firstPage'] ?? 'Go to first page');
$prevPageText = esc($texts['prevPage'] ?? 'Go to previous page');
$nextPageText = esc($texts['nextPage'] ?? 'Go to next page');
$lastPageText = esc($texts['lastPage'] ?? 'Go to last page');

// Validate CSS classes
foreach ($cssClasses as $key => $class) {
  if (!is_string($class) || empty(trim($class))) {
    $cssClasses[$key] = 'collection-pagination__' . $key;
  }
}
?>

<nav class="<?= $cssClasses['nav'] ?>" role="navigation" aria-label="Collection pagination">
  <ul>
  <?php if ($collection->count() > 0) : ?>
  <?php $pagination = $collection->pagination() ?>
    <?php
    // Only show pagination controls if there are actually multiple pages
    $showPagination = $pagination->pages() > 1;
    ?>
    <?php if ($showPagination && $pagination->hasPrevPage()) : ?>
    <li class="<?= $cssClasses['item'] ?> <?= $cssClasses['item'] ?>--to-first">
      <a href="<?= $pagination->firstPageURL() ?>" data-page="1" aria-label="<?= esc($firstPageText) ?>">
        <span class="<?= $cssClasses['icon'] ?> <?= $cssClasses['icon'] ?>--first" aria-hidden="true"></span>
        <span class="sr-only"><?= esc($firstPageText) ?></span>
      </a>
    </li>
    <li class="<?= $cssClasses['item'] ?> <?= $cssClasses['item'] ?>--to-sibling">
      <a href="<?= $pagination->prevPageURL() ?>" data-page="<?= $pagination->prevPage() ?>" aria-label="<?= esc($prevPageText) ?>">
        <span class="<?= $cssClasses['icon'] ?> <?= $cssClasses['icon'] ?>--prev" aria-hidden="true"></span>
        <span class="sr-only"><?= esc($prevPageText) ?></span>
      </a>
    </li>
    <?php endif ?>

    <?php if ($showPagination) : ?>
    <?php foreach ($pagination->range($range) as $r) : ?>
    <li class="<?= $cssClasses['item'] ?> <?= $cssClasses['item'] ?>--to-number">
      <a <?php
      echo attr([
        'href' => $pagination->pageURL($r),
        'data-page' => $r,
        'aria-current' => $pagination->page() === $r ? 'page' : null,
        'aria-label' => $pagination->page() === $r ? 'Current page, page ' . $r : 'Go to page ' . $r,
        'tabindex' => $pagination->page() === $r ? '-1' : null
      ])
      ?>>
        <?= $r ?>
      </a>
    </li>
    <?php endforeach ?>
    <?php endif ?>

    <?php if ($showPagination && $pagination->hasNextPage()) : ?>
    <li class="<?= $cssClasses['item'] ?> <?= $cssClasses['item'] ?>--to-sibling">
      <a href="<?= $pagination->nextPageURL() ?>" data-page="<?= $pagination->nextPage() ?>" aria-label="<?= esc($nextPageText) ?>">
        <span class="<?= $cssClasses['icon'] ?> <?= $cssClasses['icon'] ?>--next" aria-hidden="true"></span>
        <span class="sr-only"><?= esc($nextPageText) ?></span>
      </a>
    </li>
    <li class="<?= $cssClasses['item'] ?> <?= $cssClasses['item'] ?>--to-last">
      <a href="<?= $pagination->lastPageURL() ?>" data-page="<?= $pagination->lastPage() ?>" aria-label="<?= esc($lastPageText) ?>">
        <span class="<?= $cssClasses['icon'] ?> <?= $cssClasses['icon'] ?>--last" aria-hidden="true"></span>
        <span class="sr-only"><?= esc($lastPageText) ?></span>
      </a>
    </li>
    <?php endif ?>
  <?php else: ?>
    <li class="<?= $cssClasses['item'] ?> <?= $cssClasses['item'] ?>--empty">
      <span>No items to paginate</span>
    </li>
  <?php endif ?>
  </ul>
</nav>
