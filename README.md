# Kirby Collection Manager

A powerful Kirby CMS plugin for advanced collection management with AJAX pagination, filtering, and search functionality. This plugin provides both server-side PHP snippets and client-side JavaScript tools to create seamless, dynamic collection experiences without page reloads.

## Features

- 🔄 **AJAX Pagination**: Navigate through collections without page reloads
- 🔍 **Dynamic Search**: Real-time search functionality with URL state management
- 🏷️ **Taxonomy Filtering**: Filter collections by categories, tags, or custom fields
- 📱 **Mobile-friendly**: Responsive pagination with touch support
- ♿ **Accessible**: ARIA labels and keyboard navigation support
- 🎨 **Isotope Integration**: Optional smooth animations with Isotope.js
- 🌐 **URL Management**: Maintains browser history and shareable URLs
- 🎯 **Flexible**: Works with any Kirby collection (pages, files, etc.)

## Installation

### Download

Download and copy this repository to `/site/plugins/kirby-collection-manager`.

### Git submodule

```bash
git submodule add https://github.com/ShallowRed/kirby-collection-manager.git site/plugins/kirby-collection-manager
```

### Composer

```bash
composer require shallowred/kirby-collection-manager
```

## Quick Start

### 1. PHP Templates

Use the provided snippets in your templates:

```php
<?php
// Get your collection (example with articles)
$articles = page('blog')->children()->listed()->paginate(10);
?>

<!-- Display your collection items -->
<?php foreach ($articles as $article): ?>
  <article data-id="<?= $article->id() ?>" data-order="<?= $article->indexOf() ?>">
    <h2><?= $article->title() ?></h2>
    <p><?= $article->text()->excerpt(200) ?></p>
  </article>
<?php endforeach ?>

<!-- Add pagination -->
<?php snippet('collection-pagination', ['collection' => $articles]) ?>

<!-- Add page indicator -->
<?php snippet('current-page-indicator', ['collection' => $articles]) ?>
```

### 2. JavaScript Integration

Include the JavaScript library and initialize:

```html
<!-- Include the library -->
<script type="module">
import { CollectionManager } from '/site/plugins/kirby-collection-manager/lib/index.js';

// Initialize collection manager
const manager = new CollectionManager({
  contentRoute: '/blog', // Your collection page route
  useIsotope: false,     // Set to true for animations
  afterReplace: () => {
    console.log('Content updated!');
  }
});

// Set up event listeners
document.querySelectorAll('.collection-pagination a').forEach(link => {
  manager.listenPaginationEvent(link);
});
</script>
```

## Advanced Usage

### AJAX Content Replacement

For AJAX functionality, your route should return JSON with replacement instructions:

```php
// In your page controller or route
if ($kirby->request()->is('GET') && get('json')) {
  return [
    'replacements' => [
      [
        'containerSelector' => '.articles-container',
        'itemSelector' => 'article',
        'data' => snippet('articles-list', ['articles' => $articles], true)
      ]
    ]
  ];
}
```

### Search Functionality

Add a search form:

```php
<form class="search-form">
  <input type="search" name="q" placeholder="Search articles...">
  <input type="submit" value="Search">
</form>
```

```javascript
// Listen for search events
const searchForm = document.querySelector('.search-form');
manager.listenSearchEvent(searchForm);
```

### Taxonomy Filtering

Add filter links:

```php
<!-- Category filters -->
<?php foreach (page('blog')->children()->pluck('category', ',', true) as $category): ?>
  <a href="#" data-param="category" data-value="<?= $category ?>">
    <?= $category ?>
  </a>
<?php endforeach ?>
```

```javascript
// Listen for taxonomy events
document.querySelectorAll('[data-param]').forEach(link => {
  manager.listenTaxonomyEvent(link);
});
```

### Isotope Integration

For smooth animations, include Isotope.js:

```javascript
const manager = new CollectionManager({
  contentRoute: '/blog',
  useIsotope: true,
  isotopeOptions: {
    layoutMode: 'fitRows',
    transitionDuration: '0.3s'
  }
});

// Load Isotope library
await manager.loadIsotope();
```

## Configuration

The plugin can be configured in your `site/config/config.php` file:

```php
return [
  'shallowred.collection-manager' => [
    'pagination' => [
      'range' => 10, // Number of page links to show
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
  ]
];
```

### Internationalization

The plugin supports multiple languages through configuration:

```php
// French
'texts' => [
  'firstPage' => 'Aller à la première page',
  'prevPage' => 'Aller à la page précédente',
  'nextPage' => 'Aller à la page suivante',
  'lastPage' => 'Aller à la dernière page',
  'pageIndicator' => 'Page {current} sur {total}',
]

// German
'texts' => [
  'firstPage' => 'Zur ersten Seite',
  'prevPage' => 'Zur vorherigen Seite',
  'nextPage' => 'Zur nächsten Seite',
  'lastPage' => 'Zur letzten Seite',
  'pageIndicator' => 'Seite {current} von {total}',
]
```

See `config/config.example.php` for more examples.

