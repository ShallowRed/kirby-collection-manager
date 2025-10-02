<?php

/**
 * Collection Filters - Snippet Controller
 * Prepares all data for the collection filters snippet
 */

return function ($collection, $page, $config) {

  // Get configured parameters
  $paginationParam = $config['pagination']['param'] ?? 'p';
  $searchParam = $config['search']['param'] ?? 'q';

  // Get taxonomy configuration
  $taxonomies = $config['taxonomies'] ?? [];

  if (empty($taxonomies)) {
    // Auto-detect common taxonomy fields if none configured
    $taxonomies = [
      ['param' => 'category', 'field' => 'category', 'label' => 'Category'],
      ['param' => 'tag', 'field' => 'tags', 'label' => 'Tag'],
    ];
  }

  $processedTaxonomies = [];

  foreach ($taxonomies as $taxonomy) {
    $param = $taxonomy['param'];
    $field = $taxonomy['field'];
    $label = $taxonomy['label'] ?? ucfirst($param);
    $currentValue = get($param);

    // Get all unique values for this taxonomy
    $values = [];
    if (method_exists($page, 'children')) {
      $allItems = $page->children()->listed();
      $values = $allItems->pluck($field, ',', true);
    }

    if (empty($values)) {
      continue;
    }

    // Process individual filter options
    $filterOptions = [];
    foreach ($values as $value) {
      if (empty(trim($value))) {
        continue;
      }

      $isActive = $currentValue === $value;
      $filterOptions[] = [
        'value' => $value,
        'label' => $value,
        'isActive' => $isActive,
        'url' => \KirbyCollectionManager\CollectionController::buildUrl($page, [$param => $value], $paginationParam, $searchParam),
        'param' => $param
      ];
    }

    $processedTaxonomies[] = [
      'param' => $param,
      'field' => $field,
      'label' => $label,
      'currentValue' => $currentValue,
      'allUrl' => \KirbyCollectionManager\CollectionController::buildUrl($page, [$param => null], $paginationParam, $searchParam),
      'hasActiveFilter' => !empty($currentValue),
      'options' => $filterOptions
    ];
  }

  // Check if any filters are active
  $hasActiveFilters = !empty(array_filter($_GET, fn($k) => $k !== $paginationParam && $k !== 'json', ARRAY_FILTER_USE_KEY));

  return [
    'taxonomies' => $processedTaxonomies,
    'hasActiveFilters' => $hasActiveFilters,
    'clearAllUrl' => $page->url(),
    'page' => $page
  ];
};
