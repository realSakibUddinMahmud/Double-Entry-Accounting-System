<?php

namespace Tests;

use Laravel\Dusk\TestCase as BaseTestCase;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Edge\EdgeOptions;
use Illuminate\Foundation\Testing\DatabaseMigrations;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseMigrations;

    /**
     * Prepare for Dusk test execution.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Set up test environment
        $this->setUpTestEnvironment();
    }

    /**
     * Create the RemoteWebDriver instance for Edge browser.
     */
    protected function driver(): RemoteWebDriver
    {
        // Use Edge browser for testing
        $options = (new EdgeOptions)->addArguments([
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--disable-extensions',
            '--disable-web-security',
            '--allow-running-insecure-content',
            '--disable-features=VizDisplayCompositor',
            '--window-size=1920,1080',
            '--disable-blink-features=AutomationControlled',
            '--disable-infobars',
            '--disable-notifications',
            '--disable-popup-blocking',
            '--disable-translate',
            '--disable-background-timer-throttling',
            '--disable-backgrounding-occluded-windows',
            '--disable-renderer-backgrounding',
            '--disable-features=TranslateUI',
            '--disable-ipc-flooding-protection',
            '--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0',
        ]);

        // Add headless mode for CI environments
        if (env('CI', false) || env('HEADLESS', false)) {
            $options->addArguments(['--headless']);
        }

        // Set Edge-specific capabilities
        $capabilities = DesiredCapabilities::edge();
        $capabilities->setCapability(EdgeOptions::CAPABILITY, $options);
        $capabilities->setCapability('ms:edgeOptions', $options->toArray());

        return RemoteWebDriver::create(
            'http://localhost:9516', // Edge WebDriver default port
            $capabilities
        );
    }

    /**
     * Create Chrome driver (fallback option)
     */
    protected function chromeDriver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments([
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--disable-extensions',
            '--disable-web-security',
            '--allow-running-insecure-content',
            '--disable-features=VizDisplayCompositor',
            '--window-size=1920,1080',
        ]);

        if (env('CI', false) || env('HEADLESS', false)) {
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

    /**
     * Set up test environment
     */
    protected function setUpTestEnvironment(): void
    {
        // Set test environment variables
        config([
            'app.env' => 'testing',
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'session.driver' => 'array',
            'cache.driver' => 'array',
            'queue.default' => 'sync',
        ]);

        // Clear caches
        $this->artisan('config:clear');
        $this->artisan('cache:clear');
        $this->artisan('view:clear');
    }

    /**
     * Create a new browser instance
     */
    protected function newBrowser($driver): \Laravel\Dusk\Browser
    {
        return new \Laravel\Dusk\Browser($driver);
    }

    /**
     * Take a screenshot on test failure
     */
    protected function onNotSuccessfulTest(\Throwable $t): void
    {
        if ($this->browser) {
            $this->browser->screenshot('failure-' . $this->getName());
        }

        parent::onNotSuccessfulTest($t);
    }

    /**
     * Wait for page to load completely
     */
    protected function waitForPageLoad(\Laravel\Dusk\Browser $browser): void
    {
        $browser->waitUntil('document.readyState === "complete"');
    }

    /**
     * Assert that user is logged in
     */
    protected function assertLoggedIn(\Laravel\Dusk\Browser $browser): void
    {
        $browser->assertPathIs('/admin/dashboard');
    }

    /**
     * Assert that user is logged out
     */
    protected function assertLoggedOut(\Laravel\Dusk\Browser $browser): void
    {
        $browser->assertPathIs('/login');
    }

    /**
     * Login as a specific user
     */
    protected function loginAsUser(\Laravel\Dusk\Browser $browser, $user): void
    {
        $browser->visit('/login')
            ->type('email', $user->email)
            ->type('password', 'password')
            ->press('Login')
            ->assertLoggedIn();
    }

    /**
     * Create a test user with specific role
     */
    protected function createUserWithRole(string $role): \App\Models\User
    {
        $user = \App\Models\User::factory()->create();

        if (\Spatie\Permission\Models\Role::where('name', $role)->exists()) {
            $user->assignRole($role);
        }

        return $user;
    }

    /**
     * Create test data for browser tests
     */
    protected function createTestData(): void
    {
        // Seed basic test data
        $this->artisan('db:seed', ['--class' => 'TestChartOfAccountsSeeder']);

        // Create test users with different roles
        $this->createUserWithRole('admin');
        $this->createUserWithRole('accountant');
        $this->createUserWithRole('manager');
        $this->createUserWithRole('user');
    }

    /**
     * Clean up after test
     */
    protected function tearDown(): void
    {
        if ($this->browser) {
            $this->browser->quit();
        }

        parent::tearDown();
    }
}
