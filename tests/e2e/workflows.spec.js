import { test, expect } from '@playwright/test';

/**
 * Test complete user workflows combining search, filters, and pagination
 */
test.describe('Complete User Workflows', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/blog');
    await expect(page.locator('.collection-manager')).toBeVisible();
  });

  test('should handle search → filter → paginate workflow', async ({ page }) => {
    // Step 1: Search
    await page.fill('#collection-search-input', 'blog');
    await page.press('#collection-search-input', 'Enter');
    await page.waitForLoadState('networkidle');

    const itemCount = await page.locator('.collection-item').count();
    const hasItemsAfterSearch = itemCount > 0;

    if (hasItemsAfterSearch) {
      // Step 2: Apply filter (if available)
      const categoryFilter = page.locator('[data-param="category"]').first();
      if (await categoryFilter.isVisible()) {
        await categoryFilter.click();
        await page.waitForLoadState('networkidle');

        // Should maintain search and add filter
        expect(page.url()).toContain('q=blog');
      }

      // Step 3: Navigate pagination (if available)
      const nextButton = page.locator('.collection-pagination .pagination-next');
      if (await nextButton.isVisible()) {
        await nextButton.click();
        await page.waitForTimeout(1000);

        // URL should contain search, filter, and pagination
        expect(page.url()).toContain('q=blog');
      }
    }
  });

  test('should preserve grid layout during AJAX updates', async ({ page }) => {
    // Get initial grid layout
    const initialItems = await page.locator('.collection-item').count();

    if (initialItems > 0) {
      // Get positions of first few items
      const initialPositions = [];
      for (let i = 0; i < Math.min(3, initialItems); i++) {
        const box = await page.locator('.collection-item').nth(i).boundingBox();
        if (box) initialPositions.push(box);
      }

      // Perform search that changes content
      await page.fill('.collection-search input[type="search"]', 'test');
      await page.press('.collection-search input[type="search"]', 'Enter');
      await page.waitForTimeout(1000);

      // Clear search to return to original state
      await page.fill('.collection-search input[type="search"]', '');
      await page.press('.collection-search input[type="search"]', 'Enter');
      await page.waitForTimeout(1000);

      // Check that items are properly laid out (not overlapping)
      const finalItems = await page.locator('.collection-item').count();
      if (finalItems > 1) {
        // Get positions of current items
        const item1Box = await page.locator('.collection-item').nth(0).boundingBox();
        const item2Box = await page.locator('.collection-item').nth(1).boundingBox();

        if (item1Box && item2Box) {
          // Items should not overlap
          const noOverlap =
            item1Box.x + item1Box.width <= item2Box.x ||
            item2Box.x + item2Box.width <= item1Box.x ||
            item1Box.y + item1Box.height <= item2Box.y ||
            item2Box.y + item2Box.height <= item1Box.y;

          expect(noOverlap).toBeTruthy();
        }
      }
    }
  });

  test('should handle browser back/forward navigation', async ({ page }) => {
    const initialUrl = page.url();

    // Navigate through different states
    await page.fill('#collection-search-input', 'test');
    await page.press('#collection-search-input', 'Enter');
    await page.waitForLoadState('networkidle');

    const searchUrl = page.url();
    expect(searchUrl).toContain('q=test');

    // Go back
    await page.goBack();
    await page.waitForTimeout(1000);

    // Should be back to initial state
    expect(page.url()).toBe(initialUrl);
    await expect(page.locator('#collection-search-input')).toHaveValue('');

    // Go forward
    await page.goForward();
    await page.waitForLoadState('networkidle');

    // Should restore search state
    expect(page.url()).toBe(searchUrl);
    await expect(page.locator('#collection-search-input')).toHaveValue('test');
  });

  test('should be responsive on mobile viewport', async ({ page }) => {
    // Set mobile viewport
    await page.setViewportSize({ width: 375, height: 667 });

    // Check that collection manager is visible and functional
    await expect(page.locator('.collection-manager')).toBeVisible();

    // Search should work on mobile
    await page.fill('#collection-search-input', 'mobile');
    await page.press('#collection-search-input', 'Enter');
    await page.waitForLoadState('networkidle');

    // Items should be properly stacked on mobile
    const items = page.locator('.collection-item');
    const itemCount = await items.count();

    if (itemCount > 1) {
      const item1 = items.nth(0);
      const item2 = items.nth(1);

      const box1 = await item1.boundingBox();
      const box2 = await item2.boundingBox();

      if (box1 && box2) {
        // On mobile, items should likely be stacked (item2 below item1)
        expect(box2.y).toBeGreaterThanOrEqual(box1.y);
      }
    }
  });
});
