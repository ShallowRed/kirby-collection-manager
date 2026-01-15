<?php

/**
 * Collection Manager - Filters Snippet
 * Uses htmx for AJAX filtering
 */

$htmxEnabled = $config['enableJs'] ?? true;
$htmxTarget = '#collection-content';
$htmxSwap = 'innerHTML show:window:top';

?>

<div class="collection-filters">

  <?php foreach ($taxonomies as $taxonomy) : ?>
    <div class="collection-filters__group">
      <h4 class="collection-filters__label"><?php echo esc($taxonomy['label'], 'html') ?></h4>

      <div class="collection-filters__options">
        <!-- All/Clear option -->
        <?php
        $allUrl = $taxonomy['allUrl'];
        $allHtmxUrl = $allUrl . (strpos($allUrl, '?') !== false ? '&' : '?') . 'htmx=1';
        ?>
        <a <?php echo attr(array_filter([
          'href' => $allUrl,
          'class' => 'collection-filter' . (!$taxonomy['hasActiveFilter'] ? ' collection-filter--active' : ''),
          'data-param' => $taxonomy['param'],
          'data-value' => '',
          'hx-get' => $htmxEnabled ? $allHtmxUrl : null,
          'hx-target' => $htmxEnabled ? $htmxTarget : null,
          'hx-swap' => $htmxEnabled ? $htmxSwap : null,
          'hx-push-url' => $htmxEnabled ? $allUrl : null
        ])) ?>>
          <?= Str::template(t('collection.filters.all', 'All {label}'), ['label' => esc($taxonomy['label'], 'html')]) ?>
        </a>

        <!-- Individual filter options -->
        <?php foreach ($taxonomy['options'] as $option) : ?>
          <?php
          $optionUrl = $option['url'];
          $optionHtmxUrl = $optionUrl . (strpos($optionUrl, '?') !== false ? '&' : '?') . 'htmx=1';
          ?>
          <a <?php echo attr(array_filter([
            'href' => $optionUrl,
            'class' => 'collection-filter' . ($option['isActive'] ? ' collection-filter--active' : ''),
            'data-param' => $option['param'],
            'data-value' => $option['value'],
            'hx-get' => $htmxEnabled ? $optionHtmxUrl : null,
            'hx-target' => $htmxEnabled ? $htmxTarget : null,
            'hx-swap' => $htmxEnabled ? $htmxSwap : null,
            'hx-push-url' => $htmxEnabled ? $optionUrl : null
          ])) ?>>
            <?php echo esc($option['label'], 'html') ?>
          </a>
        <?php endforeach ?>
      </div>
    </div>
  <?php endforeach ?>

  <?php if ($hasActiveFilters) : ?>
    <?php
    $clearAllHtmxUrl = $clearAllUrl . (strpos($clearAllUrl, '?') !== false ? '&' : '?') . 'htmx=1';
    ?>
    <div class="collection-filters__actions">
      <a <?php echo attr(array_filter([
        'href' => $clearAllUrl,
        'class' => 'collection-filters__clear',
        'hx-get' => $htmxEnabled ? $clearAllHtmxUrl : null,
        'hx-target' => $htmxEnabled ? $htmxTarget : null,
        'hx-swap' => $htmxEnabled ? $htmxSwap : null,
        'hx-push-url' => $htmxEnabled ? $clearAllUrl : null
      ])) ?>>
        <?= t('collection.filters.clear', 'Clear all filters') ?>
      </a>
    </div>
  <?php endif ?>

</div>
