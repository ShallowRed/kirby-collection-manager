<?php

require_once __DIR__ . '/classes/CollectionController.php';

Kirby::plugin('shallowred/collection-manager', [

  'options' => [
    'pagination' => [
      'range' => 10,
      'cssClasses' => [
        'nav' => 'collection-pagination',
        'item' => 'collection-pagination__item',
        'icon' => 'collection-pagination__icon',
      ]
    ],
    'texts' => [
      'firstPage' => 'Go to first page',
      'prevPage' => 'Go to previous page',
      'nextPage' => 'Go to next page',
      'lastPage' => 'Go to last page',
      'pageIndicator' => 'Page {current} of {total}',
      'pageIndicatorShort' => 'p. {current} of {total}',
    ]
  ],

  'snippets' => [
    'collection-pagination' => __DIR__ . '/snippets/collection-pagination.php',
    'collection-pagination.controller' => __DIR__ . '/controllers/collection-pagination.php',
    'current-page-indicator' => __DIR__ . '/snippets/current-page-indicator.php',
    'current-page-indicator.controller' => __DIR__ . '/controllers/current-page-indicator.php',
    'collection-items' => __DIR__ . '/snippets/collection-items.php',
    'collection-item' => __DIR__ . '/snippets/collection-item.php',
    'collection-item.controller' => __DIR__ . '/controllers/collection-item.php',
    'collection-filters' => __DIR__ . '/snippets/collection-filters.php',
    'collection-filters.controller' => __DIR__ . '/controllers/collection-filters.php',
    'collection-search' => __DIR__ . '/snippets/collection-search.php',
    'collection-search.controller' => __DIR__ . '/controllers/collection-search.php',
    'collection-manager' => __DIR__ . '/snippets/collection-manager.php',
  ],

  'pageMethods' => [
    'collectionManager' => function ($config = []) {
      $controller = new \KirbyCollectionManager\CollectionController($this, kirby(), $config);
      return $controller->handle();
    }
  ],

  'siteMethods' => [
    'collectionManager' => function ($page, $config = []) {
      $controller = new \KirbyCollectionManager\CollectionController($page, $this, $config);
      return $controller->handle();
    }
  ]

]);
