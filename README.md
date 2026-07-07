# Kirby Collection Manager

AJAX-powered collection listings for Kirby CMS: search, filters, sorting and
pagination, rendered server-side and enhanced with [htmx](https://htmx.org).
Works without JavaScript, supports several independent collections on the same
page, and ships with sensible default snippets you can override.

## Installation

```bash
composer require shallowred/kirby-collection-manager
```

Requires PHP 8.1+ and Kirby 4 or 5.

## Quick start

The plugin follows Kirby's controller/template split. In a controller (page
controller, snippet controller or block controller), hand your collection to
`CollectionController::handle()`:

```php
// site/controllers/blog.php
<?php

use KirbyCollectionManager\CollectionController;

return function ($page) {
  return CollectionController::handle($page, [
    'collection' => $page->children()->listed(),
    'search' => [
      'fields' => ['title', 'text'],
    ],
    'taxonomies' => [
      ['param' => 'category', 'field' => 'category', 'label' => 'Category'],
    ],
    'pagination' => [
      'limit' => 10,
    ],
  ]);
};
```

Then render the wrapper snippet in the template:

```php
// site/templates/blog.php
<?php snippet('collection-manager', compact('collection', 'config', 'snippets')) ?>
```

That's it: search form, filter pills, paginated items — with AJAX swaps when
JavaScript is available, plain links and form submits when it is not.

There is also a shorthand page method:

```php
$data = $page->collectionManager(['pagination' => ['limit' => 6]]);
```

## Configuration

All keys are optional; defaults shown below.

```php
CollectionController::handle($page, [
  // The collection to manage: a Kirby collection object, or 'children'
  'collection' => 'children',
  'collectionMethod' => 'listed',   // used when collection is resolved from the page

  'search' => [
    'fields' => ['title', 'text'],  // fields to search
    'placeholder' => null,          // defaults to the translated placeholder
    'param' => 'q',                 // query param
  ],

  'taxonomies' => [
    [
      'param' => 'category',        // query param
      'field' => 'category',        // content field (comma-separated values supported)
      'label' => 'Category',
      'multiple' => false,          // true = multi-select (toggleable pills, comma-separated param)
    ],
  ],

  'pagination' => [
    'limit' => 10,
    'param' => 'p',                 // use a distinct param per instance on multi-collection pages
    'range' => 5,                   // number of page links
  ],

  'sorting' => [
    'default' => 'date',            // field, or 'field:direction'
    'direction' => 'desc',
    'options' => [],                // sort options offered to the visitor (whitelist)
    'param' => 'sort',
  ],

  'enableSearch' => true,
  'enableFilters' => true,
  'enableSorting' => false,         // requires sorting.options
  'enableIndicator' => true,
  'enablePagination' => true,
  'enableJs' => true,               // set false to disable htmx entirely

  'snippets' => [                   // override any snippet by name
    'wrapper' => 'collection-manager',
    'items' => 'collection-items',
    'item' => 'collection-item',
    'pagination' => 'collection-pagination',
    'filters' => 'collection-filters',
    'search' => 'collection-search',
    'sorting' => 'collection-sorting',
    'indicator' => 'current-page-indicator',
  ],
]);
```

### Visitor-facing sorting

Offer sort options with the `field:direction` syntax (direction defaults to
`sorting.direction`). Requested values are validated against this whitelist.

```php
'sorting' => [
  'default' => 'date',
  'direction' => 'desc',
  'options' => [
    'date' => 'Newest first',
    'date:asc' => 'Oldest first',
    'title:asc' => 'Title A→Z',
  ],
],
'enableSorting' => true,
```

### Multi-select filters

Set `'multiple' => true` on a taxonomy to let visitors combine values. Options
toggle in and out of a comma-separated param (`?category=Design,Engineering`)
and items match when any of their values match.

### Several collections on one page

Give each instance its own pagination param — everything else (fragment
targeting, URL generation) is scoped automatically:

```php
CollectionController::handle($page, ['pagination' => ['param' => 'page-news'], ...]);
CollectionController::handle($page, ['pagination' => ['param' => 'page-events'], ...]);
```

## Custom item template

Point `snippets.item` to your own snippet; it receives `$item` (the page),
`$orderIndex` and `$config`:

```php
'snippets' => ['item' => 'article-card'],
```

```php
<!-- site/snippets/article-card.php -->
<article class="card">
  <h3><a href="<?= $item->url() ?>"><?= $item->title()->esc() ?></a></h3>
  <p><?= $item->text()->excerpt(100) ?></p>
</article>
```

Any other snippet (search, filters, pagination, sorting, indicator, wrapper)
can be overridden the same way, or globally by creating a snippet with the
same name in `site/snippets/`.

## Styling

