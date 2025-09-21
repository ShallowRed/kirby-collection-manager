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

## Configuration Options

### CollectionManager Constructor Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `contentRoute` | `string` | **required** | The route for your collection page |
| `useIsotope` | `boolean` | `false` | Enable Isotope.js animations |
| `isotopeOptions` | `object` | `{}` | Options passed to Isotope constructor |
| `afterReplace` | `function` | `() => {}` | Callback after content replacement |

### Snippet Options

#### collection-pagination

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `$collection` | `Collection` | **required** | The paginated collection |
| `$range` | `integer` | `10` | Number of page links to show |

#### current-page-indicator

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `$collection` | `Collection` | **required** | The paginated collection |

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
