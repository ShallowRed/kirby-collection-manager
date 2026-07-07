<?php

/**
 * Collection Manager - Main Wrapper Snippet
 * This is the default wrapper that contains all collection manager components
 * Uses htmx for AJAX interactions
 */

$cssClass = trim($config['containers']['wrapper'] ?? '', '.');
$instanceId = $config['instanceId'] ?? 'collection';
?>

<?php if ($config['enableJs'] ?? true) : ?>
<script>
  if (!window.collectionManagerHtmxLoaded) {
    window.collectionManagerHtmxLoaded = true;
    // Refetch on history navigation so back/forward always reflect the URL
    var cmHtmxMeta = document.createElement('meta');
    cmHtmxMeta.name = 'htmx-config';
    cmHtmxMeta.content = '{"historyCacheSize": 0, "refreshOnHistoryMiss": true}';
    document.head.appendChild(cmHtmxMeta);
    var cmHtmxScript = document.createElement('script');
    cmHtmxScript.src = <?= json_encode(kirby()->plugin('shallowred/collection-manager')->asset('htmx.min.js')->url()) ?>;
    document.head.appendChild(cmHtmxScript);
  }
</script>
<?php endif ?>

<div <?php echo attr([
  'class' => $cssClass,
  'data-collection-manager' => true,
  'id' => $instanceId
]) ?>>

  <div id="<?= esc($instanceId) ?>-content">
    <?php if ($config['enableSearch'] ?? true) : ?>
      <!-- Search Form -->
      <div <?php echo attr(['class' => trim($config['containers']['search'] ?? '', '.')]) ?>>
      <?php echo $snippets['search'] ?? '' ?>
      </div>
    <?php endif ?>

    <?php if ($config['enableFilters'] ?? true) : ?>
      <!-- Taxonomy Filters -->
      <div <?php echo attr(['class' => trim($config['containers']['filters'] ?? '', '.')]) ?>>
      <?php echo $snippets['filters'] ?? '' ?>
      </div>
    <?php endif ?>

    <?php if ($config['enableSorting'] ?? false) : ?>
      <!-- Sorting -->
      <div <?php echo attr(['class' => trim($config['containers']['sorting'] ?? '', '.')]) ?>>
      <?php echo $snippets['sorting'] ?? '' ?>
      </div>
    <?php endif ?>

    <!-- Collection Items -->
    <div <?php echo attr(['class' => trim($config['containers']['items'] ?? '', '.')]) ?>>
      <?php echo $snippets['items'] ?? '' ?>
    </div>

    <!-- Pagination and Indicator -->
    <div class="collection-pagination-wrapper">
      <?php echo $snippets['pagination'] ?? '' ?>

      <?php echo $snippets['indicator'] ?? '' ?>
    </div>
  </div>

  <?php if (($config['enableJs'] ?? true) && ($config['useIsotope'] ?? false)) : ?>
    <!-- Isotope integration (optional) -->
    <script type="module">
      import { IsotopeManager } from <?= json_encode(kirby()->plugin('shallowred/collection-manager')->asset('isotope.js')->url()) ?>;

      const isotope = new IsotopeManager({
        container: '.collection-items__list',
        itemSelector: '.collection-item',
        options: <?php echo json_encode($config['isotopeOptions'] ?? []) ?>
      });

      // Re-initialize Isotope after htmx swaps
      document.body.addEventListener('htmx:afterSwap', () => {
        isotope.reinit();
      });
    </script>
  <?php endif ?>

</div>
