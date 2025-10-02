<?php

/**
 * Collection Item - Snippet Controller
 * Prepares data for individual collection item display
 */

return function ($item, $orderIndex = 0, $config = []) {

  // Ensure we have required variables
  if (!isset($item)) {
    return ['shouldRender' => false];
  }

  // Prepare item data
  $itemData = [
    'shouldRender' => true,
    'item' => $item,
    'orderIndex' => $orderIndex,
    'config' => $config,

    // Pre-calculate common data for template
    'hasImage' => $item->hasImages(),
    'firstImage' => $item->hasImages() ? $item->images()->first() : null,
    'hasText' => $item->hasMethod('text') && $item->text()->isNotEmpty(),
    'hasDate' => $item->hasMethod('date') && $item->date()->isNotEmpty(),
    'hasCategory' => $item->hasMethod('category') && $item->category()->isNotEmpty(),
    'hasTags' => $item->hasMethod('tags') && $item->tags()->isNotEmpty(),
  ];

  // Process tags if available
  if ($itemData['hasTags']) {
    $itemData['processedTags'] = array_map('trim', $item->tags()->split(','));
  }

  return $itemData;
};
