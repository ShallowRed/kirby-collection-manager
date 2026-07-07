# Changelog

## 1.1.0 — 2026-07-07

### Added

- `classes` config map: append custom utility classes (Tailwind, UnoCSS,
  DaisyUI…) to every element the plugin renders, while keeping the default
  BEM classes intact. Keys cover wrapper, search, filters, sorting, items,
  item, empty state, pagination and indicator.
- `enableCss` config: auto-inject the bundled stylesheet once per page.
- Default styles for the sorting control and the multi-select active pill
  (small × indicator).

### Changed

- The bundled stylesheet is wrapped in the `collection-manager` cascade
  layer: any unlayered site rule now overrides it without specificity fights.
- Manual dark mode also honors `[data-theme="dark"]`.
- The demo showcases the `classes` map with DaisyUI on top of the default
  stylesheet.

## 1.0.0 — 2026-07-07

First tagged release. Consolidates the plugin on a single, tested pipeline.

### Added

- Visitor-facing sorting UI (`sorting.options` whitelist + `enableSorting`),
  with `field:direction` option keys.
- Multi-select taxonomy filters (`'multiple' => true`), comma-separated param.
- Out-of-range pagination pages are clamped instead of rendering an error page.
- Fragment responses send `Vary: HX-Target`; search input capped at 100 chars.
- Search and sorting forms preserve the other active params via hidden inputs.

### Changed

- **htmx fragment detection now uses the `HX-Target` header** instead of an
  `htmx` query param. Generated URLs are clean and reloadable; the legacy
  param is still honored for old links.
- Filter options are built from the configured collection, not from
  `$page->children()` — custom collections now get correct filters.
- Generated URLs only carry params owned by the instance; unrelated query
  params are no longer reflected into links.
- `hasActiveFilters` only considers the search and configured filter params.
- Back/forward navigation refetches state (htmx history cache disabled).
- The demo runs through Kirby's router and uses the plugin's real snippets.

### Fixed

- `$page->collectionManager()` page/site methods (previously fatal).
- Missing space before attributes in the default item snippet
  (`<article`/`<img`/`<time` rendered as invalid HTML).
- Item date displayed unformatted in the default item snippet.
- Isotope module loaded from a hardcoded path incompatible with composer
  installs.

### Removed

- The unfinished parallel pipeline (`processWithQuery`, `ResponseFactory`,
  `SnippetRenderer`, `RequestDetector`, response handlers), unused config
  DTOs and exceptions, and the never-wired `EventDispatcher`/`DebugCollector`
  (the documented hooks and debug mode never fired).
- Duplicate snippet controllers that recomputed and overrode controller data.
- Legacy pre-htmx JavaScript (`lib/index.legacy.js` and friends) and the v1
  stylesheet (the CSS-variables stylesheet is now `collection-manager.css`).
- Legacy JSON response mode (`?json`).
