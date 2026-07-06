<?php

/**
 * Collection Manager - Page Indicator Snippet
 * Simple presentation-focused snippet using snippet controller
 */

// Defensive defaults
$shouldRender = $shouldRender ?? true;
$indicatorText = $indicatorText ?? '';

if (!$shouldRender || empty($indicatorText)) {
  return;
}
?>

<p <?php echo attr([
  'class' => 'current-page-indicator',
  'role' => 'status',
  'aria-live' => 'polite'
]) ?>>
  <?php echo esc($indicatorText, 'html') ?>
</p>
