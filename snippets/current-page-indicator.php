<?php
// Validate required collection parameter
if (!isset($collection) || !$collection) {
  throw new Exception('Collection parameter is required for current-page-indicator snippet');
}

// Get plugin configuration
$config = kirby()->option('shallowred.collection-manager', []);
$texts = $config['texts'] ?? [];

// Get pagination data
$pagination = $collection->pagination();
$currentPage = $pagination->page();
$totalPages = $pagination->pages();

// Text configuration with fallback
$format = $format ?? $texts['pageIndicatorShort'] ?? 'Page {current} of {total}';
$indicatorText = str_replace(['{current}', '{total}'], [$currentPage, $totalPages], $format);
?>

<p class="current-page-indicator" role="status" aria-live="polite">
  <?= esc($indicatorText) ?>
</p>
