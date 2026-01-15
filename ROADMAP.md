# Kirby Collection Manager - Improvement Roadmap

> **Status:** 🟡 In Progress  
> **Created:** 2026-01-15  
> **Last Updated:** 2026-01-15  
> **Current Phase:** Phase 1 - Foundation

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
| Phase 1: Foundation | 🔴 Not Started | 0/3 | Week 1-2 |
| Phase 2: Architecture | 🔴 Not Started | 0/4 | Week 3-4 |
| Phase 3: Developer Experience | 🔴 Not Started | 0/4 | Week 5-6 |
| Phase 4: Polish & Performance | 🔴 Not Started | 0/5 | Week 7-8 |

**Overall Progress:** 0/16 tasks (0%)

---

## Phase 1: Foundation (Week 1-2)

> **Goal:** Fix critical issues that block adoption and cause debugging nightmares  
> **Status:** 🔴 Not Started  
> **Branch:** `feature/phase-1-foundation`

### 1.1 Internationalization (i18n) Support

**Priority:** 🔴 Critical | **Effort:** Medium | **Status:** 🔴 Not Started

**Branch:** `feature/i18n`

#### Tasks

- [ ] Create translation files structure
  - [ ] `translations/en.php`
  - [ ] `translations/fr.php`
  - [ ] `translations/de.php`
- [ ] Define all translation keys (~30 strings)
- [ ] Replace hardcoded strings in snippets:
  - [ ] `collection-search.php` — 4 strings
  - [ ] `collection-filters.php` — 3 strings
  - [ ] `collection-pagination.php` — 6 strings
  - [ ] `collection-items.php` — 3 strings
  - [ ] `collection-item.php` — 2 strings
  - [ ] `current-page-indicator.php` — 1 string
- [ ] Register translations in `index.php`
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

- [ ] All user-facing strings use `t()` helper
- [ ] English and French translations complete
- [ ] Translation override documented
- [ ] No hardcoded strings in snippets

#### Notes

_Add any blockers, decisions, or context here_

---

### 1.2 Configuration Validation & DTOs

**Priority:** 🔴 Critical | **Effort:** High | **Status:** 🔴 Not Started

**Branch:** `feature/config-validation`

#### Tasks

- [ ] Create Config namespace structure:
  ```
  classes/Config/
  ├── CollectionConfig.php
  ├── SearchConfig.php
  ├── PaginationConfig.php
  ├── SortingConfig.php
  ├── TaxonomyConfig.php
  ├── ContainerConfig.php
  └── SnippetConfig.php
  ```
- [ ] Implement `CollectionConfig.php`:
  - [ ] Define all properties with types
  - [ ] Create `fromArray()` factory method
  - [ ] Add validation in constructor
  - [ ] Implement `toArray()` for serialization
- [ ] Implement `SearchConfig.php`:
  - [ ] Validate fields array
  - [ ] Validate param is URL-safe
  - [ ] Default placeholder handling
- [ ] Implement `PaginationConfig.php`:
  - [ ] Validate limit >= 1
  - [ ] Validate range 1-20
  - [ ] Validate param is URL-safe
- [ ] Implement `SortingConfig.php`:
  - [ ] Validate direction enum (asc/desc)
  - [ ] Validate field is string
- [ ] Implement `TaxonomyConfig.php`:
  - [ ] Validate param is URL-safe
  - [ ] Validate field is non-empty
  - [ ] Validate label is non-empty
- [ ] Implement `ContainerConfig.php`:
  - [ ] Validate CSS selectors format
- [ ] Update `CollectionController` to use DTOs
- [ ] Write unit tests for each config class

#### Validation Rules

| Config | Property | Rule |
|--------|----------|------|
| Pagination | limit | `int`, >= 1 |
| Pagination | param | `string`, non-empty, URL-safe (`/^[a-z][a-z0-9_]*$/i`) |
| Pagination | range | `int`, >= 1, <= 20 |
| Search | fields | `array`, non-empty |
| Search | param | `string`, non-empty, URL-safe |
| Search | placeholder | `string` |
| Sorting | default | `string`, non-empty |
| Sorting | direction | `string`, enum: `asc`, `desc` |
| Taxonomy | param | `string`, non-empty, URL-safe, unique |
| Taxonomy | field | `string`, non-empty |
| Taxonomy | label | `string`, non-empty |

#### Acceptance Criteria

- [ ] All config classes have full type declarations
- [ ] Invalid config throws `ConfigurationException` with helpful message
- [ ] Unit tests cover all validation rules
- [ ] `CollectionController` uses DTOs internally

#### Notes

_Add any blockers, decisions, or context here_

---

### 1.3 Custom Exceptions

**Priority:** 🔴 Critical | **Effort:** Low | **Status:** 🔴 Not Started

**Branch:** `feature/custom-exceptions`

#### Tasks