### CollectionManager Constructor Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `contentRoute` | `string` | **required** | The route for your collection page |
| `useIsotope` | `boolean` | `false` | Enable Isotope.js animations |
| `isotopeOptions` | `object` | `{}` | Options passed to Isotope constructor |
| `afterReplace` | `function` | `() => {}` | Callback after content replacement |
| `debug` | `boolean` | `false` | Enable debug logging |

### Snippet Options

#### collection-pagination

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `$collection` | `Collection` | **required** | The paginated collection |
| `$range` | `integer` | config value or `10` | Number of page links to show |

#### current-page-indicator

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `$collection` | `Collection` | **required** | The paginated collection |
| `$format` | `string` | config value | Custom format string with {current} and {total} placeholders |

## Error Handling

The plugin includes comprehensive error handling and validation:

### PHP Error Handling
- **Debug Mode**: In debug mode (`debug: true` in Kirby config), exceptions are thrown for missing parameters
- **Production Mode**: In production, errors are handled gracefully with silent failures
- **Parameter Validation**: All snippet parameters are validated for type and content
- **Pagination Validation**: Ensures collections have proper pagination methods

### JavaScript Error Handling
- **Input Validation**: All method parameters are validated before processing
- **Network Errors**: HTTP errors are caught and handled with fallback navigation
- **Abort Support**: Requests can be cancelled to prevent race conditions
- **Memory Management**: Uses Map for Isotope instances and WeakMap for event listeners
- **Resource Cleanup**: `destroy()` method for proper cleanup

### Debug Mode
Enable detailed logging in development:

```javascript
const manager = new CollectionManager({
  contentRoute: '/blog',
  debug: true // Enables console logging
});
```

## TypeScript Support

The plugin includes TypeScript definitions in `lib/index.d.ts`:

```typescript
import { CollectionManager, CollectionManagerOptions } from './lib/index.js';

const options: CollectionManagerOptions = {
  contentRoute: '/blog',
  useIsotope: true,
  debug: true
};

const manager = new CollectionManager(options);
```

## Advanced Usage

### Memory Management
```javascript
// Clean up resources when done
manager.destroy();
```

### Request Cancellation
Requests are automatically cancelled when new ones are made, preventing race conditions.

### Custom Validation
```javascript
// The library includes built-in validation for all inputs
try {
  await manager.paginate('invalid'); // Will throw validation error
} catch (error) {
  console.error('Validation failed:', error.message);
}
```

## Graceful Degradation

The plugin works without JavaScript enabled:
- Pagination links work as normal page links
- Forms submit normally
- Content is accessible and functional

## Styling

### Default CSS

The plugin includes comprehensive CSS files:

- **Main styles**: `assets/collection-manager.css` - Core pagination and indicator styles
- **Utility classes**: `assets/utilities.css` - Additional layout and component utilities

Include them in your template:

```html
<?= css('/site/plugins/kirby-collection-manager/assets/collection-manager.css') ?>
<?= css('/site/plugins/kirby-collection-manager/assets/utilities.css') ?>
```

### Utility Classes

The plugin provides helpful utility classes:

```css
/* Layout */
.collection-grid, .collection-grid--2-cols, .collection-grid--3-cols
.collection-flex, .collection-flex--center

/* Loading states */
.collection-loading

/* Filter controls */
.collection-filters, .collection-filter, .collection-filter--active

/* Search forms */
.collection-search, .collection-search__input, .collection-search__submit

/* Item cards */
.collection-item, .collection-item__content, .collection-item__title

/* Empty states */
.collection-empty, .collection-empty__title, .collection-empty__message
```

### CSS Framework Integration

#### Bootstrap
```php
'cssClasses' => [
  'nav' => 'pagination justify-content-center',
  'item' => 'page-item',
  'icon' => 'page-link',
]
```

#### Tailwind CSS
```php
'cssClasses' => [
  'nav' => 'flex justify-center items-center space-x-2',
  'item' => 'inline-block',
  'icon' => 'px-3 py-2 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50',
]
```

## CSS Classes

The plugin generates semantic CSS classes for easy styling:

```css
/* Pagination */
.collection-pagination { }
.collection-pagination__item { }
.collection-pagination__item--to-first { }
.collection-pagination__item--to-sibling { }
.collection-pagination__item--to-number { }
.collection-pagination__item--to-last { }
.collection-pagination__icon { }
.collection-pagination__icon--first { }
.collection-pagination__icon--prev { }
.collection-pagination__icon--next { }
.collection-pagination__icon--last { }

/* Page indicator */
.current-page-indicator { }
```

## Data Attributes

For proper AJAX functionality, ensure your collection items have:

- `data-id`: Unique identifier for the item
- `data-order`: Sort order for proper insertion

## Browser Support

- Modern browsers with ES6 module support
- Progressive enhancement (works without JavaScript)
- Graceful degradation for older browsers

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

## Changelog

### 1.0.0
- Initial release
- AJAX pagination functionality
- Search and filtering capabilities
- Isotope.js integration
- Accessibility features

## Development

*Add instructions on how to help working on the plugin (e.g. npm setup, Composer dev dependencies, etc.)*

## License

MIT

## Credits

- [Your Name](https://github.com/ghost)
