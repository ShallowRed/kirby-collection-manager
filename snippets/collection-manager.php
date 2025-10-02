<?php

/**
 * Collection Manager - Main Wrapper Snippet
 * This is the default wrapper that contains all collection manager components
 */

$cssClass = trim($config['containers']['wrapper'] ?? '', '.');
?>

<div <?php echo attr([
  'class' => $cssClass,
  'data-collection-manager' => true
]) ?>>

  <?php if ($config['enableSearch'] ?? true) : ?>
    <!-- Search Form -->
    <div <?php echo attr(['class' => trim($config['containers']['search'] ?? '', '.')]) ?>>
    <?php echo $snippets['search'] ?? '' ?>
    </div>
  <?php endif ?>

  <?php if ($config['enableFilters'] ?? true) : ?>
    <!-- Taxonomy Filters -->
    <div <?php echo attr(['class' => trim($config['containers']['filters'] ?? '', '.')]) ?>>
    <?php echo $snippets['filters'] ?? '' ?>
    </div>
  <?php endif ?>

  <!-- Collection Items -->
  <div <?php echo attr([
  'class' => trim($config['containers']['items'] ?? '', '.'),
  'data-replacementtop' => 'true',
  'data-offset' => '100'
]) ?>>
    <?php echo $snippets['items'] ?? '' ?>
  </div>

  <!-- Pagination and Indicator -->
  <div class="collection-pagination-wrapper">
    <?php echo $snippets['pagination'] ?? '' ?>

    <?php echo $snippets['indicator'] ?? '' ?>
  </div>

  <?php if ($config['enableJs'] ?? true) : ?>
    <!-- Auto-initialize JavaScript -->
    <script type="module">
      import { CollectionManager } from '/site/plugins/kirby-collection-manager/lib/index.js';

      const manager = new CollectionManager({
        contentRoute: <?php echo json_encode($page->url()) ?>,
        useIsotope: <?php echo json_encode($config['useIsotope'] ?? false) ?>,
        debug: <?php echo json_encode(kirby()->option('debug', false)) ?>,
        paginationParam: <?php echo json_encode($config['pagination']['param'] ?? 'p') ?>,
        searchParam: <?php echo json_encode($config['search']['param'] ?? 'q') ?>,
        afterReplace: () => {
          // Re-attach event listeners after content replacement
          initializeCollectionManager(manager);
        }
      });

      function initializeCollectionManager(manager) {
        // Pagination links
        document.querySelectorAll(<?php echo json_encode($config['containers']['pagination']) ?> + ' a').forEach(link => {
          manager.listenPaginationEvent(link);
        });

        // Taxonomy filter links
        document.querySelectorAll('[data-param]').forEach(link => {
          manager.listenTaxonomyEvent(link);
        });

        // Search form
        const searchForm = document.querySelector(<?php echo json_encode($config['containers']['search']) ?> + ' form');
        if (searchForm) {
          manager.listenSearchEvent(searchForm);
        }
      }

      // Initial setup
      initializeCollectionManager(manager);
    </script>
  <?php endif ?>

</div>
