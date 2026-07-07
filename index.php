<?php

use KirbyCollectionManager\CollectionController;

Kirby::plugin('shallowred/collection-manager', [

  'translations' => [
    'en' => require __DIR__ . '/translations/en.php',
    'fr' => require __DIR__ . '/translations/fr.php',
    'de' => require __DIR__ . '/translations/de.php',
  ],

  'options' => [
    'pagination' => [
      'range' => 10,
      'cssClasses' => [
        'nav' => 'collection-pagination',
        'item' => 'collection-pagination__item',
        'icon' => 'collection-pagination__icon',
      ]
    ],
  ],

  'snippets' => [
    'collection-manager' => __DIR__ . '/snippets/collection-manager.php',
    'collection-items' => __DIR__ . '/snippets/collection-items.php',
    'collection-item' => __DIR__ . '/snippets/collection-item.php',
    'collection-item.controller' => __DIR__ . '/controllers/collection-item.php',
    'collection-pagination' => __DIR__ . '/snippets/collection-pagination.php',
    'current-page-indicator' => __DIR__ . '/snippets/current-page-indicator.php',
    'collection-filters' => __DIR__ . '/snippets/collection-filters.php',
    'collection-search' => __DIR__ . '/snippets/collection-search.php',
    'collection-sorting' => __DIR__ . '/snippets/collection-sorting.php',
  ],

  'pageMethods' => [
    'collectionManager' => function (array $config = []) {
      return CollectionController::handle($this, $config);
    }
  ],

  'siteMethods' => [
    'collectionManager' => function ($page, array $config = []) {
      return CollectionController::handle($page, $config);
    }
  ]

]);
