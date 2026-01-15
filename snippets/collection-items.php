<?php
/**
 * Collection Manager - Items List Snippet
 * Simple presentation-focused snippet
 */
?>

<?php if (!$isEmpty): ?>
  <div class="collection-items__list" data-testid="collection-items">
    <?php foreach ($items as $itemData): ?>
      <?php echo snippet($config['snippets']['item'] ?? 'collection-item', [
        'item' => $itemData->page,
        'orderIndex' => $itemData->orderIndex,
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
