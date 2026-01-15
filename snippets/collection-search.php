<?php

/**
 * Collection Manager - Search Form Snippet
 * Uses htmx for AJAX search functionality
 */

$htmxEnabled = $config['enableJs'] ?? true;
$htmxTarget = '#collection-content';
$htmxSwap = 'innerHTML show:window:top';
$searchUrl = $page->url();

?>

<div class="collection-search">
  <form <?php echo attr(array_filter([
    'class' => 'collection-search__form',
    'action' => $searchUrl,
    'method' => 'get',
    'hx-get' => $htmxEnabled ? $searchUrl : null,
    'hx-target' => $htmxEnabled ? $htmxTarget : null,
    'hx-swap' => $htmxEnabled ? $htmxSwap : null,
    'hx-push-url' => $htmxEnabled ? 'true' : null,
    'hx-include' => $htmxEnabled ? '[name]' : null
  ])) ?>>
    <?php if ($htmxEnabled) : ?>
    <input type="hidden" name="htmx" value="1">
    <?php endif ?>
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
      <a <?php echo attr(array_filter([
        'href' => $clearUrl,
        'class' => 'collection-search__clear',
        'title' => 'Clear search',
        'hx-get' => $htmxEnabled ? $clearUrl . (strpos($clearUrl, '?') !== false ? '&' : '?') . 'htmx=1' : null,
        'hx-target' => $htmxEnabled ? $htmxTarget : null,
        'hx-swap' => $htmxEnabled ? $htmxSwap : null,
        'hx-push-url' => $htmxEnabled ? $clearUrl : null
      ])) ?>>
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
