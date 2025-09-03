# Browser Testing Setup Guide - Double Entry Accounting System

## 🎯 **Overview**

This guide provides step-by-step instructions for setting up and running browser tests for the Double Entry Accounting System using Laravel Dusk and browser automation tools.

## 🛠️ **Prerequisites**

### **System Requirements**
- **PHP:** 8.2 or higher
- **Laravel:** 12.x
- **Node.js:** 18+ (for frontend assets)
- **Chrome/Chromium:** Latest version
- **ChromeDriver:** Compatible with Chrome version
- **Operating System:** Windows, macOS, or Linux

### **Development Tools**
- **Composer:** For PHP dependencies
- **NPM/Yarn:** For frontend dependencies
- **Git:** For version control
- **IDE:** VS Code, PhpStorm, or similar

## 📦 **Installation Steps**

### **Step 1: Install Laravel Dusk**

```bash
# Install Laravel Dusk
composer require laravel/dusk --dev

# Publish Dusk configuration
php artisan dusk:install

# Install ChromeDriver
php artisan dusk:chrome-driver
```

### **Step 2: Configure Environment**

Create `.env.dusk.local` file:
```env
APP_NAME="Double Entry Accounting System"
APP_ENV=dusk
APP_KEY=base64:your-dusk-key-here
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=:memory:

BROADCAST_DRIVER=log
CACHE_DRIVER=array
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
SESSION_LIFETIME=120

MAIL_MAILER=array
```

### **Step 3: Update PHPUnit Configuration**

Update `phpunit.xml` to include Dusk testsuite:
```xml
<testsuites>
    <testsuite name="Unit">
        <directory>tests/Unit</directory>
    </testsuite>
    <testsuite name="Feature">
        <directory>tests/Feature</directory>
    </testsuite>
    <testsuite name="Browser">
        <directory>tests/Browser</directory>
    </testsuite>
</testsuites>
```

### **Step 4: Configure Dusk**

Update `tests/DuskTestCase.php`:
```php
<?php

namespace Tests;

use Laravel\Dusk\TestCase as BaseTestCase;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments([
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--window-size=1920,1080',
        ]);

        return RemoteWebDriver::create(
            'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY,
                $options
            )
        );
    }
}
```

## 🚀 **Running Browser Tests**

### **Basic Test Execution**

```bash
# Run all browser tests
php artisan dusk

# Run specific test file
php artisan dusk tests/Browser/JournalWorkflowTest.php

# Run specific test method
php artisan dusk --filter=test_complete_journal_entry_workflow

# Run tests with specific browser
php artisan dusk --browser=chrome

# Run tests in headless mode
php artisan dusk --headless
```

### **Advanced Test Execution**

```bash
# Run tests in parallel
php artisan dusk --parallel

# Run tests with coverage
php artisan dusk --coverage

# Run tests with specific environment
php artisan dusk --env=dusk

# Run tests with custom timeout
php artisan dusk --timeout=60
```

## 🎭 **Test Data Management**

### **Database Seeding**

Create test seeders for consistent data:
```php
// database/seeders/BrowserTestSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Database\Seeders\TestChartOfAccountsSeeder;

class BrowserTestSeeder extends Seeder
{
    public function run(): void
    {
        // Seed chart of accounts
        $this->call(TestChartOfAccountsSeeder::class);
        
        // Create test users with roles
        $this->createTestUsers();
    }
    
    private function createTestUsers(): void
    {
        $admin = User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
        ]);
        $admin->assignRole('admin');
        
        $accountant = User::factory()->create([
            'name' => 'Test Accountant',
            'email' => 'accountant@test.com',
        ]);
        $accountant->assignRole('accountant');
    }
}
```

### **Test Data Cleanup**

Use database transactions for test isolation:
```php
use Illuminate\Foundation\Testing\DatabaseTransactions;

class JournalWorkflowTest extends DuskTestCase
{
    use DatabaseTransactions;
    
    // Tests will automatically rollback database changes
}
```

## 🔧 **Browser Test Configuration**

### **Chrome Options Configuration**

```php
protected function driver(): RemoteWebDriver
{
    $options = (new ChromeOptions)->addArguments([
        '--disable-gpu',
        '--no-sandbox',
        '--disable-dev-shm-usage',
        '--disable-extensions',
        '--disable-web-security',
        '--allow-running-insecure-content',
        '--window-size=1920,1080',
        '--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    ]);

    // Add headless mode for CI
    if (env('CI', false)) {
        $options->addArguments(['--headless']);
    }

    return RemoteWebDriver::create(
        'http://localhost:9515',
        DesiredCapabilities::chrome()->setCapability(
            ChromeOptions::CAPABILITY,
            $options
        )
    );
}
```

### **Screenshot and Video Configuration**

```php
// Take screenshots on failure
protected function onNotSuccessfulTest(\Throwable $t): void
{
    if ($this->browser) {
        $this->browser->screenshot('failure-' . $this->getName());
    }
    
    parent::onNotSuccessfulTest($t);
}

// Record video of test execution
protected function setUp(): void
{
    parent::setUp();
    
    if (env('RECORD_VIDEO', false)) {
        $this->browser->startRecording();
    }
}
```

## 📊 **Test Reporting**

### **HTML Report Generation**

```bash
# Generate HTML test report
php artisan dusk --html=storage/app/dusk-report.html

# Generate JUnit XML report
php artisan dusk --junit=storage/app/dusk-report.xml
```

### **Coverage Reports**

```bash
# Generate coverage report
php artisan dusk --coverage --coverage-html=storage/app/coverage

# Generate coverage with specific threshold
php artisan dusk --coverage --coverage-threshold=80
```

## 🐛 **Debugging Browser Tests**

### **Debug Mode**

