<?php

use KirbyCollectionManager\CollectionController;

return function (...$data) {

  extract($data);

  // Get the configured pagination parameter name
  $paginationParam = $config['pagination']['param'] ?? 'p';

  // Check if pagination should be shown
  $shouldShowPagination = $showPagination && $pagination && !($pagination->limit() > 0 && $pagination->total() === 0);

  // CSS classes configuration
  $cssClasses = [
    'nav' => 'collection-pagination',
    'item' => 'collection-pagination__item',
    'icon' => 'collection-pagination__icon'
  ];

  // Pagination state
  $hasPrevPage = $pagination->hasPrevPage();
  $hasNextPage = $pagination->hasNextPage();
  $currentPage = $pagination->page();
  $totalPages = $pagination->pages();
  $rangePages = $pagination->range($range ?? 5);

  // Generate URLs for navigation
  $firstPageUrl = !$hasPrevPage ? '#' : CollectionController::buildUrl($page, [$paginationParam => null], $paginationParam);
  $prevPageUrl = !$hasPrevPage ? '#' : CollectionController::buildUrl($page, [$paginationParam => $pagination->prevPage() > 1 ? $pagination->prevPage() : null], $paginationParam);
  $nextPageUrl = !$hasNextPage ? '#' : CollectionController::buildUrl($page, [$paginationParam => $pagination->nextPage()], $paginationParam);
  $lastPageUrl = !$hasNextPage ? '#' : CollectionController::buildUrl($page, [$paginationParam => $pagination->lastPage()], $paginationParam);

  // Generate page number URLs
  $pageUrls = [];
  foreach ($rangePages as $pageNum) {
    $pageUrls[$pageNum] = CollectionController::buildUrl($page, [$paginationParam => $pageNum > 1 ? $pageNum : null], $paginationParam);
  }

  // Accessibility labels
  $firstPageLabel = 'Go to first page' . (!$hasPrevPage ? ' (disabled)' : '');
  $prevPageLabel = 'Go to previous page' . (!$hasPrevPage ? ' (disabled)' : '');
  $nextPageLabel = 'Go to next page' . (!$hasNextPage ? ' (disabled)' : '');
  $lastPageLabel = 'Go to last page' . (!$hasNextPage ? ' (disabled)' : '');

  // Button states for CSS classes
  $firstPageClasses = $cssClasses['item'] . ' ' . $cssClasses['item'] . '--to-first' . (!$hasPrevPage ? ' ' . $cssClasses['item'] . '--disabled' : '');
  $prevPageClasses = $cssClasses['item'] . ' ' . $cssClasses['item'] . '--to-sibling' . (!$hasPrevPage ? ' ' . $cssClasses['item'] . '--disabled' : '');
  $nextPageClasses = $cssClasses['item'] . ' ' . $cssClasses['item'] . '--to-sibling' . (!$hasNextPage ? ' ' . $cssClasses['item'] . '--disabled' : '');
  $lastPageClasses = $cssClasses['item'] . ' ' . $cssClasses['item'] . '--to-last' . (!$hasNextPage ? ' ' . $cssClasses['item'] . '--disabled' : '');

  return A::merge($data, compact(
      'shouldShowPagination',
      'cssClasses',
      'hasPrevPage',
      'hasNextPage',
      'currentPage',
      'totalPages',
      'rangePages',
      'firstPageUrl',
      'prevPageUrl',
      'nextPageUrl',
      'lastPageUrl',
      'pageUrls',
      'firstPageLabel',
      'prevPageLabel',
      'nextPageLabel',
      'lastPageLabel',
      'firstPageClasses',
      'prevPageClasses',
      'nextPageClasses',
      'lastPageClasses',
      'paginationParam'
  ));
};
