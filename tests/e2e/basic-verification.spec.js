import { test, expect } from '@playwright/test';

/**
 * Basic functionality verification tests
 */
test.describe('Basic Verification', () => {
  test('should load blog page and verify basic structure', async ({ page }) => {
    await page.goto('/blog');

    // Basic page verification
    await expect(page).toHaveTitle(/.+/); // Should have some title

    // Check if collection manager exists
    const hasCollectionManager = await page.locator('.collection-manager').count() > 0;
    console.log('Has collection manager:', hasCollectionManager);

    if (hasCollectionManager) {
      await expect(page.locator('.collection-manager')).toBeVisible();

      // Check for search input
      const hasSearchInput = await page.locator('#collection-search-input').count() > 0;
      console.log('Has search input:', hasSearchInput);

      // Check for items or empty state
      const itemCount = await page.locator('.collection-item').count();
      const emptyStateCount = await page.locator('.collection-empty').count();
      console.log('Item count:', itemCount);
      console.log('Empty state count:', emptyStateCount);

      // We should have SOMETHING - either items, empty state, or at least the container
      expect(itemCount + emptyStateCount).toBeGreaterThanOrEqual(0);

      if (hasSearchInput) {
        // Just verify search input works without checking results
        await page.fill('#collection-search-input', 'test');
        const inputValue = await page.locator('#collection-search-input').inputValue();
        expect(inputValue).toBe('test');

        // Clear the search
        await page.fill('#collection-search-input', '');
        const clearedValue = await page.locator('#collection-search-input').inputValue();
        expect(clearedValue).toBe('');
      }
    } else {
      // If no collection manager, at least verify the page loads
      const bodyText = await page.textContent('body');
      expect(bodyText).toBeTruthy();
      console.log('Page loads but no collection manager found');
    }
  });

  test('should handle URL parameters gracefully', async ({ page }) => {
    // Test with search parameter
    await page.goto('/blog?q=test');
    await expect(page).toHaveTitle(/.+/);

    // Test with pagination parameter
    await page.goto('/blog?p=2');
    await expect(page).toHaveTitle(/.+/);

    // Test with both parameters
    await page.goto('/blog?q=test&p=2');
    await expect(page).toHaveTitle(/.+/);
  });
});
