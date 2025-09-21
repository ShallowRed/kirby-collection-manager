<?php
// Validate required collection parameter
if (!isset($collection) || !$collection) {
  throw new Exception('Collection parameter is required for collection-pagination snippet');
}

// Get plugin configuration
$config = kirby()->option('shallowred.collection-manager', []);
$paginationConfig = $config['pagination'] ?? [];
$texts = $config['texts'] ?? [];

// Set default values
$range = $range ?? $paginationConfig['range'] ?? 10;
$cssClasses = array_merge([
  'nav' => 'collection-pagination',
  'item' => 'collection-pagination__item',
  'icon' => 'collection-pagination__icon',
], $paginationConfig['cssClasses'] ?? []);

// Text configuration with fallbacks
$firstPageText = $texts['firstPage'] ?? 'Go to first page';
$prevPageText = $texts['prevPage'] ?? 'Go to previous page';
$nextPageText = $texts['nextPage'] ?? 'Go to next page';
$lastPageText = $texts['lastPage'] ?? 'Go to last page';
?>

<nav class="<?= $cssClasses['nav'] ?>" role="navigation" aria-label="Collection pagination">
  <ul>
  <?php if ($collection->count() > 0) : ?>
  <?php $pagination = $collection->pagination() ?>
    <?php if ($pagination->hasPrevPage()) : ?>
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

    <?php if ($pagination->hasNextPage()) : ?>
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
