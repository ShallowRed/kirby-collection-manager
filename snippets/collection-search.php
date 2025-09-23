<?php
/**
 * Collection Manager - Search Form Snippet
 * Renders the search form
 *
 * Available variables:
 * - $page: Current page object
 * - $config: Controller configuration
 */

$currentSearch = get('q', '');
$hasSearch = !empty($currentSearch);
?>

<div class="collection-search">
  <form class="collection-search__form" action="<?= $page->url() ?>" method="get">
    <div class="collection-search__field">
      <label for="collection-search-input" class="collection-search__label sr-only">
        Search
      </label>
      <input type="search"
             id="collection-search-input"
             name="q"
             value="<?= esc($currentSearch) ?>"
             placeholder="<?= esc($config['search']['placeholder'] ?? 'Search...') ?>"
             class="collection-search__input">

      <button type="submit" class="collection-search__submit">
        <span class="collection-search__submit-text">Search</span>
        <span class="collection-search__submit-icon" aria-hidden="true">🔍</span>
      </button>
    </div>
      </button>
    </div>

    <?php if ($hasSearch): ?>
      <a href="<?= \KirbyCollectionManager\CollectionController::buildUrl($page, ['q' => null]) ?>"
         class="collection-search__clear"
         title="Clear search">
        <span class="collection-search__clear-text">Clear</span>
        <span class="collection-search__clear-icon" aria-hidden="true">✕</span>
      </a>
    <?php endif ?>
  </form>

  <?php if ($hasSearch): ?>
    <div class="collection-search__indicator">
      <span class="collection-search__indicator-label">Searching for:</span>
      <strong class="collection-search__indicator-term">"<?= esc($currentSearch) ?>"</strong>
    </div>
  <?php endif ?>
</div>
