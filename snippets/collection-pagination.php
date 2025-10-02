<?php

/**
 * Collection Manager - Pagination Snippet
 * Pure presentation template - all logic handled in controller
 *
 * Available variables (from controller):
 * - $shouldShowPagination: Whether to show pagination
 * - $cssClasses: CSS class configuration
 * - $hasPrevPage, $hasNextPage: Navigation state
 * - $currentPage, $totalPages, $rangePages: Pagination data
 * - $firstPageUrl, $prevPageUrl, $nextPageUrl, $lastPageUrl: Navigation URLs
 * - $pageUrls: Array of page number URLs
 * - $firstPageLabel, $prevPageLabel, etc.: Accessibility labels
 * - $firstPageClasses, $prevPageClasses, etc.: CSS classes for buttons
 */

?>

<?php if (!$shouldShowPagination) : ?>
<nav class="collection-pagination collection-pagination--empty"></nav>
<?php else : ?>
<nav
  <?php echo attr(['class' => $cssClasses['nav'], 'role' => 'navigation', 'aria-label' => 'Collection pagination']) ?>>
  <ul>
    <!-- First Page Button -->
    <li <?php echo attr(['class' => $firstPageClasses]) ?>>
      <a <?= attr([
      'href' => $firstPageUrl,
      'data-page' => '1',
      'aria-label' => $firstPageLabel,
      'aria-disabled' => !$hasPrevPage ? 'true' : null,
      'tabindex' => !$hasPrevPage ? '-1' : null
]) ?>>
        <span
          <?php echo attr(['class' => $cssClasses['icon'] . ' ' . $cssClasses['icon'] . '--first', 'aria-hidden' => 'true']) ?>></span>
        <span <?php echo attr(['class' => 'sr-only']) ?>><?php echo esc($firstPageLabel, 'html') ?></span>
      </a>
    </li>

    <!-- Previous Page Button -->
    <li <?php echo attr(['class' => $prevPageClasses]) ?>>
      <a <?php echo attr([
      'href' => $prevPageUrl,
      'data-page' => $hasPrevPage ? $currentPage - 1 : 1,
      'aria-label' => $prevPageLabel,
      'aria-disabled' => !$hasPrevPage ? 'true' : null,
      'tabindex' => !$hasPrevPage ? '-1' : null
]) ?>>
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
    ?>
      <a <?php echo attr([
      'href' => $pageUrls[$pageNum],
      'data-page' => $pageNum,
      'aria-current' => $isCurrentPage ? 'page' : null,
      'tabindex' => $isCurrentPage ? '-1' : null,
      'aria-label' => $pageLabel
]) ?>>
        <?php echo esc($pageNum, 'html') ?>
      </a>
    </li>
    <?php endforeach ?>

    <!-- Next Page Button -->
    <li <?php echo attr(['class' => $nextPageClasses]) ?>>
      <a <?php echo attr([
      'href' => $nextPageUrl,
      'data-page' => $hasNextPage ? $currentPage + 1 : $totalPages,
      'aria-label' => $nextPageLabel,
      'aria-disabled' => !$hasNextPage ? 'true' : null,
      'tabindex' => !$hasNextPage ? '-1' : null
]) ?>>
        <span
          <?php echo attr(['class' => $cssClasses['icon'] . ' ' . $cssClasses['icon'] . '--next', 'aria-hidden' => 'true']) ?>></span>
        <span <?php echo attr(['class' => 'sr-only']) ?>><?php echo esc($nextPageLabel, 'html') ?></span>
      </a>
    </li>

    <!-- Last Page Button -->
    <li <?php echo attr(['class' => $lastPageClasses]) ?>>
      <a <?php echo attr([
      'href' => $lastPageUrl,
      'data-page' => $totalPages,
      'aria-label' => $lastPageLabel,
      'aria-disabled' => !$hasNextPage ? 'true' : null,
      'tabindex' => !$hasNextPage ? '-1' : null
]) ?>>
        <span
          <?php echo attr(['class' => $cssClasses['icon'] . ' ' . $cssClasses['icon'] . '--last', 'aria-hidden' => 'true']) ?>></span>
        <span <?php echo attr(['class' => 'sr-only']) ?>><?php echo esc($lastPageLabel, 'html') ?></span>
      </a>
    </li>
  </ul>
</nav>
<?php endif ?>
<?php
