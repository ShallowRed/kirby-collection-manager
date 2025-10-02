<?php

/**
 * Current Page Indicator - Snippet Controller
 * Prepares all data for the current page indicator snippet
 */

return function ($pagination, $format = null, $showIndicator = true) {

  // Early return if conditions not met
  if (!$pagination || $pagination->pages() <= 1 || !$showIndicator || $pagination->total() === 0) {
    return ['shouldRender' => false];
  }

  // Get format string from parameter or use default
  $format = $format ?? t('collection.pagination.indicator', 'Page {current} of {total}');

  // Generate indicator text
  $indicatorText = str_replace(
      ['{current}', '{total}'],
      [$pagination->page(), $pagination->pages()],
      $format
  );

  return [
    'shouldRender' => true,
    'pagination' => $pagination,
    'format' => $format,
    'indicatorText' => $indicatorText,
    'currentPage' => $pagination->page(),
    'totalPages' => $pagination->pages()
  ];
};
