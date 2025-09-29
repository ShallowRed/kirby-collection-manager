<?php
/**
 * Collection Manager - Items List Snippet
 * Simple presentation-focused snippet
 *
 * Available variables:
 * - $items: Array of indexed items (legacy name, kept for compatibility)
 * - $isEmpty: Whether collection is empty
 * - $hasActiveFilters: Whether filters/search are active
 * - $config: Controller configuration
 */
?>

<?php if (!$isEmpty): ?>
  <div class="collection-items__list">
    <?php foreach ($items as $itemData): ?>
      <?= snippet($config['snippets']['item'] ?? 'collection-item', [
        'item' => $itemData->page,
        'orderIndex' => $itemData->orderIndex,
        'config' => $config
      ]) ?>
    <?php endforeach ?>
  </div>
<?php else: ?>
  <div class="collection-empty">
    <div class="collection-empty__icon">📝</div>
    <h3 class="collection-empty__title">No items found</h3>
    <p class="collection-empty__message">
      <?= $hasActiveFilters ? 'Try adjusting your search or filter criteria.' : 'There are no items to display yet.' ?>
    </p>
  </div>
<?php endif ?>
