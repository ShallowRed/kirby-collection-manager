# Kirby Collection Manager

A powerful Kirby CMS plugin for managing collections with AJAX pagination, search, filtering, and sorting. Provides both a complete out-of-the-box solution and flexible components for custom implementations.

## Features

- 🔍 **Search** - Full-text search across collection items
- 🏷️ **Filtering** - Taxonomy-based filtering (categories, tags, etc.)
- 📄 **Pagination** - htmx-powered AJAX pagination with URL preservation
- 📱 **Responsive** - Mobile-first responsive design
- ⚡ **Performance** - Optimized for large collections
- 🎨 **Customizable** - CSS custom properties + override templates
- ♿ **Accessible** - Screen reader friendly with proper ARIA
- 🚀 **Progressive Enhancement** - Works without JavaScript
- 🌍 **i18n** - Multi-language support (EN, FR, DE included)
- 🌙 **Dark Mode** - Automatic dark mode via CSS custom properties
- 🔧 **Debug Mode** - Built-in debugging for development
- 🪝 **Hooks** - Extensible via Kirby hooks system

## Installation

### Via Composer (Recommended)

```bash
composer require shallowred/kirby-collection-manager
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
      'placeholder' => 'Search items...'
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
- PHP 8.1+

## License

MIT License. See [LICENSE.md](LICENSE.md) for details.

## Internationalization (i18n)

The plugin includes translations for English, French, and German. All user-facing strings use Kirby's `t()` helper.

### Available Languages

- `en` - English (default)
- `fr` - French
- `de` - German

### Adding Custom Translations

Override translations in your `site/config/config.php`:

```php
return [
    'translations' => [
        'en' => [
            'collection.search.placeholder' => 'Find articles...',
            'collection.empty.title' => 'Nothing here yet',
        ],
        'fr' => [
            'collection.search.placeholder' => 'Trouver des articles...',
        ]
    ]
];
```

### Translation Keys Reference

```php
// Search
'collection.search.placeholder'  // Search input placeholder
'collection.search.label'        // Screen reader label
'collection.search.submit'       // Submit button text
'collection.search.clear'        // Clear button tooltip
'collection.search.searching'    // "Searching for:" label

// Filters
'collection.filters.all'         // "All {label}" text
'collection.filters.clear'       // "Clear all filters" text

// Pagination
'collection.pagination.first'    // First page aria-label
'collection.pagination.prev'     // Previous page aria-label
'collection.pagination.next'     // Next page aria-label
'collection.pagination.last'     // Last page aria-label

// Empty state
'collection.empty.title'         // Empty state heading
'collection.empty.filtered'      // Message when filters active
'collection.empty.default'       // Default empty message
```

## CSS Customization

### CSS Custom Properties (v2)

Use `collection-manager-v2.css` for full CSS custom properties support:

```php
<?= css('media/plugins/kirby-collection-manager/collection-manager-v2.css') ?>
```

Override variables in your stylesheet:

```css
:root {
  /* Colors */
  --cm-color-primary: #333;
  --cm-color-bg-active: #007bff;
  --cm-color-link: #007bff;
  
  /* Spacing */
  --cm-spacing-md: 1rem;
  --cm-spacing-lg: 1.5rem;
  
  /* Typography */
  --cm-font-size-base: 1rem;
  
  /* Borders */
  --cm-border-radius-sm: 4px;
  --cm-border-radius-pill: 20px;
}
```

### Dark Mode

Dark mode is automatic via `prefers-color-scheme`:

```css
@media (prefers-color-scheme: dark) {
  /* Automatically applied */
}
```

For manual control, use the `.cm-dark` class or `data-theme="dark"`:

```html
<div class="collection-manager cm-dark">
  <!-- Dark mode active -->
</div>
```

### All CSS Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `--cm-color-primary` | `#333` | Primary text color |
| `--cm-color-secondary` | `#6c757d` | Secondary text color |
| `--cm-color-border` | `#ddd` | Border color |
| `--cm-color-bg` | `#fff` | Background color |
| `--cm-color-bg-hover` | `#f5f5f5` | Hover background |
| `--cm-color-bg-active` | `#007bff` | Active state background |
| `--cm-color-text-on-active` | `#fff` | Text on active background |
| `--cm-spacing-xs` | `0.25rem` | Extra small spacing |
| `--cm-spacing-sm` | `0.5rem` | Small spacing |
| `--cm-spacing-md` | `1rem` | Medium spacing |
| `--cm-spacing-lg` | `1.5rem` | Large spacing |
| `--cm-spacing-xl` | `2rem` | Extra large spacing |
| `--cm-border-radius-sm` | `4px` | Small border radius |
| `--cm-border-radius-pill` | `20px` | Pill border radius |
| `--cm-transition-normal` | `0.2s ease` | Standard transition |

## Debug Mode

Enable debug mode for development to track query performance and applied filters:

