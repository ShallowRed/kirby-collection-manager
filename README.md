# Kirby Collection Manager

A powerful Kirby CMS plugin for managing collections with AJAX pagination, search, filtering, and sorting. Provides both a complete out-of-the-box solution and flexible components for custom implementations.

## Features

- 🔍 **Search** - Full-text search across collection items
- 🏷️ **Filtering** - Taxonomy-based filtering (categories, tags, etc.)
- 📄 **Pagination** - AJAX-powered pagination with URL preservation
- 📱 **Responsive** - Mobile-first responsive design
- ⚡ **Performance** - Optimized for large collections
- 🎨 **Customizable** - Override templates and styles easily
- ♿ **Accessible** - Screen reader friendly with proper ARIA
- 🚀 **Progressive Enhancement** - Works without JavaScript

## Installation

### Via Composer (Recommended)

```bash
composer require your-namespace/kirby-collection-manager
```

### Manual Installation

1. Download the plugin
2. Place in `site/plugins/kirby-collection-manager`

## Quick Start

### 1. Basic Usage

The simplest way to add collection management to any page:

```php
<?php snippet('collection-manager', [
  'collection' => $page->children()->listed()
]) ?>
```

### 2. Advanced Configuration

```php
<?php snippet('collection-manager', [
  'collection' => $page->children()->listed(),
  'config' => [
    'search' => [
      'fields' => ['title', 'text', 'category'],
      'placeholder' => 'Search articles...'
    ],
    'taxonomies' => [
      ['param' => 'category', 'field' => 'category', 'label' => 'Category'],
      ['param' => 'tag', 'field' => 'tags', 'label' => 'Tag']
    ],
    'pagination' => [
      'limit' => 12,
      'template' => 'custom-pagination'
    ],
    'sorting' => [
      'default' => 'date',
      'direction' => 'desc'
    ]
  ]
]) ?>
```

## Controller Factory Pattern

For advanced use cases, use the `CollectionController` directly in your page controllers:

### site/controllers/blog.php

```php
<?php

use KirbyCollectionManager\CollectionController;

return function ($page, $site, $kirby) {
    return CollectionController::handle($page, [
        'collection' => $page->children()->listed(),
        'config' => [
            'search' => [
                'fields' => ['title', 'text', 'intro', 'category'],
                'placeholder' => 'Search blog posts...'
            ],
            'taxonomies' => [
                ['param' => 'category', 'field' => 'category', 'label' => 'Category'],
                ['param' => 'author', 'field' => 'author', 'label' => 'Author']
            ],
            'pagination' => [
                'limit' => 6
            ],
            'sorting' => [
                'default' => 'date',
                'direction' => 'desc'
            ]
        ]
    ]);
};
```

### site/templates/blog.php

```php
<?php snippet('collection-manager', compact('collection', 'config')) ?>
```

## Configuration Options

### Search Configuration

```php
'search' => [
    'fields' => ['title', 'text'],        // Fields to search
    'placeholder' => 'Search...',         // Input placeholder
    'minLength' => 2,                     // Minimum search length
    'highlight' => true                   // Highlight search terms
]
```

### Taxonomy Configuration

```php
'taxonomies' => [
    [
        'param' => 'category',            // URL parameter name
        'field' => 'category',            // Page field name
        'label' => 'Category'             // Display label
    ]
]
```

### Pagination Configuration

```php
'pagination' => [
    'limit' => 10,                        // Items per page
    'template' => 'my-pagination'         // Custom pagination template
]
```

### Sorting Configuration

```php
'sorting' => [
    'default' => 'title',                 // Default sort field
    'direction' => 'asc',                 // Default direction
    'options' => [                        // Available sort options
        'title' => 'Title',
        'date' => 'Date'
    ]
]
```

## Page Methods

The plugin adds convenient page methods:

```php
// Get a configured collection manager
$manager = $page->collectionManager([
    'collection' => $page->children()->listed(),
    'config' => [...]
]);

// Quick collection with search and pagination
$result = $page->collection([
    'search' => ['title', 'text'],
    'limit' => 12
]);
```

## Template Customization

### Override Individual Components

Create custom snippets in your theme to override defaults:

```
site/snippets/
├── collection-manager.php     # Main wrapper
├── collection-search.php      # Search form
├── collection-filters.php     # Filter links
├── collection-items.php       # Items container
├── collection-item.php        # Individual item
└── collection-pagination.php  # Pagination
```

### Custom Item Template

```php
<!-- site/snippets/collection-item.php -->
<article class="my-item">
  <h3><a href="<?= $item->url() ?>"><?= $item->title() ?></a></h3>
  <p><?= $item->text()->excerpt(150) ?></p>
  <time><?= $item->date()->toDate('M j, Y') ?></time>
</article>
```

