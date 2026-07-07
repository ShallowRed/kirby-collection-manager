<?php

/**
 * Collection Manager - Filters Snippet
 * Uses htmx for AJAX filtering
 */

// Defensive defaults for when controller doesn't run
$config = $config ?? [];
$taxonomies = $taxonomies ?? [];

$htmxEnabled = $config['enableJs'] ?? true;
$instanceId = $config['instanceId'] ?? 'collection';
$htmxTarget = '#' . $instanceId . '-content';
$htmxSwap = 'innerHTML show:window:top';

// If no taxonomies, don't render anything
if (empty($taxonomies)) {
    return;
}
?>

<div class="collection-filters" data-testid="collection-filters">

  <?php foreach ($taxonomies as $taxonomy) : ?>
    <div class="collection-filters__group" data-testid="collection-filter-group-<?= esc($taxonomy['param'], 'html') ?>">
      <h4 class="collection-filters__label"><?php echo esc($taxonomy['label'], 'html') ?></h4>

      <div class="collection-filters__options">
        <!-- All/Clear option -->
        <a <?php echo attr(array_filter([
          'href' => $taxonomy['allUrl'],
          'class' => 'collection-filter' . (!$taxonomy['hasActiveFilter'] ? ' collection-filter--active' : ''),
          'data-param' => $taxonomy['param'],
          'data-value' => '',
          'data-testid' => 'collection-filter-' . $taxonomy['param'] . '-all',
          'hx-get' => $htmxEnabled ? $taxonomy['allUrl'] : null,
          'hx-target' => $htmxEnabled ? $htmxTarget : null,
          'hx-swap' => $htmxEnabled ? $htmxSwap : null,
          'hx-push-url' => $htmxEnabled ? 'true' : null
        ])) ?>>
          <?= Str::template(t('collection.filters.all', 'All {label}'), ['label' => esc($taxonomy['label'], 'html')]) ?>
        </a>

        <!-- Individual filter options -->
        <?php foreach ($taxonomy['options'] as $option) : ?>
          <a <?php echo attr(array_filter([
            'href' => $option['url'],
            'class' => 'collection-filter'
              . ($option['isActive'] ? ' collection-filter--active' : '')
              . (($taxonomy['multiple'] ?? false) ? ' collection-filter--multiple' : ''),
            'data-param' => $option['param'],
            'data-value' => $option['value'],
            'data-testid' => 'collection-filter-' . $option['param'] . '-' . Str::slug($option['value']),
            'hx-get' => $htmxEnabled ? $option['url'] : null,
            'hx-target' => $htmxEnabled ? $htmxTarget : null,
            'hx-swap' => $htmxEnabled ? $htmxSwap : null,
            'hx-push-url' => $htmxEnabled ? 'true' : null
          ])) ?>>
            <?php echo esc($option['label'], 'html') ?>
          </a>
        <?php endforeach ?>
      </div>
    </div>
  <?php endforeach ?>

  <?php if ($hasActiveFilters) : ?>
    <div class="collection-filters__actions">
      <a <?php echo attr(array_filter([
        'href' => $clearAllUrl,
        'class' => 'collection-filters__clear',
        'data-testid' => 'collection-filters-clear',
        'hx-get' => $htmxEnabled ? $clearAllUrl : null,
        'hx-target' => $htmxEnabled ? $htmxTarget : null,
        'hx-swap' => $htmxEnabled ? $htmxSwap : null,
        'hx-push-url' => $htmxEnabled ? 'true' : null
      ])) ?>>
        <?= t('collection.filters.clear', 'Clear all filters') ?>
      </a>
    </div>
  <?php endif ?>

</div>
