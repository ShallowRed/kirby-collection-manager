# Kirby Collection Manager

Drop-in AJAX-powered collections for Kirby CMS. Search, filter, paginate — all with smooth transitions and zero JavaScript to write.

## ✨ Features

- **Instant Search** — Real-time filtering as users type
- **Smart Filters** — Taxonomy-based filtering that just works
- **AJAX Pagination** — Smooth page transitions, URLs stay bookmarkable
- **Dark Mode** — Automatic theme switching, or control it yourself
- **Multi-language** — English, French, German built-in
- **Accessible** — Proper ARIA labels, keyboard navigation, screen reader support
- **Progressive** — Works without JavaScript, enhances with htmx

## 🚀 Quick Start

```bash
composer require shallowred/kirby-collection-manager
```

Then in any template:

```php
<?php snippet('collection-manager', [
    'collection' => $page->children()->listed()
]) ?>
```

That's it. You have search, filters, and pagination.

## 📖 Configuration

### Full Example

```php
<?php snippet('collection-manager', [
    'collection' => $page->children()->listed(),
    'config' => [
        'search' => [
            'fields' => ['title', 'text', 'author'],
            'placeholder' => 'Find articles...'
        ],
        'taxonomies' => [
            ['param' => 'category', 'field' => 'category', 'label' => 'Category'],
            ['param' => 'year', 'field' => 'date', 'label' => 'Year']
        ],
        'pagination' => [
            'limit' => 12
        ],
        'sorting' => [
            'default' => 'date',
            'direction' => 'desc'
        ]
    ]
]) ?>
```

### Using a Controller

For complex pages, use the controller pattern:

```php
// site/controllers/blog.php
<?php
use KirbyCollectionManager\CollectionController;

return function ($page) {
    return CollectionController::handle($page, [
        'collection' => $page->children()->listed(),
        'config' => [
            'search' => ['fields' => ['title', 'text']],
            'taxonomies' => [
                ['param' => 'category', 'field' => 'category', 'label' => 'Category']
            ],
            'pagination' => ['limit' => 6]
        ]
    ]);
};
```

```php
// site/templates/blog.php
<?php snippet('collection-manager', compact('collection', 'config')) ?>
```

## 🎨 Styling

### Include the CSS

```php
<?= css('media/plugins/kirby-collection-manager/collection-manager-v2.css') ?>
```

### Customize with CSS Variables

Override any property in your stylesheet:

```css
:root {
    --cm-color-bg-active: #e74c3c;     /* Your brand color */
    --cm-color-link: #e74c3c;
    --cm-border-radius-pill: 4px;       /* Square-ish filters */
    --cm-spacing-lg: 2rem;              /* More breathing room */
}
```

### Dark Mode

Automatic via system preferences. Or control manually:

```html
<body class="cm-dark">
    <!-- Dark mode everywhere -->
</body>
```

<details>
<summary>All CSS Variables</summary>

| Variable | Default | Purpose |
|----------|---------|---------|
| `--cm-color-primary` | `#333` | Main text |
| `--cm-color-secondary` | `#6c757d` | Muted text |
| `--cm-color-bg` | `#fff` | Background |
| `--cm-color-bg-active` | `#007bff` | Active state |
| `--cm-color-border` | `#ddd` | Borders |
| `--cm-spacing-sm` | `0.5rem` | Small gaps |
| `--cm-spacing-md` | `1rem` | Medium gaps |
| `--cm-spacing-lg` | `1.5rem` | Large gaps |
| `--cm-border-radius-sm` | `4px` | Buttons |
| `--cm-border-radius-pill` | `20px` | Filter pills |

</details>

## 🌍 Translations

Built-in support for English, French, and German. Add your own:

```php
// site/config/config.php
return [
    'translations' => [
        'es' => [
            'collection.search.placeholder' => 'Buscar...',
            'collection.empty.title' => 'Sin resultados',
            'collection.filters.clear' => 'Borrar filtros'
        ]
    ]
];
```

## 🔧 Custom Templates

Override any component by creating snippets in your site:

```
site/snippets/
├── collection-item.php        # How each item looks
├── collection-search.php      # Search form
├── collection-filters.php     # Filter pills
└── collection-pagination.php  # Page navigation
```

### Example: Custom Item

```php
<!-- site/snippets/collection-item.php -->
<article class="card">
    <?php if ($image = $item->cover()->toFile()): ?>
        <img src="<?= $image->thumb(['width' => 400])->url() ?>" alt="">
    <?php endif ?>
    <h3><?= $item->title() ?></h3>
    <p><?= $item->text()->excerpt(100) ?></p>
    <a href="<?= $item->url() ?>">Read more →</a>
</article>
```

## 🪝 Hooks

Tap into the collection lifecycle:

```php
// site/config/config.php
return [
    'hooks' => [
        // Filter before display
        'collection-manager.query.before' => function ($collection, $config) {
            return $collection->filter(fn($p) => $p->isPublished()->toBool());
        },
        
        // Track views
        'collection-manager.query.after' => function ($collection, $debug) {
            Analytics::track('browse', ['count' => $collection->count()]);
        }
    ]
];
```

Available hooks: `config.resolved`, `query.before`, `query.after`, `snippets.before`, `snippets.after`, `response.before`

## 🐛 Debug Mode

See what's happening under the hood:

```php
// site/config/config.php
return [
    'shallowred.collection-manager' => [
        'debug' => true
    ]
];
```

Opens a console panel showing execution times, applied filters, and collection counts at each stage.

## 📋 Requirements

- Kirby 4.0+
- PHP 8.1+

## 📄 License

MIT — Use it however you like.

---

**[View on GitHub](https://github.com/ShallowRed/kirby-collection-manager)** · **[Report Issues](https://github.com/ShallowRed/kirby-collection-manager/issues)**
