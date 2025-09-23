<?php
// Validate required collection parameter
if (!isset($collection) || !$collection) {
  if (kirby()->option('debug')) {
    throw new Exception('Collection parameter is required for collection-pagination snippet');
  } else {
    // In production, fail gracefully but return empty nav
    echo '<nav class="collection-pagination"><!-- Collection parameter missing --></nav>';
    return;
  }
}

// Validate that collection has pagination method
if (!method_exists($collection, 'pagination')) {
  if (kirby()->option('debug')) {
    throw new Exception('Collection must be a paginated collection (use ->paginate() method)');
  } else {
    // In production, fail gracefully but return empty nav
    echo '<nav class="collection-pagination"><!-- Collection not paginated --></nav>';
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

// Use the CollectionController's URL building method
use KirbyCollectionManager\CollectionController;
?>

<?php if ($collection->count() > 0) : ?>
<?php $pagination = $collection->pagination() ?>
<?php
// Only show pagination controls if there are actually multiple pages
$showPagination = $pagination->pages() > 1;
?>

<?php if ($showPagination) : ?>
<nav class="<?= $cssClasses['nav'] ?>" role="navigation" aria-label="Collection pagination">
  <ul>
    <!-- First Page Button -->
    <li class="<?= $cssClasses['item'] ?> <?= $cssClasses['item'] ?>--to-first<?= !$pagination->hasPrevPage() ? ' ' . $cssClasses['item'] . '--disabled' : '' ?>">
      <a href="<?= !$pagination->hasPrevPage() ? '#' : CollectionController::buildUrl($page, ['p' => null]) ?>"
         data-page="1"
         aria-label="<?= esc($firstPageText) ?><?= !$pagination->hasPrevPage() ? ' (disabled)' : '' ?>"
         <?= !$pagination->hasPrevPage() ? 'aria-disabled="true" tabindex="-1"' : '' ?>>
        <span class="<?= $cssClasses['icon'] ?> <?= $cssClasses['icon'] ?>--first" aria-hidden="true"></span>
        <span class="sr-only"><?= esc($firstPageText) ?><?= !$pagination->hasPrevPage() ? ' (disabled)' : '' ?></span>
      </a>
    </li>

    <!-- Previous Page Button -->
    <li class="<?= $cssClasses['item'] ?> <?= $cssClasses['item'] ?>--to-sibling<?= !$pagination->hasPrevPage() ? ' ' . $cssClasses['item'] . '--disabled' : '' ?>">
      <a href="<?= !$pagination->hasPrevPage() ? '#' : CollectionController::buildUrl($page, ['p' => $pagination->prevPage() > 1 ? $pagination->prevPage() : null]) ?>"
         data-page="<?= $pagination->prevPage() ?>"
         aria-label="<?= esc($prevPageText) ?><?= !$pagination->hasPrevPage() ? ' (disabled)' : '' ?>"
         <?= !$pagination->hasPrevPage() ? 'aria-disabled="true" tabindex="-1"' : '' ?>>
        <span class="<?= $cssClasses['icon'] ?> <?= $cssClasses['icon'] ?>--prev" aria-hidden="true"></span>
        <span class="sr-only"><?= esc($prevPageText) ?><?= !$pagination->hasPrevPage() ? ' (disabled)' : '' ?></span>
      </a>
    </li>

    <!-- Page Numbers -->
    <?php foreach ($pagination->range($range) as $r) : ?>
    <li class="<?= $cssClasses['item'] ?> <?= $cssClasses['item'] ?>--to-number">
      <a <?php
      echo attr([
        'href' => CollectionController::buildUrl($page, ['p' => $r > 1 ? $r : null]),
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

    <!-- Next Page Button -->
    <li class="<?= $cssClasses['item'] ?> <?= $cssClasses['item'] ?>--to-sibling<?= !$pagination->hasNextPage() ? ' ' . $cssClasses['item'] . '--disabled' : '' ?>">
      <a href="<?= !$pagination->hasNextPage() ? '#' : CollectionController::buildUrl($page, ['p' => $pagination->nextPage()]) ?>"
         data-page="<?= $pagination->nextPage() ?>"
         aria-label="<?= esc($nextPageText) ?><?= !$pagination->hasNextPage() ? ' (disabled)' : '' ?>"
         <?= !$pagination->hasNextPage() ? 'aria-disabled="true" tabindex="-1"' : '' ?>>
        <span class="<?= $cssClasses['icon'] ?> <?= $cssClasses['icon'] ?>--next" aria-hidden="true"></span>
        <span class="sr-only"><?= esc($nextPageText) ?><?= !$pagination->hasNextPage() ? ' (disabled)' : '' ?></span>
      </a>
    </li>

    <!-- Last Page Button -->
    <li class="<?= $cssClasses['item'] ?> <?= $cssClasses['item'] ?>--to-last<?= !$pagination->hasNextPage() ? ' ' . $cssClasses['item'] . '--disabled' : '' ?>">
      <a href="<?= !$pagination->hasNextPage() ? '#' : CollectionController::buildUrl($page, ['p' => $pagination->lastPage()]) ?>"
         data-page="<?= $pagination->lastPage() ?>"
         aria-label="<?= esc($lastPageText) ?><?= !$pagination->hasNextPage() ? ' (disabled)' : '' ?>"
         <?= !$pagination->hasNextPage() ? 'aria-disabled="true" tabindex="-1"' : '' ?>>
        <span class="<?= $cssClasses['icon'] ?> <?= $cssClasses['icon'] ?>--last" aria-hidden="true"></span>
        <span class="sr-only"><?= esc($lastPageText) ?><?= !$pagination->hasNextPage() ? ' (disabled)' : '' ?></span>
      </a>
    </li>
  </ul>
</nav>
<?php else : ?>
<nav class="<?= $cssClasses['nav'] ?> <?= $cssClasses['nav'] ?>--empty" role="navigation" aria-label="Collection pagination">
  <!-- No pagination needed for single page -->
</nav>
<?php endif ?>
<?php else: ?>
<nav class="<?= $cssClasses['nav'] ?> <?= $cssClasses['nav'] ?>--empty" role="navigation" aria-label="Collection pagination">
  <!-- No items to paginate -->
</nav>
<?php endif ?>
