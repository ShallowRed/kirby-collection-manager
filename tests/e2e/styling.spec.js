import { test, expect } from '@playwright/test';

test.describe('Styling customization', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/blog');
  });

  test('auto-injects the default stylesheet once when enableCss is on', async ({ page }) => {
    await expect(page.locator('link[href*="collection-manager.css"]')).toHaveCount(1);
  });

  test('appends custom classes from the classes config', async ({ page }) => {
    // Filter pills keep the BEM class and gain the custom ones
    const filter = page.locator('.collection-filter.badge').first();
    await expect(filter).toBeVisible();

    // Active filter gets the active custom class
    await expect(page.locator('.collection-filter--active.badge-primary').first()).toBeVisible();

    // Items keep data-testid and gain the custom card classes
    const item = page.locator('.collection-item.card').first();
    await expect(item).toBeVisible();

    // Sorting select gains the custom classes
    await expect(page.locator('.collection-sorting__select.select')).toBeVisible();
  });

  test('keeps custom classes on htmx fragments', async ({ page }) => {
    await page.locator('[data-testid="collection-pagination-page-2"]').click();
    await page.waitForURL(/p=2/);

    await expect(page.locator('.collection-item.card').first()).toBeVisible();
    await expect(page.locator('.collection-filter.badge').first()).toBeVisible();
  });
});
