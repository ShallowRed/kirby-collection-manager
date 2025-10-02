<?php
/**
 * Collection Manager - Pagination Snippet
 * Simple presentation-focused snippet
 *
 * Available variables:
 * - $pagination: Pagination object
 * - $showPagination: Whether to show pagination
 * - $range: Range for page numbers
 * - $page: Current page object
 */

use KirbyCollectionManager\CollectionController;

if (!$showPagination || ($pagination && $pagination->limit() > 0 && $pagination->total() === 0)) {
    echo '<nav class="collection-pagination collection-pagination--empty"></nav>';
    return;
}

// Get the configured pagination parameter name
$paginationParam = $config['pagination']['param'] ?? 'p';

$cssClasses = [
    'nav' => 'collection-pagination',
    'item' => 'collection-pagination__item',
    'icon' => 'collection-pagination__icon'
];
?>

<nav class="<?= $cssClasses['nav'] ?>" role="navigation" aria-label="Collection pagination">
  <ul>
    <!-- First Page Button -->
    <li class="<?= $cssClasses['item'] ?> <?= $cssClasses['item'] ?>--to-first<?= !$pagination->hasPrevPage() ? ' ' . $cssClasses['item'] . '--disabled' : '' ?>">
      <a href="<?= !$pagination->hasPrevPage() ? '#' : CollectionController::buildUrl($page, [$paginationParam => null], $paginationParam) ?>"
         data-page="1"
         aria-label="Go to first page<?= !$pagination->hasPrevPage() ? ' (disabled)' : '' ?>"
         <?= !$pagination->hasPrevPage() ? 'aria-disabled="true" tabindex="-1"' : '' ?>>
        <span class="<?= $cssClasses['icon'] ?> <?= $cssClasses['icon'] ?>--first" aria-hidden="true"></span>
        <span class="sr-only">Go to first page<?= !$pagination->hasPrevPage() ? ' (disabled)' : '' ?></span>
      </a>
    </li>

    <!-- Previous Page Button -->
    <li class="<?= $cssClasses['item'] ?> <?= $cssClasses['item'] ?>--to-sibling<?= !$pagination->hasPrevPage() ? ' ' . $cssClasses['item'] . '--disabled' : '' ?>">
      <a href="<?= !$pagination->hasPrevPage() ? '#' : CollectionController::buildUrl($page, [$paginationParam => $pagination->prevPage() > 1 ? $pagination->prevPage() : null], $paginationParam) ?>"
         data-page="<?= $pagination->prevPage() ?>"
         aria-label="Go to previous page<?= !$pagination->hasPrevPage() ? ' (disabled)' : '' ?>"
         <?= !$pagination->hasPrevPage() ? 'aria-disabled="true" tabindex="-1"' : '' ?>>
        <span class="<?= $cssClasses['icon'] ?> <?= $cssClasses['icon'] ?>--prev" aria-hidden="true"></span>
        <span class="sr-only">Go to previous page<?= !$pagination->hasPrevPage() ? ' (disabled)' : '' ?></span>
      </a>
    </li>

    <!-- Page Numbers -->
    <?php foreach ($pagination->range($range) as $r) : ?>
    <li class="<?= $cssClasses['item'] ?> <?= $cssClasses['item'] ?>--to-number">
      <a href="<?= CollectionController::buildUrl($page, [$paginationParam => $r > 1 ? $r : null], $paginationParam) ?>"
         data-page="<?= $r ?>"
         <?= $pagination->page() === $r ? 'aria-current="page" tabindex="-1"' : '' ?>
         aria-label="<?= $pagination->page() === $r ? 'Current page, page ' . $r : 'Go to page ' . $r ?>">
        <?= $r ?>
      </a>
    </li>
    <?php endforeach ?>

    <!-- Next Page Button -->
    <li class="<?= $cssClasses['item'] ?> <?= $cssClasses['item'] ?>--to-sibling<?= !$pagination->hasNextPage() ? ' ' . $cssClasses['item'] . '--disabled' : '' ?>">
      <a href="<?= !$pagination->hasNextPage() ? '#' : CollectionController::buildUrl($page, [$paginationParam => $pagination->nextPage()], $paginationParam) ?>"
         data-page="<?= $pagination->nextPage() ?>"
         aria-label="Go to next page<?= !$pagination->hasNextPage() ? ' (disabled)' : '' ?>"
         <?= !$pagination->hasNextPage() ? 'aria-disabled="true" tabindex="-1"' : '' ?>>
        <span class="<?= $cssClasses['icon'] ?> <?= $cssClasses['icon'] ?>--next" aria-hidden="true"></span>
        <span class="sr-only">Go to next page<?= !$pagination->hasNextPage() ? ' (disabled)' : '' ?></span>
      </a>
    </li>

    <!-- Last Page Button -->
    <li class="<?= $cssClasses['item'] ?> <?= $cssClasses['item'] ?>--to-last<?= !$pagination->hasNextPage() ? ' ' . $cssClasses['item'] . '--disabled' : '' ?>">
      <a href="<?= !$pagination->hasNextPage() ? '#' : CollectionController::buildUrl($page, [$paginationParam => $pagination->lastPage()], $paginationParam) ?>"
         data-page="<?= $pagination->lastPage() ?>"
         aria-label="Go to last page<?= !$pagination->hasNextPage() ? ' (disabled)' : '' ?>"
         <?= !$pagination->hasNextPage() ? 'aria-disabled="true" tabindex="-1"' : '' ?>>
        <span class="<?= $cssClasses['icon'] ?> <?= $cssClasses['icon'] ?>--last" aria-hidden="true"></span>
        <span class="sr-only">Go to last page<?= !$pagination->hasNextPage() ? ' (disabled)' : '' ?></span>
      </a>
    </li>
  </ul>
</nav>
