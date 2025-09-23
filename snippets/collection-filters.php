<?php
/**
 * Collection Manager - Filters Snippet
 * Renders taxonomy filter links
 *
 * Available variables:
 * - $collection: The collection (before pagination)
 * - $page: Current page object
 * - $config: Controller configuration
 */

// Get taxonomy configuration
$taxonomies = $config['taxonomies'] ?? [];

if (empty($taxonomies)) {
  // Auto-detect common taxonomy fields if none configured
  $taxonomies = [
    ['param' => 'category', 'field' => 'category', 'label' => 'Category'],
    ['param' => 'tag', 'field' => 'tags', 'label' => 'Tag'],
  ];
}
?>

<div class="collection-filters">

  <?php foreach ($taxonomies as $taxonomy): ?>
    <?php
    $param = $taxonomy['param'];
    $field = $taxonomy['field'];
    $label = $taxonomy['label'] ?? ucfirst($param);
    $currentValue = get($param);

    // Get all unique values for this taxonomy
    $values = [];
    if (method_exists($page, 'children')) {
      $allItems = $page->children()->listed();
      $values = $allItems->pluck($field, ',', true);
    }

    if (empty($values)) continue;
    ?>

    <div class="collection-filters__group">
      <h4 class="collection-filters__label"><?= esc($label) ?></h4>

      <div class="collection-filters__options">
        <!-- All/Clear option -->
        <a href="<?= \KirbyCollectionManager\CollectionController::buildUrl($page, [$param => null]) ?>"
           class="collection-filter <?= !$currentValue ? 'collection-filter--active' : '' ?>"
           data-param="<?= esc($param) ?>"
           data-value="">
          All <?= esc($label) ?>s
        </a>

        <!-- Individual filter options -->
        <?php foreach ($values as $value): ?>
          <?php if (empty(trim($value))) continue; ?>
          <?php $isActive = $currentValue === $value; ?>

          <a href="<?= \KirbyCollectionManager\CollectionController::buildUrl($page, [$param => $value]) ?>"
             class="collection-filter <?= $isActive ? 'collection-filter--active' : '' ?>"
             data-param="<?= esc($param) ?>"
             data-value="<?= esc($value) ?>">
            <?= esc($value) ?>
          </a>
        <?php endforeach ?>
      </div>
    </div>

  <?php endforeach ?>

  <?php if (!empty(array_filter($_GET, fn($k) => $k !== 'p' && $k !== 'json', ARRAY_FILTER_USE_KEY))): ?>
    <div class="collection-filters__actions">
      <a href="<?= $page->url() ?>" class="collection-filters__clear">
        Clear all filters
      </a>
    </div>
  <?php endif ?>

</div>
