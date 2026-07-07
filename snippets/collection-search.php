<?php

/**
 * Collection Manager - Search Form Snippet
 * Uses htmx for AJAX search functionality
 */

// Defensive defaults for when controller doesn't run
$config = $config ?? [];
$page = $page ?? page();
$searchParam = $searchParam ?? 'q';
$currentSearch = $currentSearch ?? get('q', '');
$hasSearch = $hasSearch ?? ($currentSearch !== '');
$placeholder = $placeholder ?? t('collection.search.placeholder', 'Search...');
$clearUrl = $clearUrl ?? $page->url();
$preservedParams = $preservedParams ?? [];

$htmxEnabled = $config['enableJs'] ?? true;
$instanceId = $config['instanceId'] ?? 'collection';
$htmxTarget = '#' . $instanceId . '-content';
$htmxSwap = 'innerHTML show:window:top';
$searchUrl = $page->url();

?>

<div class="collection-search" data-testid="collection-search">
  <form <?php echo attr(array_filter([
    'class' => 'collection-search__form',
    'action' => $searchUrl,
    'method' => 'get',
    'hx-get' => $htmxEnabled ? $searchUrl : null,
    'hx-target' => $htmxEnabled ? $htmxTarget : null,
    'hx-swap' => $htmxEnabled ? $htmxSwap : null,
    'hx-push-url' => $htmxEnabled ? 'true' : null
  ])) ?>>
    <?php foreach ($preservedParams as $param => $value) : ?>
    <input <?php echo attr([
      'type' => 'hidden',
      'name' => $param,
      'value' => $value
    ]) ?>>
    <?php endforeach ?>
    <div class="collection-search__field">
      <label <?php echo attr([
        'for' => 'collection-search-input',
        'class' => 'collection-search__label sr-only'
      ]) ?>>
        <?= t('collection.search.label', 'Search') ?>
      </label>
      <input <?php echo attr([
        'type' => 'search',
        'id' => 'collection-search-input',
        'name' => $searchParam,
        'value' => $currentSearch,
        'placeholder' => $placeholder,
        'autocomplete' => 'off',
        'class' => 'collection-search__input',
        'data-testid' => 'collection-search-input'
      ]) ?>>

      <button <?php echo attr([
        'type' => 'submit',
        'class' => 'collection-search__submit',
        'data-testid' => 'collection-search-submit'
      ]) ?>>
        <span class="collection-search__submit-text"><?= t('collection.search.submit', 'Search') ?></span>
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
        'title' => t('collection.search.clear', 'Clear search'),
        'data-testid' => 'collection-search-clear',
        'hx-get' => $htmxEnabled ? $clearUrl : null,
        'hx-target' => $htmxEnabled ? $htmxTarget : null,
        'hx-swap' => $htmxEnabled ? $htmxSwap : null,
        'hx-push-url' => $htmxEnabled ? 'true' : null
      ])) ?>>
        <span class="collection-search__clear-text"><?= t('collection.search.clear.button', 'Clear') ?></span>
        <span <?php echo attr([
          'class' => 'collection-search__clear-icon',
          'aria-hidden' => 'true'
        ]) ?>>✕</span>
      </a>
    <?php endif ?>
  </form>

  <?php if ($hasSearch) : ?>
    <div class="collection-search__indicator">
      <span class="collection-search__indicator-label"><?= t('collection.search.searching', 'Searching for:') ?></span>
      <strong class="collection-search__indicator-term">"<?php echo esc($currentSearch, 'html') ?>"</strong>
    </div>
  <?php endif ?>
</div>
