<?php

/**
 * Collection Manager - Filters Snippet
 * Renders taxonomy filter links
 */

?>

<div class="collection-filters">

  <?php foreach ($taxonomies as $taxonomy) : ?>
    <div class="collection-filters__group">
      <h4 class="collection-filters__label"><?php echo esc($taxonomy['label'], 'html') ?></h4>

      <div class="collection-filters__options">
        <!-- All/Clear option -->
        <a <?php echo attr([
          'href' => $taxonomy['allUrl'],
          'class' => 'collection-filter' . (!$taxonomy['hasActiveFilter'] ? ' collection-filter--active' : ''),
          'data-param' => $taxonomy['param'],
          'data-value' => ''
        ]) ?>>
          All <?php echo esc($taxonomy['label'], 'html') ?>s
        </a>

        <!-- Individual filter options -->
        <?php foreach ($taxonomy['options'] as $option) : ?>
          <a <?php echo attr([
            'href' => $option['url'],
            'class' => 'collection-filter' . ($option['isActive'] ? ' collection-filter--active' : ''),
            'data-param' => $option['param'],
            'data-value' => $option['value']
          ]) ?>>
            <?php echo esc($option['label'], 'html') ?>
          </a>
        <?php endforeach ?>
      </div>
    </div>
  <?php endforeach ?>

  <?php if ($hasActiveFilters) : ?>
    <div class="collection-filters__actions">
      <a <?php echo attr([
        'href' => $clearAllUrl,
        'class' => 'collection-filters__clear'
      ]) ?>>
        Clear all filters
      </a>
    </div>
  <?php endif ?>

</div>