```php
// Pause test execution for debugging
$browser->pause(5000); // Pause for 5 seconds

// Take screenshot for debugging
$browser->screenshot('debug-screenshot');

// Dump page source
$browser->dump();

// Dump specific element
$browser->dump('@element-selector');
```

### **Console Logs**

```php
// Capture console logs
$browser->script('console.log("Test message");');
$logs = $browser->driver->manage()->getLog('browser');
```

### **Network Monitoring**

```php
// Enable network monitoring
$browser->driver->executeScript('
    window.networkLogs = [];
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        window.networkLogs.push(args);
        return originalFetch.apply(this, args);
    };
');
```

## 🚀 **CI/CD Integration**

### **GitHub Actions Configuration**

```yaml
# .github/workflows/dusk.yml
name: Browser Tests

on: [push, pull_request]

jobs:
  dusk:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: 8.3
        extensions: dom, curl, libxml, mbstring, zip, pcntl, pdo, sqlite, pdo_sqlite, bcmath, soap, intl, gd, exif, iconv, imagick
    
    - name: Install dependencies
      run: composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist
    
    - name: Generate key
      run: php artisan key:generate
    
    - name: Create Database
      run: touch database/database.sqlite
    
    - name: Install Laravel Dusk
      run: php artisan dusk:install --no-interaction
    
    - name: Start ChromeDriver
      run: ./vendor/laravel/dusk/bin/chromedriver-linux &
    
    - name: Run Dusk Tests
      run: php artisan dusk
      env:
        APP_ENV: dusk
        DB_CONNECTION: sqlite
        DB_DATABASE: :memory:
    
    - name: Upload Screenshots
      uses: actions/upload-artifact@v2
      if: failure()
      with:
        name: screenshots
        path: tests/Browser/screenshots/
```

### **Local CI Setup**

```bash
# Install ChromeDriver
php artisan dusk:chrome-driver

# Start ChromeDriver in background
./vendor/laravel/dusk/bin/chromedriver-linux &

# Run tests
php artisan dusk
```

## 📱 **Mobile Testing**

### **Responsive Testing**

```php
// Test mobile viewport
$browser->resize(375, 667) // iPhone SE
    ->visit('/admin/dashboard')
    ->assertVisible('@mobile-menu');

// Test tablet viewport
$browser->resize(768, 1024) // iPad
    ->visit('/admin/products')
    ->assertVisible('@tablet-layout');
```

### **Touch Interactions**

```php
// Simulate touch interactions
$browser->touch('@mobile-button')
    ->swipe('@swipe-element', 'left', 100)
    ->pinch('@pinch-element', 1.5);
```

## 🔒 **Security Testing**

### **Authentication Testing**

```php
// Test login security
$browser->visit('/login')
    ->type('email', 'invalid@example.com')
    ->type('password', 'wrongpassword')
    ->press('Login')
    ->assertSee('These credentials do not match our records');

// Test session timeout
$browser->loginAs($user)
    ->visit('/admin/dashboard')
    ->script('localStorage.clear(); sessionStorage.clear();')
    ->visit('/admin/dashboard')
    ->assertPathIs('/login');
```

### **Permission Testing**

```php
// Test role-based access
$browser->loginAs($userWithoutPermission)
    ->visit('/admin/users')
    ->assertSee('Access Denied')
    ->assertDontSee('Add User');
```

## 📈 **Performance Testing**

### **Response Time Testing**

```php
// Measure page load time
$startTime = microtime(true);
$browser->visit('/admin/dashboard');
$loadTime = microtime(true) - $startTime;
$this->assertLessThan(3.0, $loadTime, 'Page load time exceeded 3 seconds');
```

### **Memory Usage Testing**

```php
// Monitor memory usage
$memoryBefore = memory_get_usage();
$browser->visit('/admin/reports/trial-balance');
$memoryAfter = memory_get_usage();
$memoryUsed = $memoryAfter - $memoryBefore;
$this->assertLessThan(50 * 1024 * 1024, $memoryUsed, 'Memory usage exceeded 50MB');
```

## 🎯 **Best Practices**

### **Test Organization**
- **One test per user journey**
- **Descriptive test names**
- **Proper test data setup**
- **Clean test isolation**

### **Element Selection**
- **Use data attributes for selectors**
- **Avoid brittle CSS selectors**
- **Create reusable page objects**
- **Use meaningful element names**

### **Error Handling**
- **Take screenshots on failure**
- **Log detailed error information**
- **Implement retry mechanisms**
- **Handle flaky tests gracefully**

### **Maintenance**
- **Regular test review and updates**
- **Remove obsolete tests**
- **Keep test data current**
- **Monitor test execution time**

## 🚨 **Troubleshooting**

### **Common Issues**

**ChromeDriver Issues:**
```bash
# Update ChromeDriver
php artisan dusk:chrome-driver

# Check Chrome version
google-chrome --version

# Verify ChromeDriver compatibility
./vendor/laravel/dusk/bin/chromedriver-linux --version
```

**Database Issues:**
```bash
# Clear test database
php artisan migrate:fresh --seed

# Reset database state
php artisan db:wipe
php artisan migrate
```

**Permission Issues:**
```bash
# Fix file permissions
chmod -R 755 storage bootstrap/cache

# Clear caches
php artisan config:clear
php artisan cache:clear
```

### **Debug Commands**

```bash
# Run single test with verbose output
php artisan dusk tests/Browser/JournalWorkflowTest.php --verbose

# Run tests with debug mode
php artisan dusk --debug

# Check test environment
php artisan dusk:install --force
```

This setup guide provides comprehensive instructions for implementing browser testing for your Double Entry Accounting System, ensuring reliable end-to-end validation of user workflows and system functionality.
