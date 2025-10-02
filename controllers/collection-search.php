<?php

/**
 * Collection Search - Snippet Controller
 * Prepares all data for the collection search snippet
 */

return function ($page, $config) {

  // Get configured search parameter
  $searchParam = $config['search']['param'] ?? 'q';
  $paginationParam = $config['pagination']['param'] ?? 'p';

  // Get current search query
  $currentSearch = get($searchParam, '');
  $hasSearch = !empty($currentSearch);

  // Generate clear search URL
  $clearUrl = \KirbyCollectionManager\CollectionController::buildUrl(
      $page,
      [$searchParam => ''],
      $paginationParam,
      $searchParam
  );

  // Get placeholder text from config or use default
  $placeholder = $config['search']['placeholder'] ?? t('collection.search.placeholder', 'Search...');

  return [
    'page' => $page,
    'currentSearch' => $currentSearch,
    'hasSearch' => $hasSearch,
    'placeholder' => $placeholder,
    'clearUrl' => $clearUrl,
    'searchParam' => $searchParam
  ];
};
