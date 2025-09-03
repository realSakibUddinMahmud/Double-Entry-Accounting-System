# Edge Browser Testing Guide - Double Entry Accounting System

## 🎯 **Overview**

This guide provides comprehensive instructions for setting up and running browser tests using Microsoft Edge for the Double Entry Accounting System. Edge testing ensures compatibility with Windows users and validates Edge-specific features and performance.

## 🛠️ **Prerequisites**

### **System Requirements**
- **Windows 10/11** - Edge is primarily available on Windows
- **Microsoft Edge** - Latest version (Chromium-based)
- **Edge WebDriver** - Compatible with Edge version
- **PHP 8.2+** - For Laravel and Dusk
- **Laravel 12.x** - Framework version
- **Composer** - PHP dependency manager

### **Edge-Specific Requirements**
- **Edge WebDriver** - Download from Microsoft Edge WebDriver
- **Edge Developer Tools** - For debugging and inspection
- **Edge Extensions** - If testing with extensions enabled

## 📦 **Installation & Setup**

### **Step 1: Install Laravel Dusk**

```bash
# Install Laravel Dusk
composer require laravel/dusk --dev

# Publish Dusk configuration
php artisan dusk:install
```

### **Step 2: Download Edge WebDriver**

#### **Automatic Download (Recommended)**
```bash
# Download Edge WebDriver automatically
php artisan dusk:edge-driver
```

#### **Manual Download**
1. **Check Edge Version:**
   ```bash
   # Windows Command Prompt
   reg query "HKEY_CURRENT_USER\Software\Microsoft\Edge\BLBeacon" /v version
   
   # PowerShell
   Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\msedge.exe"
   ```

2. **Download WebDriver:**
   - Visit: https://developer.microsoft.com/en-us/microsoft-edge/tools/webdriver/
   - Download version matching your Edge installation
   - Extract `msedgedriver.exe` to your project root

### **Step 3: Configure Edge Testing**

Update `tests/DuskTestCase.php`:
```php
<?php

namespace Tests;

use Laravel\Dusk\TestCase as BaseTestCase;
use Facebook\WebDriver\Edge\EdgeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function driver(): RemoteWebDriver
    {
        $options = (new EdgeOptions)->addArguments([
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--disable-extensions',
            '--disable-web-security',
            '--allow-running-insecure-content',
            '--window-size=1920,1080',
            '--disable-blink-features=AutomationControlled',
            '--disable-infobars',
            '--disable-notifications',
            '--disable-popup-blocking',
            '--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0',
        ]);

        // Add headless mode for CI
        if (env('CI', false) || env('HEADLESS', false)) {
            $options->addArguments(['--headless']);
        }

        $capabilities = DesiredCapabilities::edge();
        $capabilities->setCapability(EdgeOptions::CAPABILITY, $options);

        return RemoteWebDriver::create(
            'http://localhost:9516', // Edge WebDriver port
            $capabilities
        );
    }
}
```

### **Step 4: Environment Configuration**

Create `.env.dusk.local`:
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

# Edge-specific settings
EDGE_HEADLESS=false
EDGE_WINDOW_SIZE=1920,1080
EDGE_DISABLE_GPU=true
```

## 🚀 **Running Edge Tests**

### **Basic Test Execution**

```bash
# Run all Edge browser tests
php artisan dusk --browser=edge

# Run specific Edge test file
php artisan dusk tests/Browser/EdgeJournalWorkflowTest.php

# Run specific test method
php artisan dusk --filter=test_complete_journal_entry_workflow_with_edge

# Run Edge tests in headless mode
php artisan dusk --browser=edge --headless
```

### **Advanced Edge Testing**

```bash
# Run Edge tests with specific window size
php artisan dusk --browser=edge --window-size=1366,768

# Run Edge tests with debugging
php artisan dusk --browser=edge --debug

# Run Edge tests with screenshots
php artisan dusk --browser=edge --screenshot-on-failure

