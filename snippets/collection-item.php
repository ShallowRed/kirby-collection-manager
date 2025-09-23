<?php
/**
 * Collection Manager - Individual Item Snippet
 * Renders a single collection item
 *
 * Available variables:
 * - $article: The page/item object
 * - $orderIndex: The sort order index
 * - $config: Controller configuration
 */

// Ensure we have required variables
if (!isset($article)) {
  return;
}

$orderIndex = $orderIndex ?? 0;
$config = $config ?? [];
?>

<article class="collection-item" data-id="<?= $article->id() ?>" data-order="<?= $orderIndex ?>">

  <?php if ($article->hasImages()): ?>
    <div class="collection-item__image">
      <?php $image = $article->images()->first() ?>
      <img src="<?= $image->crop(300, 200)->url() ?>"
           alt="<?= esc($image->alt()->or($article->title())) ?>"
           loading="lazy">
    </div>
  <?php endif ?>

  <div class="collection-item__content">
    <h2 class="collection-item__title">
      <a href="<?= $article->url() ?>">
        <?= $article->title() ?>
      </a>
    </h2>

    <?php if ($article->hasMethod('text') && $article->text()->isNotEmpty()): ?>
      <p class="collection-item__excerpt">
        <?= $article->text()->excerpt(150) ?>
      </p>
    <?php endif ?>

    <div class="collection-item__meta">
      <?php if ($article->hasMethod('date') && $article->date()->isNotEmpty()): ?>
        <time class="collection-item__date" datetime="<?= $article->date('c') ?>">
          <?= $article->date('M j, Y') ?>
        </time>
      <?php endif ?>

      <?php if ($article->hasMethod('category') && $article->category()->isNotEmpty()): ?>
        <span class="collection-item__category">
          <?= $article->category() ?>
        </span>
      <?php endif ?>

      <?php if ($article->hasMethod('tags') && $article->tags()->isNotEmpty()): ?>
        <div class="collection-item__tags">
          <?php foreach ($article->tags()->split(',') as $tag): ?>
            <span class="collection-item__tag"><?= trim($tag) ?></span>
          <?php endforeach ?>
        </div>
      <?php endif ?>
    </div>

    <div class="collection-item__actions">
      <a href="<?= $article->url() ?>" class="collection-item__link">
        Read more
      </a>
    </div>
  </div>

</article>
