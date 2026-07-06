<?php

/**
 * Collection Manager - Pagination Snippet
 * Uses htmx for AJAX pagination
 */

// Defensive defaults for when controller doesn't run
$config = $config ?? [];
$shouldShowPagination = $shouldShowPagination ?? false;
$cssClasses = $cssClasses ?? ['nav' => 'collection-pagination', 'item' => 'collection-pagination__item', 'icon' => 'collection-pagination__icon'];
$hasPrevPage = $hasPrevPage ?? false;
$hasNextPage = $hasNextPage ?? false;
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$rangePages = $rangePages ?? [1];
$firstPageUrl = $firstPageUrl ?? '#';
$prevPageUrl = $prevPageUrl ?? '#';
$nextPageUrl = $nextPageUrl ?? '#';
$lastPageUrl = $lastPageUrl ?? '#';
$pageUrls = $pageUrls ?? [];
$firstPageLabel = $firstPageLabel ?? 'First page';
$prevPageLabel = $prevPageLabel ?? 'Previous page';
$nextPageLabel = $nextPageLabel ?? 'Next page';
$lastPageLabel = $lastPageLabel ?? 'Last page';
$firstPageClasses = $firstPageClasses ?? $cssClasses['item'];
$prevPageClasses = $prevPageClasses ?? $cssClasses['item'];
$nextPageClasses = $nextPageClasses ?? $cssClasses['item'];
$lastPageClasses = $lastPageClasses ?? $cssClasses['item'];

$htmxEnabled = $config['enableJs'] ?? true;
$instanceId = $config['instanceId'] ?? 'collection';
$htmxTarget = '#' . $instanceId . '-content';
$htmxSwap = 'innerHTML show:window:top';

?>

