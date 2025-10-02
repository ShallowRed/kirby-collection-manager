<?php

/**
 * Collection Manager - Page Indicator Snippet
 * Simple presentation-focused snippet using snippet controller
 */

if (!($shouldRender ?? true)) {
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
