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

    <!-- Collection Items -->
    <div <?php echo attr([
    'class' => trim($config['containers']['items'] ?? '', '.'),
    'data-replacementtop' => 'true',
    'data-offset' => '100'
  ]) ?>>
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
      import { IsotopeManager } from '/site/plugins/kirby-collection-manager/lib/isotope.js';

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
