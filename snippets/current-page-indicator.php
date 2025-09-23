<?php
/**
 * Collection Manager - Page Indicator Snippet
 * Simple presentation-focused snippet
 *
 * Available variables:
 * - $pagination: Pagination object
 * - $format: Format string for indicator
 * - $showIndicator: Whether to show the indicator
 */

if (!$pagination || $pagination->pages() <= 1 || !($showIndicator ?? true) || $pagination->total() === 0) return;

$indicatorText = str_replace(
    ['{current}', '{total}'],
    [$pagination->page(), $pagination->pages()],
    $format
);
?>

<p class="current-page-indicator" role="status" aria-live="polite">
  <?= esc($indicatorText) ?>
</p>
