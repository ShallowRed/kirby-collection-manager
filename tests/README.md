# Collection Manager E2E Tests

Simple end-to-end tests for the Kirby Collection Manager plugin using Playwright.

## Setup

1. Install dependencies:
   ```bash
   npm install
   ```

2. Install Playwright browsers:
   ```bash
   npx playwright install
   ```

## Running Tests

### Local Development
```bash
# Run all tests (starts demo server automatically)
npm test

# Run tests with UI mode
npm run test:ui

# Run tests in headed mode (see browser)
npm run test:headed

# Run specific test file
npx playwright test empty-search-bug.spec.js

# Run tests in specific browser
npx playwright test --project=chromium
```

### Manual Server
If you prefer to start the server manually:
```bash
# Terminal 1: Start demo server
cd demo && php -S localhost:8000 -t .

# Terminal 2: Run tests (without webServer auto-start)
npx playwright test --config=playwright-manual.config.js
```

## Test Files

- **`empty-search-bug.spec.js`** - Tests the critical pagination bug with empty search results
- **`search.spec.js`** - Core search functionality tests
- **`pagination.spec.js`** - Pagination behavior tests
- **`workflows.spec.js`** - Complete user workflow tests

## Key Test Scenarios

### 🐛 Bug Prevention
- Empty search results showing phantom pagination
- Search from page 3 not resetting pagination
- Grid layout preservation during AJAX updates

### ✅ Core Features
- Basic search functionality
- Pagination navigation
- Browser back/forward navigation
- Mobile responsiveness
- Search + filter + pagination workflows

## Configuration

The tests are configured to:
- Run against `http://localhost:8000` (demo server)
- Auto-start the demo server if not running
- Take screenshots on failure
- Generate HTML reports
- Run in parallel for speed

## Debugging

View test results:
```bash
npx playwright show-report
```

Debug specific test:
```bash
npx playwright test --debug empty-search-bug.spec.js
```