- [ ] Create Exception namespace:
  ```
  classes/Exception/
  ├── CollectionManagerException.php
  ├── ConfigurationException.php
  ├── CollectionNotFoundException.php
  └── ResponseException.php
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
- [ ] Replace all `throw new Error()` calls
- [ ] Replace silent failures with exceptions
- [ ] Add try/catch in `CollectionController::handle()`
- [ ] Format errors for AJAX responses
- [ ] Write unit tests

#### Exception Message Format

```php
// Example: ConfigurationException
throw new ConfigurationException(
    "Invalid pagination configuration: 'limit' must be a positive integer. " .
    "Expected: int >= 1, Got: string 'invalid'. " .
    "Example: ['pagination' => ['limit' => 10]]"
);
```

#### Acceptance Criteria

- [ ] All exceptions extend `CollectionManagerException`
- [ ] Exception messages are actionable (include fix examples)
- [ ] AJAX errors return proper JSON/HTML error responses
- [ ] No silent failures remain

#### Notes

_Add any blockers, decisions, or context here_

---

## Phase 2: Architecture Refactoring (Week 3-4)

> **Goal:** Break up God class, improve maintainability  
> **Status:** 🔴 Not Started  
> **Branch:** `feature/phase-2-architecture`

### 2.1 Extract Query Pipeline

**Priority:** 🟠 High | **Effort:** High | **Status:** 🔴 Not Started

**Branch:** `feature/query-pipeline`

#### Tasks

- [ ] Create Query interface:
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
- [ ] Create `CollectionQuery.php` implementation
- [ ] Create Stage classes:
  - [ ] `Stages/SearchStage.php`
  - [ ] `Stages/FilterStage.php`
  - [ ] `Stages/SortStage.php`
  - [ ] `Stages/PaginateStage.php`
- [ ] Extract logic from `CollectionController`:
  - [ ] `getBaseCollection()` → `CollectionQuery::from()`
  - [ ] `applyTaxonomyFilters()` → `FilterStage`
  - [ ] `sortCollection()` → `SortStage`
  - [ ] `paginateCollection()` → `PaginateStage`
- [ ] Implement fluent interface
- [ ] Add debug info collection
- [ ] Write unit tests for each stage
- [ ] Update `CollectionController` to use Query system

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

_Add any blockers, decisions, or context here_

---

### 2.2 Extract Response Handlers

**Priority:** 🟠 High | **Effort:** Medium | **Status:** 🔴 Not Started

**Branch:** `feature/response-handlers`

#### Tasks

- [ ] Create Response interface:
  ```php
  interface ResponseHandlerInterface {
      public function canHandle(): bool;
      public function handle(Collection $collection, array $snippets, array $config): void;
  }
  ```
- [ ] Create `ResponseDetector.php`:
  - [ ] Detect htmx requests (`HX-Request` header or `htmx` param)
  - [ ] Detect JSON requests (`json` param + Accept header)
  - [ ] Return request type enum
- [ ] Create `HtmxResponseHandler.php`:
  - [ ] Extract `handleHtmxRequest()` logic
  - [ ] Add proper headers
  - [ ] Handle errors gracefully
- [ ] Create `JsonResponseHandler.php`:
  - [ ] Extract `handleJsonRequest()` logic
  - [ ] Maintain backwards compatibility
- [ ] Create `ResponseFactory.php` to get appropriate handler
- [ ] Update `CollectionController`
- [ ] Write unit tests

#### Acceptance Criteria

- [ ] Response handling is fully decoupled
- [ ] Each handler is independently testable
- [ ] `CollectionController` reduced by ~80 lines
- [ ] Error responses are properly formatted

#### Notes

_Add any blockers, decisions, or context here_

---

### 2.3 Extract URL Builder

**Priority:** 🟠 High | **Effort:** Low | **Status:** 🔴 Not Started

**Branch:** `feature/url-builder`

#### Tasks

- [ ] Create `Url/UrlBuilder.php`:
  ```php
  class UrlBuilder {
      public function __construct(Page $page, string $paginationParam, string $searchParam);
      public function build(array $params = []): string;
      public function buildHtmx(array $params = []): string;
      public function buildClean(): string;
      public function getCurrentParams(): array;
  }
  ```
- [ ] Move `buildUrl()` static method to class
- [ ] Replace `$_GET` access with Kirby's `get()` helper
- [ ] Add method for htmx URLs (auto-append `htmx=1`)
- [ ] Add method for clean URLs (remove internal params)
- [ ] Update all usages in controllers
- [ ] Write unit tests

#### Acceptance Criteria

- [ ] No direct `$_GET` or `$_SERVER` access
- [ ] URL building is centralized
- [ ] Unit tests cover edge cases

#### Notes

_Add any blockers, decisions, or context here_

---

### 2.4 Refactor CollectionController

**Priority:** 🟠 High | **Effort:** Medium | **Status:** 🔴 Not Started

**Branch:** `feature/controller-refactor`

#### Tasks

- [ ] Add dependency injection:
  ```php
  public function __construct(
      Page $page,
      CollectionConfig $config,
      ?CollectionQueryInterface $query = null,
      ?ResponseHandlerInterface $responseHandler = null,
      ?UrlBuilder $urlBuilder = null
  )
  ```
- [ ] Rename methods for consistency:
  - [ ] `handle()` → `create()` (static factory)
  - [ ] `process()` → `execute()`
- [ ] Remove extracted logic (now in other classes)
- [ ] Add return type declarations
- [ ] Add PHPDoc blocks
- [ ] Update all usages in snippets/controllers
- [ ] Write integration tests

#### Target Structure

```php
class CollectionController
{
    // ~150 lines total
    
    public static function create(Page $page, array $config = []): array;
    
    public function __construct(/* dependencies */);
    
    public function execute(): array;
    
    protected function buildQuery(): CollectionQueryInterface;
    
    protected function generateSnippets(Collection $collection): array;
}
```

#### Acceptance Criteria

- [ ] Controller is ~150 lines (down from ~470)
- [ ] All methods have return type declarations
- [ ] Dependencies are injectable for testing
- [ ] All existing tests still pass

#### Notes

_Add any blockers, decisions, or context here_

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
