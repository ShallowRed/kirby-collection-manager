<?php
/**
 * Collection Manager - Main Wrapper Snippet
 * This is the default wrapper that contains all collection manager components
 *
 * Available variables:
 * - $collection: The paginated collection
 * - $page: Current page object
 * - $config: Controller configuration
 * - $snippets: Generated snippet content
 */

$cssClass = trim($config['containers']['wrapper'] ?? '', '.');
?>

<div class="<?= $cssClass ?>" data-collection-manager>

  <?php if ($config['enableSearch'] ?? true): ?>
    <!-- Search Form -->
    <div class="<?= trim($config['containers']['search'] ?? '', '.') ?>">
      <?= $snippets['search'] ?? snippet($config['snippets']['search'], compact('page', 'config'), true) ?>
    </div>
  <?php endif ?>

  <?php if ($config['enableFilters'] ?? true): ?>
    <!-- Taxonomy Filters -->
    <div class="<?= trim($config['containers']['filters'] ?? '', '.') ?>">
      <?= $snippets['filters'] ?? snippet($config['snippets']['filters'], compact('collection', 'page', 'config'), true) ?>
    </div>
  <?php endif ?>

  <!-- Collection Items -->
  <div class="<?= trim($config['containers']['items'] ?? '', '.') ?>" data-replacementtop="true" data-offset="100">
    <?php if (isset($snippets['items'])): ?>
      <?= $snippets['items'] ?>
    <?php else: ?>
      <?php
      // Generate articles with order indices if not provided
      if (!isset($articles)) {
        $articles = [];
        $index = 0;
        foreach ($collection as $item) {
          $articles[] = (object) [
            'page' => $item,
            'orderIndex' => $index++
          ];
        }
      }
      ?>
      <?= snippet($config['snippets']['items'], compact('articles', 'collection', 'page', 'config'), true) ?>
    <?php endif ?>
  </div>

  <!-- Pagination and Indicator -->
  <div class="collection-pagination-wrapper">
    <?= $snippets['pagination'] ?? snippet($config['snippets']['pagination'], compact('collection', 'page', 'config'), true) ?>

    <?= $snippets['indicator'] ?? snippet($config['snippets']['indicator'], compact('collection', 'config'), true) ?>
  </div>

  <?php if ($config['enableJs'] ?? true): ?>
    <!-- Auto-initialize JavaScript -->
    <script type="module">
      import { CollectionManager } from '/site/plugins/kirby-collection-manager/lib/index.js';

      const manager = new CollectionManager({
        contentRoute: '<?= $page->url() ?>',
        useIsotope: <?= json_encode($config['useIsotope'] ?? false) ?>,
        debug: <?= json_encode(kirby()->option('debug', false)) ?>,
        afterReplace: () => {
          // Re-attach event listeners after content replacement
          initializeCollectionManager(manager);
        }
      });

      function initializeCollectionManager(manager) {
        // Pagination links
        document.querySelectorAll('<?= $config['containers']['pagination'] ?> a').forEach(link => {
          manager.listenPaginationEvent(link);
        });

        // Taxonomy filter links
        document.querySelectorAll('[data-param]').forEach(link => {
          manager.listenTaxonomyEvent(link);
        });

        // Search form
        const searchForm = document.querySelector('<?= $config['containers']['search'] ?> form');
        if (searchForm) {
          manager.listenSearchEvent(searchForm);
        }
      }

      // Initial setup
      initializeCollectionManager(manager);
    </script>
  <?php endif ?>

</div>
