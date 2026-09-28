<!-- .ai/guidelines/pest-browser.blade.php -->
# Pest Browser Plugin (Playwright)

This project uses `pestphp/pest-plugin-browser` v4.3+ for browser testing via Playwright.

## Conventions
- Browser tests live in `tests/Browser/`
- Use `visit()` helper to start a browser test:
```php
it('can login', function () {
$page = visit('/login');
$page->fill('email', 'test@example.com')
->fill('password', 'secret')
->click('Login')
->assertSee('Dashboard');
});