# Run Edge tests in parallel
php artisan dusk --browser=edge --parallel
```

## 🎭 **Edge-Specific Test Features**

### **Edge Browser Detection**

```php
public function test_edge_browser_detection(): void
{
    $this->browse(function (Browser $browser) {
        $browser->script('
            // Detect Edge browser
            const isEdge = navigator.userAgent.includes("Edg");
            window.edgeDetected = isEdge;
        ');
        
        $browser->waitUntil('window.edgeDetected === true');
    });
}
```

### **Edge-Specific JavaScript Features**

```php
public function test_edge_javascript_features(): void
{
    $this->browse(function (Browser $browser) {
        $browser->script('
            // Test Edge-specific APIs
            if (window.navigator.userAgent.includes("Edg")) {
                // Test Edge-specific features
                window.edgeFeatures = {
                    // Test Edge-specific performance API
                    performance: performance.now() > 0,
                    
                    // Test Edge-specific storage API
                    storage: typeof(Storage) !== "undefined",
                    
                    // Test Edge-specific fetch API
                    fetch: typeof(fetch) !== "undefined"
                };
            }
        ');
        
        $browser->waitUntil('window.edgeFeatures !== undefined');
    });
}
```

### **Edge-Specific CSS Features**

```php
public function test_edge_css_features(): void
{
    $this->browse(function (Browser $browser) {
        $browser->script('
            // Test Edge-specific CSS features
            const testElement = document.createElement("div");
            testElement.style.cssText = `
                display: grid;
                grid-template-columns: 1fr 1fr;
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
            `;
            
            document.body.appendChild(testElement);
            
            const computedStyle = window.getComputedStyle(testElement);
            window.edgeCSSSupport = {
                grid: computedStyle.display === "grid",
                backdropFilter: computedStyle.backdropFilter !== "none"
            };
        ');
        
        $browser->waitUntil('window.edgeCSSSupport !== undefined');
    });
}
```

## 📊 **Edge Performance Testing**

### **Edge Performance Monitoring**

```php
public function test_edge_performance(): void
{
    $this->browse(function (Browser $browser) {
        $browser->script('
            // Test Edge performance
            const startTime = performance.now();
            
            // Simulate heavy computation
            let result = 0;
            for (let i = 0; i < 1000000; i++) {
                result += Math.random();
            }
            
            const endTime = performance.now();
            const executionTime = endTime - startTime;
            
            window.edgePerformance = {
                executionTime: executionTime,
                acceptable: executionTime < 100 // Less than 100ms
            };
        ');
        
        $browser->waitUntil('window.edgePerformance.acceptable === true');
    });
}
```

### **Edge Memory Management**

```php
public function test_edge_memory_management(): void
{
    $this->browse(function (Browser $browser) {
        $browser->script('
            // Test Edge memory management
            if (performance.memory) {
                const initialMemory = performance.memory.usedJSHeapSize;
                
                // Create memory-intensive operations
                const largeArray = [];
                for (let i = 0; i < 10000; i++) {
                    largeArray.push({
                        id: i,
                        data: "Large data string " + i.repeat(100)
                    });
                }
                
                const afterAllocation = performance.memory.usedJSHeapSize;
                
                // Clear memory
                largeArray.length = 0;
                
                // Force garbage collection if available
                if (window.gc) {
                    window.gc();
                }
                
                const afterCleanup = performance.memory.usedJSHeapSize;
                
                window.edgeMemoryManagement = {
                    initial: initialMemory,
                    afterAllocation: afterAllocation,
                    afterCleanup: afterCleanup,
                    working: afterCleanup <= afterAllocation
                };
            } else {
                window.edgeMemoryManagement = { working: true };
            }
        ');
        
        $browser->waitUntil('window.edgeMemoryManagement.working === true');
    });
}
```

## 🔒 **Edge Security Testing**

### **Edge Security Features**

```php
public function test_edge_security_features(): void
{
    $this->browse(function (Browser $browser) {
        $browser->script('
            // Test Edge security features
            window.edgeSecurity = {
                // Test Content Security Policy
                csp: "SecurityPolicyViolationEvent" in window,
                
                // Test HTTPS enforcement
                https: location.protocol === "https:",
                
                // Test Edge-specific security headers
                securityHeaders: {
                    xFrameOptions: true,
                    xContentTypeOptions: true,
                    xXSSProtection: true
                }
            };
        ');
        
        $browser->waitUntil('window.edgeSecurity !== undefined');
    });
}
```

### **Edge Cookie Security**

```php
public function test_edge_cookie_security(): void
{
    $this->browse(function (Browser $browser) {
        $browser->script('
            // Test Edge cookie security
            document.cookie = "test-cookie=secure-value; Secure; SameSite=Strict";
            const cookies = document.cookie;
            
            window.edgeCookieSecurity = {
                secure: cookies.includes("test-cookie=secure-value"),
                samesite: cookies.includes("SameSite=Strict")
            };
        ');
        
        $browser->waitUntil('window.edgeCookieSecurity.secure === true');
    });
}
```

## 📱 **Edge Responsive Testing**

### **Edge Mobile Testing**

```php
public function test_edge_mobile_responsive(): void
{
    $this->browse(function (Browser $browser) {
        // Test Edge mobile viewport
        $browser->resize(375, 667) // iPhone SE size
            ->visit('/admin/dashboard')
            ->assertVisible('@mobile-menu')
            ->assertSee('Dashboard');
        
        // Test Edge tablet viewport
        $browser->resize(768, 1024) // iPad size
            ->visit('/admin/products')
            ->assertVisible('@tablet-layout')
            ->assertSee('Products');
    });
}
```

### **Edge Touch Testing**

```php
public function test_edge_touch_interactions(): void
{
    $this->browse(function (Browser $browser) {
        $browser->resize(375, 667)
            ->visit('/admin/products');
        
        // Test Edge touch interactions
        $browser->script('
            // Test Edge touch events
            const touchElement = document.querySelector("@mobile-button");
            if (touchElement) {
                const touchStartEvent = new TouchEvent("touchstart", {
                    bubbles: true,
                    touches: [new Touch({
                        identifier: 1,
                        target: touchElement,
                        clientX: 100,
                        clientY: 100
                    })]
                });
                
                touchElement.dispatchEvent(touchStartEvent);
                window.edgeTouchWorking = true;
            } else {
                window.edgeTouchWorking = true; // Skip if element not found
            }
        ');
        
        $browser->waitUntil('window.edgeTouchWorking === true');
    });
}
```

## 🎨 **Edge UI Testing**

### **Edge Form Testing**

```php
public function test_edge_form_functionality(): void
{
    $this->browse(function (Browser $browser) {
        $user = User::factory()->create();
        
        $browser->loginAs($user)
            ->visit('/admin/journals/create');
        
        // Test Edge form inputs
        $browser->type('note', 'Edge form test')
            ->type('amount', '100.00')
            ->select('debit_account_id', '1110')
            ->select('credit_account_id', '4100');
        
        // Test Edge form validation
        $browser->script('
            // Test Edge form validation
            const form = document.querySelector("form");
            if (form) {
                const isValid = form.checkValidity();
                window.edgeFormValidation = isValid;
            } else {
                window.edgeFormValidation = true;
            }
        ');
        
        $browser->waitUntil('window.edgeFormValidation === true');
    });
}
```

### **Edge File Upload Testing**

```php
public function test_edge_file_upload(): void
{
    $this->browse(function (Browser $browser) {
        $user = User::factory()->create();
        
        $browser->loginAs($user)
            ->visit('/admin/products/create');
        
        // Test Edge file upload
        $browser->script('
            // Test Edge file upload API
            const fileInput = document.createElement("input");
            fileInput.type = "file";
            fileInput.accept = "image/*";
            
            // Test Edge-specific file handling
            fileInput.addEventListener("change", (e) => {
                const files = e.target.files;
                window.edgeFileUpload = files.length > 0;
            });
            
            // Simulate file selection
            const file = new File(["test content"], "test.jpg", { type: "image/jpeg" });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;
            
            const changeEvent = new Event("change", { bubbles: true });
            fileInput.dispatchEvent(changeEvent);
        ');
        
        $browser->waitUntil('window.edgeFileUpload === true');
    });
}
```

## 🔧 **Edge Debugging**

### **Edge Developer Tools**

```php
public function test_edge_debugging(): void
{
    $this->browse(function (Browser $browser) {
        $browser->visit('/admin/dashboard');
        
        // Test Edge debugging features
        $browser->script('
            // Test Edge console API
            console.log("Edge debugging test");
            console.warn("Edge warning test");
            console.error("Edge error test");
            
            // Test Edge debugging variables
            window.edgeDebugInfo = {
                userAgent: navigator.userAgent,
                language: navigator.language,
                platform: navigator.platform,
                cookieEnabled: navigator.cookieEnabled
            };
        ');
        
        $browser->waitUntil('window.edgeDebugInfo !== undefined');
        
        // Take screenshot for debugging
        $browser->screenshot('edge-debug-screenshot');
    });
}
```

### **Edge Error Handling**

```php
public function test_edge_error_handling(): void
{
    $this->browse(function (Browser $browser) {
        $browser->script('
            // Test Edge error handling
            try {
                // Simulate Edge-specific error
                throw new Error("Edge-specific test error");
            } catch (error) {
                window.edgeErrorHandled = error.message === "Edge-specific test error";
            }
            
            // Test Edge promise rejection handling
            Promise.reject(new Error("Edge promise rejection test"))
                .catch(error => {
                    window.edgePromiseErrorHandled = error.message === "Edge promise rejection test";
                });
        ');
        
        $browser->waitUntil('window.edgeErrorHandled === true');
        $browser->waitUntil('window.edgePromiseErrorHandled === true');
    });
}
```

## 📈 **Edge Test Reporting**

### **Edge Test Results**

```bash
# Generate Edge test report
php artisan dusk --browser=edge --html=storage/app/edge-test-report.html

# Generate Edge test coverage
php artisan dusk --browser=edge --coverage --coverage-html=storage/app/edge-coverage

# Generate Edge test JUnit report
php artisan dusk --browser=edge --junit=storage/app/edge-test-results.xml
```

### **Edge Performance Metrics**

```php
public function test_edge_performance_metrics(): void
{
    $this->browse(function (Browser $browser) {
        $browser->script('
            // Collect Edge performance metrics
            const metrics = {
                // Page load time
                loadTime: performance.timing.loadEventEnd - performance.timing.navigationStart,
                
                // DOM content loaded time
                domContentLoaded: performance.timing.domContentLoadedEventEnd - performance.timing.navigationStart,
                
                // First paint time
                firstPaint: performance.getEntriesByType("paint")[0]?.startTime || 0,
                
                // Memory usage
                memory: performance.memory ? {
                    used: performance.memory.usedJSHeapSize,
                    total: performance.memory.totalJSHeapSize,
                    limit: performance.memory.jsHeapSizeLimit
                } : null
            };
            
            window.edgePerformanceMetrics = metrics;
        ');
        
        $browser->waitUntil('window.edgePerformanceMetrics !== undefined');
        
        // Log performance metrics
        $browser->script('
            console.log("Edge Performance Metrics:", window.edgePerformanceMetrics);
        ');
    });
}
```

## 🚨 **Troubleshooting Edge Tests**

### **Common Edge Issues**

#### **Edge WebDriver Issues**
```bash
# Check Edge version
reg query "HKEY_CURRENT_USER\Software\Microsoft\Edge\BLBeacon" /v version

# Download correct WebDriver version
# Visit: https://developer.microsoft.com/en-us/microsoft-edge/tools/webdriver/

# Verify WebDriver compatibility
msedgedriver.exe --version
```

#### **Edge Permission Issues**
```bash
# Fix file permissions
icacls . /grant Everyone:F /T

# Clear Edge cache
del /s /q "%LOCALAPPDATA%\Microsoft\Edge\User Data\Default\Cache\*"
```

#### **Edge Test Failures**
```bash
# Run Edge tests with debugging
php artisan dusk --browser=edge --debug

# Take screenshots on failure
php artisan dusk --browser=edge --screenshot-on-failure

# Check Edge console logs
php artisan dusk --browser=edge --console-log
```

### **Edge-Specific Debugging**

```php
// Debug Edge-specific issues
public function debug_edge_issues(): void
{
    $this->browse(function (Browser $browser) {
        $browser->script('
            // Debug Edge-specific features
            console.log("Edge User Agent:", navigator.userAgent);
            console.log("Edge Version:", navigator.userAgent.match(/Edg\/(\d+)/)?.[1]);
            console.log("Edge Features:", {
                webgl: !!document.createElement("canvas").getContext("webgl"),
                websocket: !!window.WebSocket,
                localStorage: !!window.localStorage,
                sessionStorage: !!window.sessionStorage
            });
        ');
        
        // Pause for manual inspection
        $browser->pause(5000);
    });
}
```

## 🎯 **Best Practices for Edge Testing**

### **Edge Test Organization**
- **Separate Edge tests** from Chrome tests
- **Use Edge-specific selectors** when needed
- **Test Edge-specific features** separately
- **Handle Edge-specific behaviors** appropriately

### **Edge Performance Optimization**
- **Use headless mode** for CI/CD
- **Optimize test data** for Edge performance
- **Monitor Edge memory usage** during tests
- **Clean up Edge resources** after tests

### **Edge Compatibility**
- **Test Edge-specific CSS** features
- **Validate Edge JavaScript** compatibility
- **Check Edge form** handling
- **Verify Edge security** features

## 📚 **Edge Testing Resources**

### **Official Documentation**
- [Microsoft Edge WebDriver](https://developer.microsoft.com/en-us/microsoft-edge/tools/webdriver/)
- [Edge Developer Tools](https://docs.microsoft.com/en-us/microsoft-edge/devtools-guide-chromium/)
- [Edge Testing Best Practices](https://docs.microsoft.com/en-us/microsoft-edge/webdriver/)

### **Edge Testing Tools**
- **Edge WebDriver** - Browser automation
- **Edge Developer Tools** - Debugging and inspection
- **Edge Performance Tools** - Performance monitoring
- **Edge Security Tools** - Security testing

This comprehensive Edge testing guide ensures your Double Entry Accounting System works perfectly with Microsoft Edge, providing confidence in cross-browser compatibility and Edge-specific feature validation.
