<?php

/**
 * Collection Manager - Sorting Snippet
 * Select-based sort control, submitted via htmx on change
 */

// Defensive defaults for when controller doesn't run
$config = $config ?? [];
$page = $page ?? page();
$shouldRender = $shouldRender ?? false;
$options = $options ?? [];
$currentSort = $currentSort ?? null;
$sortParam = $sortParam ?? 'sort';
$preservedParams = $preservedParams ?? [];

if (!$shouldRender) {
  return;
}

$classes = $config['classes'] ?? [];
$htmxEnabled = $config['enableJs'] ?? true;
$instanceId = $config['instanceId'] ?? 'collection';
$htmxTarget = '#' . $instanceId . '-content';
// Scroll back to this listing rather than the top of the document: a
// collection often sits far down the page, and jumping to the window top
// yanked the reader away from the very section they were interacting with.
$htmxSwap = 'innerHTML show:#' . $instanceId . ':top';
$sortingUrl = $page->url();
$selectId = $instanceId . '-sorting';

?>

<div class="<?= esc(trim('collection-sorting ' . ($classes['sorting'] ?? ''))) ?>" data-testid="collection-sorting">
  <form <?php echo attr(array_filter([
    'class' => 'collection-sorting__form',
    'action' => $sortingUrl,
    'method' => 'get',
    'hx-get' => $htmxEnabled ? $sortingUrl : null,
    'hx-trigger' => $htmxEnabled ? 'change' : null,
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

    <label <?php echo attr([
      'for' => $selectId,
      'class' => trim('collection-sorting__label ' . ($classes['sortingLabel'] ?? ''))
    ]) ?>>
      <?= t('collection.sorting.label', 'Sort by') ?>
    </label>
    <select <?php echo attr([
      'id' => $selectId,
      'name' => $sortParam,
      'class' => trim('collection-sorting__select ' . ($classes['sortingSelect'] ?? '')),
      'data-testid' => 'collection-sorting-select'
    ]) ?>>
      <?php foreach ($options as $value => $label) : ?>
      <option <?php echo attr(array_filter([
        'value' => $value,
        'selected' => $currentSort === $value
      ])) ?>>
        <?php echo esc($label, 'html') ?>
      </option>
      <?php endforeach ?>
    </select>

    <?php if (!$htmxEnabled) : ?>
    <button type="submit" class="collection-sorting__submit">
      <?= t('collection.sorting.submit', 'Apply') ?>
    </button>
    <?php endif ?>
  </form>
</div>
