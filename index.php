<?php

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
    'current-page-indicator' => __DIR__ . '/snippets/current-page-indicator.php',
  ]

]);
