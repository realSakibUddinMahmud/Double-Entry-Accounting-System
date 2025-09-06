import { test, expect } from '@playwright/test';

test('login and see dashboard widgets', async ({ page }) => {
  await page.goto('/login');
  await page.getByLabel('Phone Number').fill('01900000000');
  await page.getByLabel('Password').fill('secret123');
  await Promise.all([
    page.waitForURL('**/home'),
    page.getByRole('button', { name: 'Sign In' }).click(),
  ]);
  await expect(page.getByText('Welcome')).toBeVisible();
});

