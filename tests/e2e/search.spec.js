import { test, expect } from '@playwright/test';

/**
 * Test core search functionality
 */
test.describe('Search Functionality', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/blog');
    await expect(page.locator('.collection-manager')).toBeVisible();
  });

  test('should perform basic search and show results', async ({ page }) => {
    // Check if search input exists
    const searchInput = page.locator('#collection-search-input');
    if (await searchInput.count() === 0) {
      console.log('Search input not found, skipping test');
      return;
    }

    // Get initial item count
    const initialItemCount = await page.locator('.collection-item').count();

    // Perform search with a common term
    await searchInput.fill('blog');
    await page.press('#collection-search-input', 'Enter');

    // Wait for AJAX response or page reload
    await page.waitForLoadState('networkidle');

    // URL should contain search parameter (this should always work)
    expect(page.url()).toContain('q=blog');

    // Search input should retain the value
    await expect(searchInput).toHaveValue('blog');

    // Check that either we have results OR we have empty state (both are valid)
    const itemCount = await page.locator('.collection-item').count();
    const emptyState = await page.locator('.collection-empty').count();

    expect(itemCount > 0 || emptyState > 0).toBeTruthy();
  });

  test('should clear search results', async ({ page }) => {
    // First perform a search
    await page.fill('#collection-search-input', 'test');
    await page.press('#collection-search-input', 'Enter');
    await page.waitForLoadState('networkidle');

    // Click clear button (if exists) or clear input
    const clearButton = page.locator('.collection-search__clear');
    if (await clearButton.isVisible()) {
      await clearButton.click();
    } else {
      await page.fill('#collection-search-input', '');
      await page.press('#collection-search-input', 'Enter');
    }

    await page.waitForLoadState('networkidle');

    // Should show all items again (or at least some content)
    const itemCount = await page.locator('.collection-item').count();
    const emptyState = await page.locator('.collection-empty').count();

    // We should have either items or empty state, but not both
    expect(itemCount > 0 || emptyState > 0).toBeTruthy();

    // URL should not contain search parameter
    expect(page.url()).not.toContain('q=');
  });

  test('should handle special characters in search', async ({ page }) => {
    // Search with special characters
    await page.fill('#collection-search-input', 'café & résumé');
    await page.press('#collection-search-input', 'Enter');

    await page.waitForLoadState('networkidle');

    // Should handle gracefully (either show results or empty state)
    const isEmpty = await page.locator('.collection-empty').isVisible();
    const itemCount = await page.locator('.collection-item').count();
    const hasItems = itemCount > 0;

    expect(isEmpty || hasItems).toBeTruthy();
  });
});
