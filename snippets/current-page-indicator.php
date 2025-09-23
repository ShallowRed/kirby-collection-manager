<?php
// Validate required collection parameter
if (!isset($collection) || !$collection) {
  if (kirby()->option('debug')) {
    throw new Exception('Collection parameter is required for current-page-indicator snippet');
  } else {
    // In production, fail gracefully but return empty indicator
    echo '<p class="current-page-indicator" style="display: none;"><!-- Collection parameter missing --></p>';
    return;
  }
}

// Validate that collection has pagination method
if (!method_exists($collection, 'pagination')) {
  if (kirby()->option('debug')) {
    throw new Exception('Collection must be a paginated collection (use ->paginate() method)');
  } else {
    // In production, fail gracefully but return empty indicator
    echo '<p class="current-page-indicator" style="display: none;"><!-- Collection not paginated --></p>';
    return;
  }
}

// Get plugin configuration with proper validation
$config = kirby()->option('shallowred.collection-manager', []);
$texts = is_array($config['texts'] ?? null) ? $config['texts'] : [];

// Get pagination data with error handling
try {
  $pagination = $collection->pagination();
  $currentPage = $pagination->page();
  $totalPages = $pagination->pages();
} catch (Exception $e) {
  if (kirby()->option('debug')) {
    throw new Exception('Error getting pagination data: ' . $e->getMessage());
  } else {
    echo '<p class="current-page-indicator" style="display: none;"><!-- Pagination data error --></p>';
    return;
  }
}

// Validate pagination data
if (!is_int($currentPage) || !is_int($totalPages) || $currentPage < 1 || $totalPages < 1) {
  if (kirby()->option('debug')) {
    throw new Exception('Invalid pagination data');
  } else {
    echo '<p class="current-page-indicator" style="display: none;"><!-- Invalid pagination data --></p>';
    return;
  }
}

// Text configuration with fallback and validation
$format = isset($format) && is_string($format) ? $format : ($texts['pageIndicatorShort'] ?? 'Page {current} of {total}');

// Validate format string contains required placeholders
if (strpos($format, '{current}') === false || strpos($format, '{total}') === false) {
  $format = 'Page {current} of {total}';
}

$indicatorText = str_replace(['{current}', '{total}'], [$currentPage, $totalPages], $format);

// Only show indicator if there are multiple pages
if ($totalPages > 1):
?>

<p class="current-page-indicator" role="status" aria-live="polite">
  <?= esc($indicatorText) ?>
</p>

<?php else: ?>
<p class="current-page-indicator" role="status" aria-live="polite" style="display: none;">
  <!-- No page indicator needed for single page -->
</p>
<?php endif ?>
