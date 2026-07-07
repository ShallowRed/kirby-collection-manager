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

<?php $classes = $config['classes'] ?? []; ?>
<?php if (!$isEmpty) : ?>
  <div class="<?= esc(trim('collection-items__list ' . ($classes['items'] ?? ''))) ?>" data-testid="collection-items">
    <?php foreach ($items as $index => $item) : ?>
      <?php snippet($config['snippets']['item'] ?? 'collection-item', [
        'item' => $item,
        'orderIndex' => $index,
        'config' => $config
      ]) ?>
    <?php endforeach ?>
  </div>
<?php else : ?>
  <div class="<?= esc(trim('collection-empty ' . ($classes['empty'] ?? ''))) ?>" data-testid="collection-empty">
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
