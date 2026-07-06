<?php

use KirbyCollectionManager\CollectionController;

return function ($collection = null, $page = null, $config = [], $pagination = null, $showPagination = false, $showIndicator = true, $urlBuilder = null, $hasActiveFilters = false) {

  // Get the configured pagination parameter name
  $paginationParam = $config['pagination']['param'] ?? 'p';

  // Check if pagination should be shown
  $shouldShowPagination = $showPagination && $pagination && !($pagination->limit() > 0 && $pagination->total() === 0);

  // If pagination doesn't exist, return early with empty state
  if (!$pagination) {
    return [
      'shouldShowPagination' => false,
      'config' => $config,
    ];
  }

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
  $range = $config['pagination']['range'] ?? 5;
  $rangePages = $pagination->range($range);

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

  // Accessibility labels (using Kirby's i18n system)
  $firstPageLabel = t('collection.pagination.first', 'Go to first page') . (!$hasPrevPage ? ' (' . t('collection.pagination.disabled', 'disabled') . ')' : '');
  $prevPageLabel = t('collection.pagination.prev', 'Go to previous page') . (!$hasPrevPage ? ' (' . t('collection.pagination.disabled', 'disabled') . ')' : '');
  $nextPageLabel = t('collection.pagination.next', 'Go to next page') . (!$hasNextPage ? ' (' . t('collection.pagination.disabled', 'disabled') . ')' : '');
  $lastPageLabel = t('collection.pagination.last', 'Go to last page') . (!$hasNextPage ? ' (' . t('collection.pagination.disabled', 'disabled') . ')' : '');

  // Button states for CSS classes
  $firstPageClasses = $cssClasses['item'] . ' ' . $cssClasses['item'] . '--to-first' . (!$hasPrevPage ? ' ' . $cssClasses['item'] . '--disabled' : '');
  $prevPageClasses = $cssClasses['item'] . ' ' . $cssClasses['item'] . '--to-sibling' . (!$hasPrevPage ? ' ' . $cssClasses['item'] . '--disabled' : '');
  $nextPageClasses = $cssClasses['item'] . ' ' . $cssClasses['item'] . '--to-sibling' . (!$hasNextPage ? ' ' . $cssClasses['item'] . '--disabled' : '');
  $lastPageClasses = $cssClasses['item'] . ' ' . $cssClasses['item'] . '--to-last' . (!$hasNextPage ? ' ' . $cssClasses['item'] . '--disabled' : '');

  return [
      'shouldShowPagination' => $shouldShowPagination,
      'cssClasses' => $cssClasses,
      'hasPrevPage' => $hasPrevPage,
      'hasNextPage' => $hasNextPage,
      'currentPage' => $currentPage,
      'totalPages' => $totalPages,
      'rangePages' => $rangePages,
      'firstPageUrl' => $firstPageUrl,
      'prevPageUrl' => $prevPageUrl,
      'nextPageUrl' => $nextPageUrl,
      'lastPageUrl' => $lastPageUrl,
      'pageUrls' => $pageUrls,
      'firstPageLabel' => $firstPageLabel,
      'prevPageLabel' => $prevPageLabel,
      'nextPageLabel' => $nextPageLabel,
      'lastPageLabel' => $lastPageLabel,
      'firstPageClasses' => $firstPageClasses,
      'prevPageClasses' => $prevPageClasses,
      'nextPageClasses' => $nextPageClasses,
      'lastPageClasses' => $lastPageClasses,
      'paginationParam' => $paginationParam,
      'config' => $config,
      'page' => $page,
      'pagination' => $pagination,
  ];
};
