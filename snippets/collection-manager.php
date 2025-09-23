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
      <?= $snippets['search'] ?? '' ?>
    </div>
  <?php endif ?>

  <?php if ($config['enableFilters'] ?? true): ?>
    <!-- Taxonomy Filters -->
    <div class="<?= trim($config['containers']['filters'] ?? '', '.') ?>">
      <?= $snippets['filters'] ?? '' ?>
    </div>
  <?php endif ?>

  <!-- Collection Items -->
  <div class="<?= trim($config['containers']['items'] ?? '', '.') ?>" data-replacementtop="true" data-offset="100">
    <?= $snippets['items'] ?? '' ?>
  </div>

  <!-- Pagination and Indicator -->
  <div class="collection-pagination-wrapper">
    <?= $snippets['pagination'] ?? '' ?>

    <?= $snippets['indicator'] ?? '' ?>
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
