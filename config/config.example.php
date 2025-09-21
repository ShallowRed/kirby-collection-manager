<?php

/**
 * Kirby Collection Manager - Configuration Examples
 *
 * Add these configurations to your site/config/config.php file
 * to customize the behavior of the Collection Manager plugin.
 */

return [
  // Other Kirby config options...

  /**
   * Collection Manager Plugin Configuration
   */
  'shallowred.collection-manager' => [

    /**
     * Pagination Settings
     */
    'pagination' => [
      // Default number of page links to show in pagination
      'range' => 10,

      // Customize CSS classes
      'cssClasses' => [
        'nav' => 'collection-pagination',
        'item' => 'collection-pagination__item',
        'icon' => 'collection-pagination__icon',
      ]
    ],

    /**
     * Text/Language Configuration
     * Customize all text strings used by the plugin
     */
    'texts' => [
      // Pagination accessibility labels
      'firstPage' => 'Go to first page',
      'prevPage' => 'Go to previous page',
      'nextPage' => 'Go to next page',
      'lastPage' => 'Go to last page',

      // Page indicator formats
      // Use {current} and {total} as placeholders
      'pageIndicator' => 'Page {current} of {total}',
      'pageIndicatorShort' => 'p. {current} of {total}',
    ]
  ],

  /**
   * Example: French Configuration
   */
  // 'shallowred.collection-manager' => [
  //   'pagination' => [
  //     'range' => 8,
  //   ],
  //   'texts' => [
  //     'firstPage' => 'Aller à la première page',
  //     'prevPage' => 'Aller à la page précédente',
  //     'nextPage' => 'Aller à la page suivante',
  //     'lastPage' => 'Aller à la dernière page',
  //     'pageIndicator' => 'Page {current} sur {total}',
  //     'pageIndicatorShort' => 'p. {current} sur {total}',
  //   ]
  // ],

  /**
   * Example: German Configuration
   */
  // 'shallowred.collection-manager' => [
  //   'texts' => [
  //     'firstPage' => 'Zur ersten Seite',
  //     'prevPage' => 'Zur vorherigen Seite',
  //     'nextPage' => 'Zur nächsten Seite',
  //     'lastPage' => 'Zur letzten Seite',
  //     'pageIndicator' => 'Seite {current} von {total}',
  //     'pageIndicatorShort' => 'S. {current} von {total}',
  //   ]
  // ],

  /**
   * Example: Custom CSS Classes for Bootstrap
   */
  // 'shallowred.collection-manager' => [
  //   'pagination' => [
  //     'cssClasses' => [
  //       'nav' => 'pagination justify-content-center',
  //       'item' => 'page-item',
  //       'icon' => 'page-link',
  //     ]
  //   ]
  // ],

  /**
   * Example: Custom CSS Classes for Tailwind CSS
   */
  // 'shallowred.collection-manager' => [
  //   'pagination' => [
  //     'cssClasses' => [
  //       'nav' => 'flex justify-center items-center space-x-2',
  //       'item' => 'inline-block',
  //       'icon' => 'px-3 py-2 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50',
  //     ]
  //   ]
  // ],
];