### Enable via Config

```php
// site/config/config.php
return [
    'shallowred.collection-manager' => [
        'debug' => true
    ]
];
```

### Debug Output

When enabled, debug info appears in:
- Browser console (JavaScript)
- HTML comments
- AJAX response data

Debug info includes:
- Execution time per stage (search, filter, sort, paginate)
- Applied filters and search query
- Collection counts before/after each stage
- Memory usage

### Using DebugCollector Programmatically

```php
use KirbyCollectionManager\Debug\DebugCollector;

$debug = DebugCollector::enabled();
$debug->startTimer('custom-operation');
// ... your code
$debug->endTimer('custom-operation');
$debug->log('myKey', $myValue);

// Get all debug info
$info = $debug->toArray();

// Output as console script
echo $debug->toConsoleScript();
```

## Hooks / Events

The plugin provides hooks for extending functionality:

### Available Hooks

```php
// site/config/config.php
return [
    'hooks' => [
        // Modify config after resolution
        'collection-manager.config.resolved' => function ($config) {
            $config['pagination']['limit'] = 20;
            return $config;
        },
        
        // Filter collection before query
        'collection-manager.query.before' => function ($collection, $config) {
            return $collection->filter(fn($item) => $item->isListed());
        },
        
        // Process collection after query
        'collection-manager.query.after' => function ($collection, $debug) {
            // Log analytics
            Analytics::track('collection_viewed', [
                'count' => $collection->count()
            ]);
            return $collection;
        },
        
        // Modify snippets before rendering
        'collection-manager.snippets.before' => function ($collection, $config) {
            return [$collection, $config];
        },
        
        // Modify snippets after rendering
        'collection-manager.snippets.after' => function ($snippets, $collection) {
            $snippets['custom'] = '<div>Custom content</div>';
            return $snippets;
        },
        
        // Modify response before sending
        'collection-manager.response.before' => function ($response, $type) {
            $response['timestamp'] = time();
            return $response;
        }
    ]
];
```

### Hook Parameters

| Hook | Parameters | Return |
|------|------------|--------|
| `config.resolved` | `array $config` | `array` |
| `query.before` | `Collection $collection, array $config` | `Collection` |
| `query.after` | `Collection $collection, array $debug` | `Collection` |
| `snippets.before` | `Collection $collection, array $config` | `array` |
| `snippets.after` | `array $snippets, Collection $collection` | `array` |
| `response.before` | `array $response, string $type` | `array` |

## Testing

### E2E Test Selectors

All interactive elements have `data-testid` attributes for reliable testing:

```javascript
// Playwright / Cypress selectors
await page.getByTestId('collection-search-input').fill('query');
await page.getByTestId('collection-search-submit').click();
await page.getByTestId('collection-search-clear').click();

await page.getByTestId('collection-filter-category-all').click();
await page.getByTestId('collection-filter-category-news').click();
await page.getByTestId('collection-filters-clear').click();

await page.getByTestId('collection-pagination-prev').click();
await page.getByTestId('collection-pagination-next').click();
await page.getByTestId('collection-pagination-page-3').click();

await page.getByTestId('collection-items');
await page.getByTestId('collection-item-blog/post-1');
await page.getByTestId('collection-empty');
```

### Available Test IDs

| Element | Test ID Pattern |
|---------|-----------------|
| Search container | `collection-search` |
| Search input | `collection-search-input` |
| Search submit | `collection-search-submit` |
| Search clear | `collection-search-clear` |
| Filters container | `collection-filters` |
| Filter group | `collection-filter-group-{param}` |
| Filter "All" | `collection-filter-{param}-all` |
| Filter option | `collection-filter-{param}-{slug}` |
| Clear all filters | `collection-filters-clear` |
| Pagination nav | `collection-pagination` |
| First page | `collection-pagination-first` |
| Prev page | `collection-pagination-prev` |
| Next page | `collection-pagination-next` |
| Last page | `collection-pagination-last` |
| Page number | `collection-pagination-page-{n}` |
| Items container | `collection-items` |
| Individual item | `collection-item-{id}` |
| Empty state | `collection-empty` |

## htmx Integration

The plugin uses htmx for AJAX functionality. No custom JavaScript required.

### How It Works

```html
<!-- Search form with htmx -->
<form hx-get="/blog" 
      hx-target="#collection-content" 
      hx-swap="innerHTML"
      hx-push-url="true">
  <input type="search" name="q" />
</form>
```

### Disable htmx

For server-side only rendering:

```php
snippet('collection-manager', [
    'collection' => $collection,
    'config' => [
        'enableJs' => false  // Disable htmx attributes
    ]
]);
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests: `pnpm test`
5. Submit a pull request

## Support

- [GitHub Issues](https://github.com/ShallowRed/kirby-collection-manager/issues)
- [Documentation](https://github.com/ShallowRed/kirby-collection-manager)
