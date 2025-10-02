<?php

/**
 * Collection Manager - Search Form Snippet
 * Simple presentation-focused snippet using snippet controller
 */

?>

<div class="collection-search">
  <form <?php echo attr([
    'class' => 'collection-search__form',
    'action' => $page->url(),
    'method' => 'get'
  ]) ?>>
    <div class="collection-search__field">
      <label <?php echo attr([
        'for' => 'collection-search-input',
        'class' => 'collection-search__label sr-only'
      ]) ?>>
        Search
      </label>
      <input<?php echo attr([
        'type' => 'search',
        'id' => 'collection-search-input',
        'name' => $searchParam,
        'value' => $currentSearch,
        'placeholder' => $placeholder,
        'class' => 'collection-search__input'
      ]) ?>>

      <button <?php echo attr([
        'type' => 'submit',
        'class' => 'collection-search__submit'
      ]) ?>>
        <span class="collection-search__submit-text">Search</span>
        <span <?php echo attr([
          'class' => 'collection-search__submit-icon',
          'aria-hidden' => 'true'
        ]) ?>>🔍</span>
      </button>
    </div>

    <?php if ($hasSearch) : ?>
      <a <?php echo attr([
        'href' => $clearUrl,
        'class' => 'collection-search__clear',
        'title' => 'Clear search'
      ]) ?>>
        <span class="collection-search__clear-text">Clear</span>
        <span <?php echo attr([
          'class' => 'collection-search__clear-icon',
          'aria-hidden' => 'true'
        ]) ?>>✕</span>
      </a>
    <?php endif ?>
  </form>

  <?php if ($hasSearch) : ?>
    <div class="collection-search__indicator">
      <span class="collection-search__indicator-label">Searching for:</span>
      <strong class="collection-search__indicator-term">"<?php echo esc($currentSearch, 'html') ?>"</strong>
    </div>
  <?php endif ?>
</div>
