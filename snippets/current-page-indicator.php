<?php $pagination = $collection->pagination() ?>
<?php $currentPage = $pagination->page() ?>
<?php $totalPages = $pagination->pages() ?>

<p class="current-page-indicator">
  p. <?php echo $currentPage ?> sur <?php echo $totalPages ?>
</p>
