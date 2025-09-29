<?php
/**
 * Collection Manager - Individual Item Snippet
 * Renders a single collection item
 *
 * Available variables:
 * - $item: The page/item object
 * - $orderIndex: The sort order index
 * - $config: Controller configuration
 */

// Ensure we have required variables
if (!isset($item)) {
  return;
}

$orderIndex = $orderIndex ?? 0;
$config = $config ?? [];
?>

<article class="collection-item" data-id="<?= $item->id() ?>" data-order="<?= $orderIndex ?>">

  <?php if ($item->hasImages()): ?>
    <div class="collection-item__image">
      <?php $image = $item->images()->first() ?>
      <img src="<?= $image->crop(300, 200)->url() ?>"
           alt="<?= esc($image->alt()->or($item->title())) ?>"
           loading="lazy">
    </div>
  <?php endif ?>

  <div class="collection-item__content">
    <h2 class="collection-item__title">
      <a href="<?= $item->url() ?>">
        <?= $item->title() ?>
      </a>
    </h2>

    <?php if ($item->hasMethod('text') && $item->text()->isNotEmpty()): ?>
      <p class="collection-item__excerpt">
        <?= $item->text()->excerpt(150) ?>
      </p>
    <?php endif ?>

    <div class="collection-item__meta">
      <?php if ($item->hasMethod('date') && $item->date()->isNotEmpty()): ?>
        <time class="collection-item__date" datetime="<?= $item->date('c') ?>">
          <?= $item->date('M j, Y') ?>
        </time>
      <?php endif ?>

      <?php if ($item->hasMethod('category') && $item->category()->isNotEmpty()): ?>
        <span class="collection-item__category">
          <?= $item->category() ?>
        </span>
      <?php endif ?>

      <?php if ($item->hasMethod('tags') && $item->tags()->isNotEmpty()): ?>
        <div class="collection-item__tags">
          <?php foreach ($item->tags()->split(',') as $tag): ?>
            <span class="collection-item__tag"><?= trim($tag) ?></span>
          <?php endforeach ?>
        </div>
      <?php endif ?>
    </div>

    <div class="collection-item__actions">
      <a href="<?= $item->url() ?>" class="collection-item__link">
        Read more
      </a>
    </div>
  </div>

</article>
