import { test, expect } from '@playwright/test';

/**
 * Test pagination functionality
 */
test.describe('Pagination', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/blog');
    await expect(page.locator('.collection-manager')).toBeVisible();
  });

  test('should navigate through pages', async ({ page }) => {
    // Check if pagination exists (might not if there aren't enough items)
    const paginationExists = await page.locator('.collection-pagination a').count() > 0;

    if (paginationExists) {
      // Go to page 2
      const nextButton = page.locator('.collection-pagination .pagination-next');
      if (await nextButton.isVisible()) {
        await nextButton.click();
        await page.waitForTimeout(1000);

        // URL should contain page parameter
        expect(page.url()).toContain('p=2');

        // Should still show items
        await expect(page.locator('.collection-item')).toHaveCountGreaterThan(0);

        // Page indicator should show correct page
        if (await page.locator('.current-page-indicator').isVisible()) {
          await expect(page.locator('.current-page-indicator')).toContainText('2');
        }
      }
    }
  });

  test('should handle direct page navigation', async ({ page }) => {
    // Navigate directly to page 2
    await page.goto('/blog?p=2');
    await page.waitForTimeout(1000);

    // Should show items (if page 2 exists)
    const hasItems = await page.locator('.collection-item').count();
    const isEmpty = await page.locator('.collection-empty').isVisible();

    // Either has items or shows empty state
    expect(hasItems > 0 || isEmpty).toBeTruthy();
  });

  test('should maintain pagination state with search', async ({ page }) => {
    // Perform search that should have multiple pages
    await page.fill('.collection-search input[type="search"]', 'blog');
    await page.press('.collection-search input[type="search"]', 'Enter');
    await page.waitForTimeout(1000);

    // If pagination exists with search results
    const paginationExists = await page.locator('.collection-pagination a').count() > 0;

    if (paginationExists) {
      // Go to next page
      const nextButton = page.locator('.collection-pagination .pagination-next');
      if (await nextButton.isVisible()) {
        await nextButton.click();
        await page.waitForTimeout(1000);

        // URL should contain both search and page parameters
        expect(page.url()).toContain('q=blog');
        expect(page.url()).toContain('p=');
      }
    }
  });

  test('should show consistent pagination sizing', async ({ page }) => {
    // Check that pagination buttons have consistent sizing
    const paginationLinks = page.locator('.collection-pagination a');
    const linkCount = await paginationLinks.count();

    if (linkCount > 0) {
      // All pagination links should have similar height
      const heights = [];
      for (let i = 0; i < linkCount; i++) {
        const box = await paginationLinks.nth(i).boundingBox();
        if (box) heights.push(box.height);
      }

      // Check that heights are reasonably consistent (within 5px difference)
      if (heights.length > 1) {
        const maxHeight = Math.max(...heights);
        const minHeight = Math.min(...heights);
        expect(maxHeight - minHeight).toBeLessThan(5);
      }
    }
  });
});