### Custom Pagination Template

```php
<!-- site/snippets/collection-pagination.php -->
<nav class="my-pagination">
  <?php if ($pagination->hasPrevPage()): ?>
    <a href="<?= $pagination->prevPageURL() ?>">← Previous</a>
  <?php endif ?>
  
  <span>Page <?= $pagination->page() ?> of <?= $pagination->pages() ?></span>
  
  <?php if ($pagination->hasNextPage()): ?>
    <a href="<?= $pagination->nextPageURL() ?>">Next →</a>
  <?php endif ?>
</nav>
```

## Styling

### Using Default Styles

Include the default CSS:

```php
<!-- In your template -->
<?= css('media/plugins/kirby-collection-manager/collection-manager.css') ?>
```

### CSS Classes Reference

```css
.collection-manager              /* Main container */
.collection-search               /* Search form */
.collection-filters              /* Filter links */
.collection-items                /* Items container */
.collection-item                 /* Individual item */
.collection-pagination           /* Pagination */

/* State classes */
.collection-manager--loading     /* During AJAX requests */
.collection-filter--active       /* Active filter */
.collection-pagination__link--current /* Current page */
```

### Custom Styling

Override specific components:

```css
.collection-item {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.collection-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
```

## JavaScript API

### Auto-initialization

JavaScript is automatically initialized when using the `collection-manager` snippet.

### Manual Initialization

```javascript
import { CollectionManager } from './lib/index.js';

const manager = new CollectionManager('#my-container', {
  debug: true,
  loadingClass: 'is-loading'
});
```

### Events

```javascript
document.addEventListener('collection:loaded', (event) => {
  console.log('Collection updated:', event.detail);
});

document.addEventListener('collection:error', (event) => {
  console.error('Collection error:', event.detail);
});
```

## Advanced Examples

### Multi-Collection Page

```php
<?php
// In your page controller
return [
    'recentPosts' => CollectionController::handle($page, [
        'collection' => page('blog')->children()->listed()->limit(3),
        'config' => ['pagination' => ['limit' => 3]]
    ])['collection'],
    
    'projects' => CollectionController::handle($page, [
        'collection' => page('work')->children()->listed(),
        'config' => [
            'search' => ['fields' => ['title', 'description']],
            'pagination' => ['limit' => 6]
        ]
    ])
];
?>

<!-- In your template -->
<section>
  <h2>Recent Posts</h2>
  <?php snippet('collection-manager', [
    'collection' => $recentPosts,
    'id' => 'recent-posts'
  ]) ?>
</section>

<section>
  <h2>Projects</h2>
  <?php snippet('collection-manager', [
    'collection' => $projects['collection'],
    'config' => $projects['config'],
    'id' => 'projects'
  ]) ?>
</section>
```

### Custom Collection Source

```php
<?php
// Custom collection with filtering
$products = page('shop')->children()->listed();

// Apply custom business logic
$products = $products->filter(function ($product) {
    return $product->inStock()->toBool() === true;
});

// Group by category
$productsByCategory = $products->group(function ($product) {
    return $product->category()->value();
});

// Use with collection manager
snippet('collection-manager', [
    'collection' => $products,
    'config' => [
        'taxonomies' => [
            ['param' => 'category', 'field' => 'category', 'label' => 'Category'],
            ['param' => 'price', 'field' => 'priceRange', 'label' => 'Price Range']
        ]
    ]
]);
?>
```

## Migration from Demo

If you were using the demo implementation, here's how to migrate:

### Before (Demo)

```php
<!-- site/templates/blog.php -->
<?php snippet('demo/collection-manager', [
  'items' => $page->children()->listed()
]) ?>
```

### After (Plugin)

```php
<!-- site/templates/blog.php -->
<?php snippet('collection-manager', [
  'collection' => $page->children()->listed()
]) ?>
```

Or use the controller pattern:

```php
<!-- site/controllers/blog.php -->
<?php
use KirbyCollectionManager\CollectionController;

return function ($page) {
    return CollectionController::handle($page, [
        'collection' => $page->children()->listed()
    ]);
};
?>

<!-- site/templates/blog.php -->
<?php snippet('collection-manager', compact('collection', 'config')) ?>
```

## Requirements

- Kirby CMS 4.0+
- PHP 8.0+

## License

MIT License. See [LICENSE.md](LICENSE.md) for details.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## Support

- [Documentation](https://github.com/your-namespace/kirby-collection-manager)
- [Issues](https://github.com/your-namespace/kirby-collection-manager/issues)
- [Discussions](https://github.com/your-namespace/kirby-collection-manager/discussions)