<?php if (!$shouldShowPagination) : ?>
<nav class="collection-pagination collection-pagination--empty"></nav>
<?php else : ?>
<nav
  <?php echo attr(['class' => $cssClasses['nav'], 'role' => 'navigation', 'aria-label' => 'Collection pagination', 'data-testid' => 'collection-pagination']) ?>>
  <ul>
    <!-- First Page Button -->
    <li <?php echo attr(['class' => $firstPageClasses]) ?>>
      <a <?= attr(array_filter([
      'href' => $firstPageUrl,
      'data-page' => '1',
      'aria-label' => $firstPageLabel,
      'data-testid' => 'collection-pagination-first',
      'aria-disabled' => !$hasPrevPage ? 'true' : null,
      'tabindex' => !$hasPrevPage ? '-1' : null,
      'hx-get' => $htmxEnabled && $hasPrevPage ? $firstPageUrl . (strpos($firstPageUrl, '?') !== false ? '&' : '?') . 'htmx=' . rawurlencode($instanceId) : null,
      'hx-target' => $htmxEnabled && $hasPrevPage ? $htmxTarget : null,
      'hx-swap' => $htmxEnabled && $hasPrevPage ? $htmxSwap : null,
      'hx-push-url' => $htmxEnabled && $hasPrevPage ? $firstPageUrl : null
])) ?>>
        <span
          <?php echo attr(['class' => $cssClasses['icon'] . ' ' . $cssClasses['icon'] . '--first', 'aria-hidden' => 'true']) ?>></span>
        <span <?php echo attr(['class' => 'sr-only']) ?>><?php echo esc($firstPageLabel, 'html') ?></span>
      </a>
    </li>

    <!-- Previous Page Button -->
    <li <?php echo attr(['class' => $prevPageClasses]) ?>>
      <a <?php echo attr(array_filter([
      'href' => $prevPageUrl,
      'data-page' => $hasPrevPage ? $currentPage - 1 : 1,
      'aria-label' => $prevPageLabel,
      'data-testid' => 'collection-pagination-prev',
      'aria-disabled' => !$hasPrevPage ? 'true' : null,
      'tabindex' => !$hasPrevPage ? '-1' : null,
      'hx-get' => $htmxEnabled && $hasPrevPage ? $prevPageUrl . (strpos($prevPageUrl, '?') !== false ? '&' : '?') . 'htmx=' . rawurlencode($instanceId) : null,
      'hx-target' => $htmxEnabled && $hasPrevPage ? $htmxTarget : null,
      'hx-swap' => $htmxEnabled && $hasPrevPage ? $htmxSwap : null,
      'hx-push-url' => $htmxEnabled && $hasPrevPage ? $prevPageUrl : null
])) ?>>
        <span
          <?php echo attr(['class' => $cssClasses['icon'] . ' ' . $cssClasses['icon'] . '--prev', 'aria-hidden' => 'true']) ?>></span>
        <span <?php echo attr(['class' => 'sr-only']) ?>><?php echo esc($prevPageLabel, 'html') ?></span>
      </a>
    </li>

    <!-- Page Numbers -->
    <?php foreach ($rangePages as $pageNum) : ?>
    <li <?php echo attr(['class' => $cssClasses['item'] . ' ' . $cssClasses['item'] . '--to-number']) ?>>
      <?php
    $pageLabel = $currentPage === $pageNum ? 'Current page, page ' . $pageNum : 'Go to page ' . $pageNum;
    $isCurrentPage = $currentPage === $pageNum;
    $pageUrl = $pageUrls[$pageNum];
    ?>
      <a <?php echo attr(array_filter([
      'href' => $pageUrl,
      'data-page' => $pageNum,
      'aria-current' => $isCurrentPage ? 'page' : null,
      'data-testid' => 'collection-pagination-page-' . $pageNum,
      'tabindex' => $isCurrentPage ? '-1' : null,
      'aria-label' => $pageLabel,
      'hx-get' => $htmxEnabled && !$isCurrentPage ? $pageUrl . (strpos($pageUrl, '?') !== false ? '&' : '?') . 'htmx=' . rawurlencode($instanceId) : null,
      'hx-target' => $htmxEnabled && !$isCurrentPage ? $htmxTarget : null,
      'hx-swap' => $htmxEnabled && !$isCurrentPage ? $htmxSwap : null,
      'hx-push-url' => $htmxEnabled && !$isCurrentPage ? $pageUrl : null
])) ?>>
        <?php echo esc($pageNum, 'html') ?>
      </a>
    </li>
    <?php endforeach ?>

    <!-- Next Page Button -->
    <li <?php echo attr(['class' => $nextPageClasses]) ?>>
      <a <?php echo attr(array_filter([
      'href' => $nextPageUrl,
      'data-page' => $hasNextPage ? $currentPage + 1 : $totalPages,
      'aria-label' => $nextPageLabel,
      'data-testid' => 'collection-pagination-next',
      'aria-disabled' => !$hasNextPage ? 'true' : null,
      'tabindex' => !$hasNextPage ? '-1' : null,
      'hx-get' => $htmxEnabled && $hasNextPage ? $nextPageUrl . (strpos($nextPageUrl, '?') !== false ? '&' : '?') . 'htmx=' . rawurlencode($instanceId) : null,
      'hx-target' => $htmxEnabled && $hasNextPage ? $htmxTarget : null,
      'hx-swap' => $htmxEnabled && $hasNextPage ? $htmxSwap : null,
      'hx-push-url' => $htmxEnabled && $hasNextPage ? $nextPageUrl : null
])) ?>>
        <span
          <?php echo attr(['class' => $cssClasses['icon'] . ' ' . $cssClasses['icon'] . '--next', 'aria-hidden' => 'true']) ?>></span>
        <span <?php echo attr(['class' => 'sr-only']) ?>><?php echo esc($nextPageLabel, 'html') ?></span>
      </a>
    </li>

    <!-- Last Page Button -->
    <li <?php echo attr(['class' => $lastPageClasses]) ?>>
      <a <?php echo attr(array_filter([
      'href' => $lastPageUrl,
      'data-page' => $totalPages,
      'aria-label' => $lastPageLabel,
      'data-testid' => 'collection-pagination-last',
      'aria-disabled' => !$hasNextPage ? 'true' : null,
      'tabindex' => !$hasNextPage ? '-1' : null,
      'hx-get' => $htmxEnabled && $hasNextPage ? $lastPageUrl . (strpos($lastPageUrl, '?') !== false ? '&' : '?') . 'htmx=' . rawurlencode($instanceId) : null,
      'hx-target' => $htmxEnabled && $hasNextPage ? $htmxTarget : null,
      'hx-swap' => $htmxEnabled && $hasNextPage ? $htmxSwap : null,
      'hx-push-url' => $htmxEnabled && $hasNextPage ? $lastPageUrl : null
])) ?>>
        <span
          <?php echo attr(['class' => $cssClasses['icon'] . ' ' . $cssClasses['icon'] . '--last', 'aria-hidden' => 'true']) ?>></span>
        <span <?php echo attr(['class' => 'sr-only']) ?>><?php echo esc($lastPageLabel, 'html') ?></span>
      </a>
    </li>
  </ul>
</nav>
<?php endif ?>