Three levels of customization, from lightest to fullest. They compose freely.

### 1. Default stylesheet + CSS variables

Enable the bundled stylesheet (injected once, however many instances):

```php
CollectionController::handle($page, [
  'enableCss' => true,
  // ...
]);
```

Or include it manually:

```php
<?= css(kirby()->plugin('shallowred/collection-manager')->asset('collection-manager.css')->url()) ?>
```

The whole file lives in the `collection-manager` [cascade layer](https://developer.mozilla.org/en-US/docs/Web/CSS/@layer),
so **any rule in your own stylesheet overrides it** — no specificity fights.
Theme it by overriding the `--cm-*` variables:

```css
:root {
  --cm-color-bg-active: #a86f1c;      /* brand color for active states */
  --cm-border-radius-pill: 4px;       /* square-ish filter pills */
  --cm-grid-min-width: 320px;         /* wider item cards */
}
```

Dark mode follows `prefers-color-scheme` automatically, or force it with a
`.cm-dark` class (also honors `[data-theme="dark"]`).

<details>
<summary>All CSS variables</summary>

| Variable | Default | Purpose |
|----------|---------|---------|
| `--cm-color-primary` | `#333` | Main text |
| `--cm-color-secondary` | `#6c757d` | Muted text |
| `--cm-color-border` | `#ddd` | Borders |
| `--cm-color-bg` | `#fff` | Surfaces |
| `--cm-color-bg-hover` | `#f5f5f5` | Hover surfaces |
| `--cm-color-bg-muted` | `#f8f9fa` | Muted surfaces (pills, buttons) |
| `--cm-color-bg-active` | `#007bff` | Active filter / current page |
| `--cm-color-text-on-active` | `#fff` | Text on active background |
| `--cm-color-link` / `--cm-color-link-hover` | `#007bff` / `#0056b3` | Links |
| `--cm-color-danger` | `#dc3545` | Clear-filters link |
| `--cm-color-info-bg` | `#e3f2fd` | "Searching for" banner |
| `--cm-color-shadow` | `rgba(0,0,0,.1)` | Card hover shadow |
| `--cm-spacing-xs/sm/md/lg/xl` | `.25/.5/1/1.5/2rem` | Spacing scale |
| `--cm-font-size-sm/base/lg` | `.875/1/1.125rem` | Type scale |
| `--cm-border-radius-sm/md/pill` | `4px/8px/20px` | Radii |
| `--cm-transition-fast/normal` | `.15s/.2s ease` | Transitions |
| `--cm-search-max-width` | `400px` | Search field width |
| `--cm-item-image-height` | `200px` | Card image height |
| `--cm-pagination-button-size` | `2.5rem` | Pagination buttons |
| `--cm-grid-min-width` | `280px` | Items grid min column |

</details>

### 2. Custom classes (Tailwind / UnoCSS / DaisyUI)

The `classes` config appends your utility classes to every element the plugin
renders — the default BEM classes stay in place, so behavior, tests and the
default stylesheet keep working:

```php
CollectionController::handle($page, [
  'classes' => [
    'item' => 'card bg-base-100 shadow-sm',
    'filter' => 'badge badge-outline',
    'filterActive' => 'badge-primary',
    'sortingSelect' => 'select select-sm',
    'searchInput' => 'input input-bordered',
  ],
  // ...
]);
```

Available keys: `wrapper`, `search`, `searchInput`, `searchSubmit`,
`searchClear`, `filters`, `filterGroup`, `filterLabel`, `filter`,
`filterActive`, `filtersClear`, `sorting`, `sortingLabel`, `sortingSelect`,
`items`, `item`, `empty`, `pagination`, `paginationItem`, `paginationIcon`,
`indicator`.

### 3. Custom snippets

For full control over the markup, override any snippet (see below).

## How the AJAX layer works

The wrapper snippet loads a bundled htmx (once per page, however many
instances there are) and every control targets the instance's content
element. The server detects fragment requests through the `HX-Target` header,
so URLs stay clean and shareable — a reload of any URL renders the full page.
Fragment responses send `Vary: HX-Target` for cache correctness. Out-of-range
page numbers (e.g. a stale link to page 3 of a filtered list) are clamped to
the nearest valid page instead of erroring.

## Translations

English, French and German are built in. Override or add languages in your
config:

```php
'translations' => [
  'es' => [
    'collection.search.placeholder' => 'Buscar...',
    'collection.empty.title' => 'Sin resultados',
  ],
],
```

## Demo & tests

A demo site lives in `demo/` (requires a Kirby core in `demo/kirby`, not
versioned). The Playwright end-to-end suite runs against it:

```bash
npm install
npm test
```

PHP linting: `composer lint`.

## License

MIT
