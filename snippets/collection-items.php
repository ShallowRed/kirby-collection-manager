<?php
/**
 * Collection Manager - Items List Snippet
 * Renders the list of collection items
 *
 * Available variables:
 * - $articles: Array of article objects with orderIndex
 * - $collection: The paginated collection
 * - $page: Current page object
 * - $config: Controller configuration
 */

// Handle both indexed articles array and direct collection
$items = $articles ?? $collection ?? [];
?>

<?php if (!empty($items)): ?>
  <div class="collection-items__list">
    <?php foreach ($items as $index => $item): ?>
      <?php
      // Handle both indexed format and direct page objects
      if (is_object($item) && property_exists($item, 'page')) {
        $article = $item->page;
        $orderIndex = $item->orderIndex;
      } else {
        $article = $item;
        $orderIndex = $index;
      }

      // Ensure we have a valid snippet name
      $itemSnippet = 'collection-item';
      if (isset($config['snippets']['item']) && !empty($config['snippets']['item'])) {
        $itemSnippet = $config['snippets']['item'];
      }
      ?>

      <?= snippet($itemSnippet, [
        'article' => $article,
        'orderIndex' => $orderIndex,
        'config' => $config ?? []
      ]) ?>

    <?php endforeach ?>
  </div>
<?php else: ?>
  <div class="collection-empty">
    <div class="collection-empty__icon">📝</div>
    <h3 class="collection-empty__title">No items found</h3>
    <p class="collection-empty__message">
      <?php if (get('q') || !empty(array_filter($_GET, fn($k) => $k !== 'p' && $k !== 'json', ARRAY_FILTER_USE_KEY))): ?>
        Try adjusting your search or filter criteria.
      <?php else: ?>
        There are no items to display yet.
      <?php endif ?>
    </p>
  </div>
<?php endif ?>
