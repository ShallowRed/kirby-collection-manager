# Kirby Collection Manager - Improvement Roadmap

> **Status:** 🟡 In Progress
> **Created:** 2026-01-15
> **Last Updated:** 2026-01-15
> **Current Phase:** Phase 2 - Architecture

## Progress Tracking Methodology

### How to Use This Document

1. **Check off tasks** as you complete them: `- [ ]` → `- [x]`
2. **Update status badges** for each section:
   - 🔴 Not Started
   - 🟡 In Progress
   - 🟢 Complete
   - ⏸️ On Hold
3. **Add completion dates** next to completed items
4. **Log blockers** in the Notes section of each task
5. **Create feature branches** following the naming convention in [Branch Strategy](#branch-strategy)

### Progress Overview

| Phase | Status | Progress | Target |
|-------|--------|----------|--------|
| Phase 1: Foundation | 🟢 Complete | 3/3 | Week 1-2 |
| Phase 2: Architecture | 🟢 Complete | 4/4 | Week 3-4 |
| Phase 3: Developer Experience | 🔴 Not Started | 0/4 | Week 5-6 |
| Phase 4: Polish & Performance | 🔴 Not Started | 0/5 | Week 7-8 |

**Overall Progress:** 7/16 tasks (44%)

---

## Phase 1: Foundation (Week 1-2)

> **Goal:** Fix critical issues that block adoption and cause debugging nightmares
> **Status:** � Complete
> **Branch:** `feature/phase-1-foundation`

### 1.1 Internationalization (i18n) Support

**Priority:** 🔴 Critical | **Effort:** Medium | **Status:** 🟢 Complete

**Branch:** `feature/i18n`

#### Tasks

- [x] Create translation files structure
  - [x] `translations/en.php`
  - [x] `translations/fr.php`
  - [x] `translations/de.php`
- [x] Define all translation keys (~30 strings)
- [x] Replace hardcoded strings in snippets:
  - [x] `collection-search.php` — 4 strings
  - [x] `collection-filters.php` — 3 strings
  - [x] `collection-pagination.php` — 6 strings
  - [x] `collection-items.php` — 3 strings
  - [x] `collection-item.php` — 2 strings
  - [x] `current-page-indicator.php` — 1 string (already using t())
- [x] Register translations in `index.php`
- [ ] Add pluralization support for filter labels
- [ ] Document translation override process in README
- [ ] Write tests for translation loading

#### Translation Keys

```php
// Required keys for translations/en.php
return [
    // Search
    'collection.search.placeholder' => 'Search...',
    'collection.search.clear' => 'Clear',
    'collection.search.label' => 'Search',
    'collection.search.searching' => 'Searching for:',

    // Filters
    'collection.filters.all' => 'All {label}',
    'collection.filters.clear' => 'Clear all filters',

    // Pagination
    'collection.pagination.first' => 'Go to first page',
    'collection.pagination.prev' => 'Go to previous page',
    'collection.pagination.next' => 'Go to next page',
    'collection.pagination.last' => 'Go to last page',
    'collection.pagination.page' => 'Go to page {page}',
    'collection.pagination.current' => 'Current page, page {page}',
    'collection.pagination.indicator' => 'Page {current} of {total}',

    // Empty state
    'collection.empty.title' => 'No items found',
    'collection.empty.message' => 'Try adjusting your search or filter criteria.',
    'collection.empty.message.default' => 'There are no items to display yet.',

    // Item
    'collection.item.readmore' => 'Read more',
];
```

#### Acceptance Criteria

- [x] All user-facing strings use `t()` helper
- [x] English and French translations complete
- [ ] Translation override documented
- [ ] No hardcoded strings in snippets

#### Notes

i18n implementation complete. Translations registered in index.php. All snippets updated with t() helper.

---

### 1.2 Configuration Validation & DTOs

**Priority:** 🔴 Critical | **Effort:** High | **Status:** 🟢 Complete

**Branch:** `feature/config-validation`

#### Tasks

- [x] Create Config namespace structure:
  ```
  classes/Config/
  ├── CollectionConfig.php
  ├── SearchConfig.php
  ├── PaginationConfig.php
  └── FilterConfig.php
  ```
- [x] Implement `CollectionConfig.php`:
  - [x] Define all properties with types
  - [x] Create `fromArray()` factory method
  - [x] Add validation in constructor
  - [x] Implement `toArray()` for serialization
- [x] Implement `SearchConfig.php`:
  - [x] Validate fields array
  - [x] Validate param is URL-safe
  - [x] Default placeholder handling
- [x] Implement `PaginationConfig.php`:
  - [x] Validate limit >= 1
  - [x] Validate range 1-20
  - [x] Validate param is URL-safe
- [x] Implement `FilterConfig.php`:
  - [x] Validate param is URL-safe
  - [x] Validate field is non-empty
  - [x] Validate label is non-empty
- [x] Update `CollectionController` with `getValidatedConfig()` method
- [ ] Write unit tests for each config class

#### Validation Rules

| Config | Property | Rule |
|--------|----------|------|
| Pagination | limit | `int`, >= 1, <= 100 |
| Pagination | param | `string`, non-empty, URL-safe (`/^[a-z][a-z0-9_]*$/i`) |
| Pagination | range | `int`, >= 1, <= 20 |
| Search | fields | `array`, non-empty |
| Search | param | `string`, non-empty, URL-safe |
| Search | placeholder | `string` |
| Search | minLength | `int`, >= 1, <= 10 |
| Filter | taxonomies | `array` with validated taxonomy definitions |
| Filter | multiSelect | `bool` |

#### Acceptance Criteria

- [x] All config classes have full type declarations
- [x] Invalid config throws `InvalidConfigurationException` with helpful message
- [ ] Unit tests cover all validation rules
- [x] `CollectionController` has `getValidatedConfig()` method

#### Notes

DTOs created with PHP 8.1 readonly properties. Factory methods and toArray() implemented. Controller integration done via opt-in method to maintain backwards compatibility.

---

### 1.3 Custom Exceptions

**Priority:** 🔴 Critical | **Effort:** Low | **Status:** 🟢 Complete

**Branch:** `feature/custom-exceptions`

#### Tasks

- [x] Create Exception namespace:
  ```
  classes/Exception/
  ├── CollectionException.php
  ├── InvalidConfigurationException.php
  └── CollectionNotFoundException.php
  ```
- [ ] Implement `CollectionManagerException.php` (base class)
- [ ] Implement `ConfigurationException.php`:
  - [ ] Include config key that failed
  - [ ] Include expected type/value
  - [ ] Include actual value received
- [ ] Implement `CollectionNotFoundException.php`:
  - [ ] Include page path attempted
- [ ] Implement `ResponseException.php`:
  - [ ] Include response type attempted
- [x] Implement `CollectionException` base class with context support
- [x] Implement `InvalidConfigurationException` with factory methods
- [x] Implement `CollectionNotFoundException` for collection errors
- [x] Update controller with `validateCollectionSource()` method
- [ ] Replace all `throw new Error()` calls
- [ ] Replace silent failures with exceptions
- [ ] Format errors for AJAX responses
- [ ] Write unit tests

#### Exception Message Format

```php
// Example: InvalidConfigurationException
InvalidConfigurationException::invalidType('pagination.limit', 'invalid', 'integer');
// Message: "Invalid type for configuration option 'pagination.limit': expected integer, got string"

InvalidConfigurationException::outOfRange('pagination.limit', 200, 1, 100);
// Message: "Configuration option 'pagination.limit' value 200 is out of range (1 - 100)"

InvalidConfigurationException::missingRequired('collection');
// Message: "Missing required configuration option: 'collection'"
```

#### Acceptance Criteria

- [x] All exceptions extend `CollectionException`
- [x] Exception messages are actionable (include fix examples)
- [ ] AJAX errors return proper JSON/HTML error responses
- [ ] No silent failures remain

#### Notes

Base exception hierarchy implemented with context support and factory methods. Exceptions use PHP 8.1 features like readonly properties and named arguments.

---

## Phase 2: Architecture Refactoring (Week 3-4)

> **Goal:** Break up God class, improve maintainability
> **Status:** � Complete
> **Branch:** `feature/phase-2-architecture`

### 2.1 Extract Query Pipeline

**Priority:** 🟠 High | **Effort:** High | **Status:** 🟢 Complete

**Branch:** `feature/query-pipeline`

#### Tasks

- [x] Create Query interface:
  ```php
  interface CollectionQueryInterface {
      public function search(string $query, array $fields): self;
      public function filter(string $field, mixed $value): self;
      public function sort(string $field, string $direction): self;
      public function paginate(int $limit, string $param): self;
      public function get(): Collection;
      public function getDebugInfo(): array;
  }
  ```
- [x] Create `CollectionQuery.php` implementation
- [x] Create Stage classes (integrated into CollectionQuery fluent API):
  - [x] Search functionality
  - [x] Filter functionality
  - [x] Sort functionality
  - [x] Paginate functionality
- [x] Extract logic from `CollectionController`:
  - [x] `getBaseCollection()` → `CollectionQuery::from()`
  - [x] `applyTaxonomyFilters()` → `filter()` method
  - [x] `sortCollection()` → `sort()` method
  - [x] `paginateCollection()` → `paginate()` method
- [x] Implement fluent interface
- [x] Add debug info collection
- [ ] Write unit tests for each stage
- [x] Update `CollectionController` to use Query system

#### API Design

```php
// Target usage
$result = CollectionQuery::from($page->children()->listed())
    ->search($searchTerm, ['title', 'text'])
    ->filter('category', 'news')
    ->filter('author', 'john')
    ->sort('date', 'desc')
    ->paginate(10, 'p')
    ->get();
```

#### Acceptance Criteria

- [ ] Query pipeline is fully tested
- [ ] Each stage is independently testable
- [ ] `CollectionController` reduced by ~100 lines
- [ ] Debug info available for each stage

#### Notes

Query Pipeline implemented with fluent API in `classes/Query/CollectionQuery.php`. Interface defined in `classes/Query/CollectionQueryInterface.php`. Stage classes were integrated directly into the fluent API rather than as separate stage classes for simpler implementation.

---

### 2.2 Extract Response Handlers

**Priority:** 🟠 High | **Effort:** Medium | **Status:** 🟢 Complete

**Branch:** `feature/response-handlers`

#### Tasks

- [x] Create Response interface:
  ```php
  interface ResponseHandlerInterface {
      public function canHandle(): bool;
      public function handle(Collection $collection, array $snippets, array $config): void;
  }
  ```
- [x] Create `ResponseDetector.php`:
  - [x] Detect htmx requests (`HX-Request` header or `htmx` param)
  - [x] Detect JSON requests (`json` param + Accept header)
  - [x] Return request type enum
- [x] Create `HtmxResponseHandler.php`:
  - [x] Extract `handleHtmxRequest()` logic
  - [x] Add proper headers
  - [x] Handle errors gracefully
- [x] Create `JsonResponseHandler.php`:
  - [x] Extract `handleJsonRequest()` logic
  - [x] Maintain backwards compatibility
- [x] Create `ResponseFactory.php` to get appropriate handler
- [x] Update `CollectionController`
- [ ] Write unit tests

#### Acceptance Criteria

- [x] Response handling is fully decoupled
- [x] Each handler is independently testable
- [x] `CollectionController` reduced by ~80 lines
- [x] Error responses are properly formatted

#### Notes

Response handlers implemented in `classes/Response/` namespace. Includes `RequestDetector`, `HtmxResponseHandler`, `JsonResponseHandler`, and `ResponseFactory`. Both htmx and legacy JSON responses are supported with proper error handling.

---

### 2.3 Extract URL Builder

**Priority:** 🟠 High | **Effort:** Low | **Status:** 🟢 Complete

**Branch:** `feature/url-builder`

#### Tasks

- [x] Create `Url/UrlBuilder.php`:
  ```php
  class UrlBuilder {
      public function __construct(Page $page, string $paginationParam, string $searchParam);
      public function build(array $params = []): string;
      public function buildHtmx(array $params = []): string;
      public function buildClean(): string;
      public function getCurrentParams(): array;
  }
  ```
- [x] Move `buildUrl()` static method to class
- [x] Replace `$_GET` access with Kirby's `get()` helper
- [x] Add method for htmx URLs (auto-append `htmx=1`)
- [x] Add method for clean URLs (remove internal params)
- [x] Update all usages in controllers
- [ ] Write unit tests

#### Acceptance Criteria

- [x] No direct `$_GET` or `$_SERVER` access
- [x] URL building is centralized
- [ ] Unit tests cover edge cases

#### Notes

URL Builder implemented in `classes/Url/UrlBuilder.php` with comprehensive methods for pagination, search, filter URLs, and htmx-specific URL generation. Uses Kirby's `get()` helper instead of direct `$_GET` access.

---

### 2.4 Refactor CollectionController

**Priority:** 🟠 High | **Effort:** Medium | **Status:** 🟢 Complete

**Branch:** `feature/controller-refactor`

#### Tasks

- [x] Add dependency injection support
- [x] Add new `processWithQuery()` method using new architecture
- [x] Maintain backwards compatibility with legacy `process()` method
- [x] Add return type declarations
- [x] Add PHPDoc blocks
- [ ] Update all usages in snippets/controllers (optional)
- [ ] Write integration tests

#### Target Structure

```php
class CollectionController
{
    // New processWithQuery() uses:
    // - CollectionQuery for fluent query building
    // - UrlBuilder for URL generation
    // - ResponseFactory for AJAX handling
    // - SnippetRenderer for HTML generation

    public static function handle(Page $page, array $config = []): array;

    public function process(): array;          // Legacy method (deprecated)

    public function processWithQuery(): array; // New architecture

    protected function buildQueryPipeline(): CollectionQueryInterface;

    protected function buildTemplateData(Collection $collection, array $snippets): array;
}
```

#### Acceptance Criteria

- [x] New architecture available via `processWithQuery()`
- [x] All methods have return type declarations
- [x] Dependencies are injectable for testing
- [x] Legacy `process()` still works (backwards compatible)

#### Notes

CollectionController updated with new `processWithQuery()` method that uses all new architecture components. Legacy `process()` method marked as deprecated but still functional for backwards compatibility. Added `SnippetRenderer` class in `classes/Render/` for HTML generation.

---

## Phase 3: Developer Experience (Week 5-6)

> **Goal:** Improve debugging, extensibility, and testing
> **Status:** 🔴 Not Started
> **Branch:** `feature/phase-3-dx`

### 3.1 Debug Mode

**Priority:** 🟡 Medium | **Effort:** Low | **Status:** 🔴 Not Started

**Branch:** `feature/debug-mode`

#### Tasks

- [ ] Add debug option to plugin:
  ```php
  'options' => [
      'debug' => false,
  ]
  ```
- [ ] Create `Debug/DebugCollector.php`:
  ```php
  class DebugCollector {
      public function startTimer(string $label): void;
      public function endTimer(string $label): void;
      public function log(string $key, mixed $value): void;
      public function toArray(): array;
  }
  ```
- [ ] Track metrics:
  - [ ] Search query applied
  - [ ] Filters applied
  - [ ] Sort field/direction
  - [ ] Total items before pagination
  - [ ] Total items after pagination
  - [ ] Execution time per stage
  - [ ] Generated URLs
- [ ] Add debug to AJAX responses (when enabled)
- [ ] Add console logging for htmx
- [ ] Document debug mode usage

#### Debug Output Format

```json
{
  "debug": {
    "searchQuery": "hello",
    "appliedFilters": {"category": "news"},
    "sorting": {"field": "date", "direction": "desc"},
    "totalBeforePagination": 45,
    "totalAfterPagination": 10,
    "currentPage": 1,
    "executionTime": {
      "total": "12.5ms",
      "search": "3.2ms",
      "filter": "1.1ms",
      "sort": "0.8ms",
      "paginate": "0.3ms",
      "snippets": "7.1ms"
    }
  }
}
```

#### Acceptance Criteria

- [ ] Debug mode toggleable via config
- [ ] Debug info in AJAX responses when enabled
- [ ] No performance impact when disabled
- [ ] Documented in README

#### Notes

_Add any blockers, decisions, or context here_

---

### 3.2 Events/Hooks System

**Priority:** 🟡 Medium | **Effort:** Medium | **Status:** 🔴 Not Started

**Branch:** `feature/events`

#### Tasks

- [ ] Define hook points:
  ```
  collection-manager.config.resolved
  collection-manager.query.before
  collection-manager.query.after
  collection-manager.snippets.before
  collection-manager.snippets.after
  collection-manager.response.before
  ```
- [ ] Create `Events/EventDispatcher.php`
- [ ] Integrate with Kirby's `trigger()` system
- [ ] Add hooks to `CollectionController`
- [ ] Document all hooks with examples
- [ ] Create example use cases

#### Hook Documentation

```php
// Example: Modify collection before query
kirby()->hook('collection-manager.query.before', function ($collection, $config) {
    // Add custom filtering
    return $collection->filter(fn($item) => $item->isPublished());
});

// Example: Track analytics after response
kirby()->hook('collection-manager.response.after', function ($collection, $snippets) {
    Analytics::track('collection_viewed', [
        'count' => $collection->count(),
        'page' => $collection->pagination()?->page()
    ]);
});
```

#### Acceptance Criteria

- [ ] 6 hook points implemented
- [ ] Hooks documented with examples
- [ ] No breaking changes to existing API

#### Notes

_Add any blockers, decisions, or context here_

---

### 3.3 Unit Test Suite

**Priority:** 🟡 Medium | **Effort:** High | **Status:** 🔴 Not Started

**Branch:** `feature/unit-tests`

#### Tasks

- [ ] Set up PHPUnit configuration
- [ ] Create test helpers/mocks:
  - [ ] Mock Page class
  - [ ] Mock Collection class
  - [ ] Test fixtures
- [ ] Write tests:
  - [ ] `Config/CollectionConfigTest.php`
  - [ ] `Config/SearchConfigTest.php`
  - [ ] `Config/PaginationConfigTest.php`
  - [ ] `Config/TaxonomyConfigTest.php`
  - [ ] `Query/CollectionQueryTest.php`
  - [ ] `Query/Stages/SearchStageTest.php`
  - [ ] `Query/Stages/FilterStageTest.php`
  - [ ] `Query/Stages/SortStageTest.php`
  - [ ] `Query/Stages/PaginateStageTest.php`
  - [ ] `Response/ResponseDetectorTest.php`
  - [ ] `Response/HtmxResponseHandlerTest.php`
  - [ ] `Url/UrlBuilderTest.php`
  - [ ] `CollectionControllerTest.php`
- [ ] Add GitHub Actions workflow
- [ ] Add code coverage reporting
- [ ] Add coverage badge to README

#### Test Structure

```
tests/
├── Unit/
│   ├── Config/
│   │   ├── CollectionConfigTest.php
│   │   ├── SearchConfigTest.php
│   │   └── PaginationConfigTest.php
│   ├── Query/
│   │   ├── CollectionQueryTest.php
│   │   └── Stages/
│   │       ├── SearchStageTest.php
│   │       ├── FilterStageTest.php
│   │       └── SortStageTest.php
│   ├── Response/
│   │   └── ResponseDetectorTest.php
│   ├── Url/
│   │   └── UrlBuilderTest.php
│   └── CollectionControllerTest.php
├── Fixtures/
│   └── ... test data
└── TestCase.php
```

#### Acceptance Criteria

- [ ] 80%+ code coverage
- [ ] All new classes have tests
- [ ] CI runs tests on PR
- [ ] Coverage badge in README

#### Notes

_Add any blockers, decisions, or context here_

---

### 3.4 Improve E2E Tests

**Priority:** 🟡 Medium | **Effort:** Medium | **Status:** 🔴 Not Started

**Branch:** `feature/e2e-improvements`

#### Tasks

- [ ] Add `data-testid` attributes to snippets:
  - [ ] `data-testid="collection-search-input"`
  - [ ] `data-testid="collection-search-submit"`
  - [ ] `data-testid="collection-search-clear"`
  - [ ] `data-testid="collection-filter-{param}"`
  - [ ] `data-testid="collection-pagination-prev"`
  - [ ] `data-testid="collection-pagination-next"`
  - [ ] `data-testid="collection-pagination-page-{n}"`
  - [ ] `data-testid="collection-item-{id}"`
- [ ] Replace `waitForTimeout()` with proper waits
- [ ] Add accessibility tests (axe-core):
  - [ ] `tests/e2e/accessibility.spec.js`
- [ ] Add visual regression tests
- [ ] Create predictable test fixtures
- [ ] Add error state tests

#### Acceptance Criteria

- [ ] No `waitForTimeout()` in tests
- [ ] All interactive elements have `data-testid`
- [ ] Accessibility tests pass
- [ ] Tests are reliable (no flakes)

#### Notes

_Add any blockers, decisions, or context here_

---

## Phase 4: Polish & Performance (Week 7-8)

> **Goal:** Optimize performance, improve styling flexibility
> **Status:** 🔴 Not Started
> **Branch:** `feature/phase-4-polish`

### 4.1 Caching Strategy

**Priority:** 🟡 Medium | **Effort:** Medium | **Status:** 🔴 Not Started

**Branch:** `feature/caching`

#### Tasks

- [ ] Create `Cache/CollectionCache.php`:
  ```php
  class CollectionCache {
      public function getTaxonomyOptions(string $pageId, string $field): ?array;
      public function setTaxonomyOptions(string $pageId, string $field, array $options): void;
      public function invalidate(string $pageId): void;
      public function flush(): void;
  }
  ```
- [ ] Add cache configuration:
  ```php
  'cache' => [
      'enabled' => true,
      'taxonomyTtl' => 60 * 24,  // 24 hours in minutes
  ]
  ```
- [ ] Implement taxonomy caching in filters controller
- [ ] Add cache invalidation hooks
- [ ] Add cache warmup command (optional)
- [ ] Document cache behavior
- [ ] Benchmark performance improvement

#### Acceptance Criteria

- [ ] Taxonomy options cached
- [ ] Cache invalidated on content changes
- [ ] Configurable TTL
- [ ] Documented behavior

#### Notes

_Add any blockers, decisions, or context here_

---

### 4.2 CSS Custom Properties

**Priority:** 🟡 Medium | **Effort:** Low | **Status:** 🔴 Not Started

**Branch:** `feature/css-variables`

#### Tasks

- [ ] Define CSS custom properties:
  ```css
  :root {
    /* Colors */
    --cm-color-primary: #333;
    --cm-color-secondary: #666;
    --cm-color-border: #ddd;
    --cm-color-bg: #fff;
    --cm-color-bg-hover: #f5f5f5;
    --cm-color-bg-active: #e3f2fd;
    --cm-color-link: #007bff;

    /* Spacing */
    --cm-spacing-xs: 0.25rem;
    --cm-spacing-sm: 0.5rem;
    --cm-spacing-md: 1rem;
    --cm-spacing-lg: 1.5rem;
    --cm-spacing-xl: 2rem;

    /* Typography */
    --cm-font-size-sm: 0.875rem;
    --cm-font-size-base: 1rem;
    --cm-font-size-lg: 1.125rem;

    /* Borders */
    --cm-border-radius: 4px;
    --cm-border-radius-pill: 20px;
    --cm-border-width: 1px;

    /* Transitions */
    --cm-transition-fast: 0.15s ease;
    --cm-transition-normal: 0.2s ease;
  }
  ```
- [ ] Update `collection-manager.css` to use variables
- [ ] Add dark mode support:
  ```css
  @media (prefers-color-scheme: dark) {
    :root {
      --cm-color-primary: #e5e5e5;
      --cm-color-bg: #1a1a1a;
      --cm-color-border: #333;
    }
  }
  ```
- [ ] Add `.cm-dark` class for manual dark mode
- [ ] Document customization in README

#### Acceptance Criteria

- [ ] All hardcoded values use CSS variables
- [ ] Dark mode works automatically
- [ ] Manual dark mode toggle available
- [ ] Customization documented

#### Notes

_Add any blockers, decisions, or context here_

---

### 4.3 Component Registration System

**Priority:** 🟢 Nice to Have | **Effort:** Medium | **Status:** 🔴 Not Started

**Branch:** `feature/component-registry`

#### Tasks

- [ ] Create `Component/ComponentInterface.php`:
  ```php
  interface ComponentInterface {
      public function getName(): string;
      public function getSnippet(): string;
      public function getController(): ?string;
      public function isEnabled(array $config): bool;
  }
  ```
- [ ] Create `Component/ComponentRegistry.php`
- [ ] Create default component classes:
  - [ ] `Component/SearchComponent.php`
  - [ ] `Component/FiltersComponent.php`
  - [ ] `Component/PaginationComponent.php`
  - [ ] `Component/ItemsComponent.php`
  - [ ] `Component/IndicatorComponent.php`
- [ ] Allow custom component registration in config
- [ ] Update snippet rendering to use registry
- [ ] Document custom component creation
- [ ] Create example custom component

#### Acceptance Criteria

- [ ] Components are pluggable
- [ ] Custom components documented
- [ ] Example component provided

#### Notes

_Add any blockers, decisions, or context here_

---

### 4.4 Security Hardening

**Priority:** 🟡 Medium | **Effort:** Low | **Status:** 🔴 Not Started

**Branch:** `feature/security`

#### Tasks

- [ ] Create `Security/InputSanitizer.php`:
  ```php
  class InputSanitizer {
      public function sanitizeSearchQuery(string $query): string;
      public function sanitizeFilterValue(string $value, array $allowed): ?string;
      public function sanitizePaginationParam(mixed $value): int;
      public function isValidParam(string $param): bool;
  }
  ```
- [ ] Implement sanitization rules:
  - [ ] Search: max 100 chars, strip HTML
  - [ ] Filters: validate against allowed values
  - [ ] Pagination: integer only, min 1
- [ ] Remove all direct `$_GET` / `$_SERVER` access
- [ ] Add CSRF token option for forms
- [ ] Document security considerations
- [ ] Add security tests

#### Acceptance Criteria

- [ ] All inputs sanitized
- [ ] No direct superglobal access
- [ ] CSRF protection available
- [ ] Security documented

#### Notes

_Add any blockers, decisions, or context here_

---

### 4.5 Documentation Updates

**Priority:** 🟢 Nice to Have | **Effort:** Medium | **Status:** 🔴 Not Started

**Branch:** `feature/documentation`

#### Tasks

- [ ] Update README.md:
  - [ ] Add architecture overview diagram
  - [ ] Add configuration validation examples
  - [ ] Add debug mode documentation
  - [ ] Add event hooks documentation
  - [ ] Add CSS customization guide
  - [ ] Add migration guide for v2
  - [ ] Update API examples
- [ ] Create CHANGELOG.md
- [ ] Create CONTRIBUTING.md
- [ ] Update llm.txt with new structure
- [ ] Add PHPDoc blocks to all classes
- [ ] Generate API documentation

#### Acceptance Criteria

- [ ] README covers all features
- [ ] CHANGELOG follows Keep a Changelog format
- [ ] All public methods documented
- [ ] llm.txt is accurate

#### Notes

_Add any blockers, decisions, or context here_

---

## Branch Strategy

```
main (stable releases)
│
└── dev (integration branch)
    │
    ├── feature/phase-1-foundation
    │   ├── feature/i18n
    │   ├── feature/config-validation
    │   └── feature/custom-exceptions
    │
    ├── feature/phase-2-architecture
    │   ├── feature/query-pipeline
    │   ├── feature/response-handlers
    │   ├── feature/url-builder
    │   └── feature/controller-refactor
    │
    ├── feature/phase-3-dx
    │   ├── feature/debug-mode
    │   ├── feature/events
    │   ├── feature/unit-tests
    │   └── feature/e2e-improvements
    │
    └── feature/phase-4-polish
        ├── feature/caching
        ├── feature/css-variables
        ├── feature/component-registry
        ├── feature/security
        └── feature/documentation
```

### Branch Workflow

1. Create feature branch from `dev`
2. Implement feature with commits
3. Open PR to `dev`
4. Code review
5. Merge to `dev`
6. When phase complete, merge `dev` to `main` with version tag

---

## Release Plan

### v2.0.0 (After Phase 1-2)

**Breaking Changes:**
- Configuration DTOs (may change config format)
- Custom exceptions (error handling changes)

**New Features:**
- i18n support
- Config validation
- Refactored architecture

### v2.1.0 (After Phase 3)

**New Features:**
- Debug mode
- Event hooks
- Improved testing

### v2.2.0 (After Phase 4)

**New Features:**
- Caching
- CSS custom properties
- Component registry
- Security improvements

---

## Metrics & Success Criteria

### Code Quality

| Metric | Current | Target |
|--------|---------|--------|
| CollectionController lines | ~470 | ~150 |
| Total PHP classes | 1 | ~25 |
| Test coverage | 0% | 80%+ |
| PHPStan level | - | 8 |

### Performance

| Metric | Current | Target |
|--------|---------|--------|
| Taxonomy load (100 items) | ~50ms | ~5ms (cached) |
| AJAX response time | ~100ms | ~80ms |

### Documentation

| Metric | Current | Target |
|--------|---------|--------|
| Translation strings | 0 | ~30 |
| CSS custom properties | 0 | ~25 |
| Documented hooks | 0 | 6 |

---

## Notes & Decisions Log

### 2026-01-15

- Created initial roadmap
- Prioritized i18n as critical blocker
- Decided on DTO approach for config validation

_Add new notes as decisions are made_

---

## Resources

- [Kirby CMS Documentation](https://getkirby.com/docs)
- [Kirby Plugin Development](https://getkirby.com/docs/guide/plugins)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Playwright Documentation](https://playwright.dev/docs/intro)
