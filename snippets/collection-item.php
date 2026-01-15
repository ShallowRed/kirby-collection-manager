<?php

/**
 * Collection Manager - Individual Item Snippet
 * Simple presentation-focused snippet using snippet controller
 */

if (!($shouldRender ?? true)) {
  return;
}
?>

<article<?php echo attr([
  'class' => 'collection-item',
  'data-id' => $item->id(),
  'data-order' => $orderIndex,
  'data-testid' => 'collection-item-' . $item->id()
]) ?>>

  <?php if ($hasImage) : ?>
    <div class="collection-item__image">
      <img<?php echo attr([
        'src' => $firstImage->crop(300, 200)->url(),
        'alt' => $firstImage->alt()->or($item->title())->value(),
        'loading' => 'lazy'
      ]) ?>>
    </div>
  <?php endif ?>

  <div class="collection-item__content">
    <h2 class="collection-item__title">
      <a <?php echo attr(['href' => $item->url()]) ?>>
        <?php echo esc($item->title(), 'html') ?>
      </a>
    </h2>

    <?php if ($hasText) : ?>
      <p class="collection-item__excerpt">
        <?php echo esc($item->text()->excerpt(150), 'html') ?>
      </p>
    <?php endif ?>

    <div class="collection-item__meta">
      <?php if ($hasDate) : ?>
        <time<?php echo attr([
          'class' => 'collection-item__date',
          'datetime' => $item->date('c')
        ]) ?>>
          <?php echo esc($item->date('M j, Y'), 'html') ?>
        </time>
      <?php endif ?>

      <?php if ($hasCategory) : ?>
        <span class="collection-item__category">
          <?php echo esc($item->category(), 'html') ?>
        </span>
      <?php endif ?>

      <?php if ($hasTags) : ?>
        <div class="collection-item__tags">
          <?php foreach ($processedTags as $tag) : ?>
            <span class="collection-item__tag"><?php echo esc($tag, 'html') ?></span>
          <?php endforeach ?>
        </div>
      <?php endif ?>
    </div>

    <div class="collection-item__actions">
      <a <?php echo attr([
        'href' => $item->url(),
        'class' => 'collection-item__link'
      ]) ?>>
        <?= t('collection.item.readmore', 'Read more') ?>
      </a>
    </div>
  </div>

</article>
