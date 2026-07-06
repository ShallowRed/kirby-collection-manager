<?php
/**
 * Collection Manager - Items List Snippet
 * Simple presentation-focused snippet
 */

// Defensive defaults
$config = $config ?? [];
$items = $items ?? [];
$isEmpty = $isEmpty ?? empty($items);
$hasActiveFilters = $hasActiveFilters ?? false;
?>

<?php if (!$isEmpty): ?>
  <div class="collection-items__list" data-testid="collection-items">
    <?php foreach ($items as $index => $itemData): ?>
      <?php 
        // Handle both old format (object with page property) and new format (direct Page objects)
        $page = is_object($itemData) && property_exists($itemData, 'page') ? $itemData->page : $itemData;
        $orderIndex = is_object($itemData) && property_exists($itemData, 'orderIndex') ? $itemData->orderIndex : $index;
      ?>
      <?php echo snippet($config['snippets']['item'] ?? 'collection-item', [
        'item' => $page,
        'orderIndex' => $orderIndex,
        'config' => $config
      ]) ?>
    <?php endforeach ?>
  </div>
<?php else: ?>
  <div class="collection-empty" data-testid="collection-empty">
    <div class="collection-empty__icon">📝</div>
    <h3 class="collection-empty__title"><?= t('collection.empty.title', 'No items found') ?></h3>
    <p class="collection-empty__message">
      <?= $hasActiveFilters
        ? t('collection.empty.filtered', 'Try adjusting your search or filter criteria.')
        : t('collection.empty.default', 'There are no items to display yet.')
      ?>
    </p>
  </div>
<?php endif ?>
