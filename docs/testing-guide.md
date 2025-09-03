# Testing Guide - Double Entry Accounting System

## Overview

This document provides comprehensive guidance for testing the Double Entry Accounting System. The testing strategy follows ISTQB best practices and focuses on risk-based testing with emphasis on accounting invariants and financial data integrity.

## Quick Start

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ (for browser tests)
- MySQL/PostgreSQL (for CI)
- SQLite (for local testing)

### Installation

```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations and seeders
php artisan migrate --seed

# Install NPM dependencies (for browser tests)
npm install
```

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
php artisan test --testsuite=Browser

# Run tests with coverage
php artisan test --coverage

# Run tests in parallel (faster)
php artisan test --parallel

# Run specific test file
php artisan test tests/Unit/MoneyMathTest.php

# Run tests with specific filter
php artisan test --filter=test_money_calculations_use_integer_cents
```

## Test Structure

### Test Pyramid

```
    /\
   /  \     Browser Tests (E2E)
  /____\    - Critical user journeys
 /      \   - UI workflows
/________\  - Permission boundaries

   /\
  /  \      Feature Tests (Integration)
 /____\     - HTTP endpoints
/      \    - Database operations
/______\    - Business logic integration

  /\
 /  \       Unit Tests (Domain Logic)
/____\      - Money calculations
/    \      - Accounting rules
/____\      - Model relationships
```

### Directory Structure

```
tests/
├── Unit/                    # Domain logic tests
│   ├── MoneyMathTest.php
│   ├── JournalBalancingTest.php
│   └── DeAccountTest.php
├── Feature/                 # Integration tests
│   ├── Auth/
│   │   └── PermissionsTest.php
│   ├── Journal/
│   │   └── PostEntryTest.php
│   └── Reports/
│       └── TrialBalanceTest.php
├── Browser/                 # E2E tests (Dusk)
│   ├── JournalWorkflowTest.php
│   └── PermissionUITest.php
└── TestCase.php            # Base test class
```

## Core Accounting Invariants

### 1. Balance Invariant
Every journal entry must be balanced:
```php
Σ(debits) == Σ(credits) within transaction currency
```

**Test Example:**
```php
public function test_balanced_journal_entries_are_accepted(): void
{
    $debitAmount = 10000;
    $creditAmount = 10000;
    
    $this->assertTrue($this->isJournalBalanced($debitAmount, $creditAmount));
}
```

### 2. Trial Balance Invariant
Trial balance over any closed period nets to zero:
```php
TrialBalance(date_range) == 0
```

**Test Example:**
```php
public function test_trial_balance_equals_zero(): void
{
    $this->createBalancedTransactions();
    $trialBalance = $this->calculateTrialBalance();
    
    $this->assertEquals(0, $trialBalance);
}
```

### 3. Posting Integrity
No posting of invalid entries:
- Missing account references
- Closed periods
- Invalid currency/precision

**Test Example:**
```php
public function test_posting_unbalanced_entry_fails(): void
{
    $response = $this->postJson('/api/journals', $unbalancedData);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['balance']);
}
```

### 4. Reversal Integrity
Reversals must negate original exactly:
```php
ReversalEntry.amount == -OriginalEntry.amount
OriginalEntry.locked == true
```

## Test Categories

### Unit Tests

**Purpose:** Test pure domain logic, money math, and business rules.

**Key Areas:**
- Money calculations (integer cents, no floats)
- Journal balancing logic
- Account type rules
- Model relationships

**Example:**
```php
class MoneyMathTest extends TestCase
{
    public function test_money_calculations_use_integer_cents(): void
    {
        $amount = 10000; // $100.00 in cents
        $transaction = new DeAccountTransaction(['amount' => $amount]);
        
        $this->assertIsInt($transaction->amount);
        $this->assertEquals(0, $amount % 1); // No decimal places
    }
}
```

### Feature Tests

**Purpose:** Test HTTP endpoints, database operations, and business logic integration.

**Key Areas:**
- API endpoints
- Permission boundaries
- Database transactions
- Multi-tenant isolation

**Example:**
```php
class PostEntryTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_posting_balanced_journal_entry(): void
    {
        $response = $this->actingAs($user)
            ->postJson('/api/journals', $balancedData);
        
        $response->assertStatus(201);
        $this->assertDatabaseHas('de_journals', $expectedData);
    }
}
```

### Browser Tests

**Purpose:** Test critical user journeys and UI workflows.

**Key Areas:**
- Login → Create Journal → Post
- Permission-based UI access
- Form validation
- Error handling

**Example:**
```php
class JournalWorkflowTest extends DuskTestCase
{
    public function test_create_and_post_journal_entry(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($user)
                ->visit('/admin/journals/create')
                ->type('amount', '100.00')
                ->select('debit_account_id', $cashAccount->id)
                ->select('credit_account_id', $revenueAccount->id)
                ->press('Post Entry')
                ->assertSee('Journal entry posted successfully');
        });
    }
}
```

## Test Data Management

### Factories

Use factories to create test data with realistic defaults:

```php
// Create balanced journal entry
$journal = DeJournal::factory()
    ->amount(10000)
    ->sales()
    ->create();

// Create account with specific type
$cashAccount = DeAccount::factory()
    ->cash()
    ->create();
```

### Seeders

Use seeders for consistent test data:

```php
// Seed chart of accounts
$this->seed(TestChartOfAccountsSeeder::class);

// Seed permissions
$this->seed(RolePermissionSeeder::class);
```

### Database

- **Unit Tests:** SQLite in-memory
- **Feature Tests:** SQLite with RefreshDatabase
- **CI:** MySQL/PostgreSQL for realism

## Performance Testing

### Query Limits

Set performance budgets for critical endpoints:

```php
public function test_journal_list_performance(): void
{
    $this->assertQueryCount(12, function () {
        $response = $this->get('/api/journals');
        $response->assertStatus(200);
    });
}
```

### Response Times

```php
public function test_api_response_times(): void
{
    $startTime = microtime(true);
    
    $response = $this->get('/api/reports/trial-balance');
    
    $responseTime = microtime(true) - $startTime;
    $this->assertLessThan(0.2, $responseTime); // < 200ms
}
```

## Coverage Targets

### Minimum Coverage
- **Unit Tests:** 20+ tests
- **Feature Tests:** 20+ tests
- **Browser Tests:** 3+ critical flows
- **Overall Coverage:** 70% lines, 80% statements

### Critical Path Coverage
- **Accounting Logic:** 95%+ coverage
- **Permission Gates:** 100% coverage
- **Money Calculations:** 100% coverage
- **Inventory Integration:** 90%+ coverage

## CI/CD Integration

### GitHub Actions

The CI pipeline runs:
- Unit and Feature tests on PHP 8.2/8.3
- Browser tests with Dusk
- Security scanning
- Performance testing
- Coverage reporting

### Local Development

```bash
# Run tests before committing
composer test

# Run specific test suite
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage

# Run browser tests
php artisan dusk
```

## Debugging Tests

### Common Issues

1. **Database Issues:**
   ```bash
   # Clear test database
   php artisan migrate:fresh --seed
   ```

2. **Permission Issues:**
   ```bash
   # Clear permission cache
   php artisan permission:cache-reset
   ```

3. **Browser Test Issues:**
   ```bash
   # Update Chrome driver
   php artisan dusk:chrome-driver
   ```

### Debugging Tips

1. **Use `dd()` in tests:**
   ```php
   $response = $this->get('/api/journals');
   dd($response->json());
   ```

2. **Check database state:**
   ```php
   $this->assertDatabaseHas('de_journals', [
       'amount' => 10000,
   ]);
   ```

3. **Use `dump()` for complex data:**
   ```php
   dump($journal->toArray());
   ```

## Best Practices

### Test Naming
- Use descriptive names: `test_posts_balanced_journal_entry_and_writes_ledger_lines`
- Follow pattern: `test_[action]_[expected_result]`

### Test Organization
- One assertion per test (when possible)
- Group related tests in classes
- Use `setUp()` for common test data

### Data Management
- Use factories for test data
- Clean up after tests with `RefreshDatabase`
- Use realistic test data

### Performance
- Run tests in parallel when possible
- Use in-memory databases for unit tests
- Mock external services

## Troubleshooting

### Common Errors

1. **"Class not found" errors:**
   ```bash
   composer dump-autoload
   ```

2. **Database connection errors:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

3. **Permission errors:**
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

### Getting Help

1. Check the test plan: `docs/test-plan.md`
2. Review existing tests for patterns
3. Check CI logs for detailed error messages
4. Use Laravel's testing documentation

## Contributing

When adding new tests:

1. Follow the existing patterns
2. Add to appropriate test suite
3. Update coverage targets if needed
4. Document any new test utilities
5. Ensure CI passes

### Test Checklist

- [ ] Test covers critical business logic
- [ ] Test is deterministic (no random failures)
- [ ] Test uses appropriate factories/seeders
- [ ] Test follows naming conventions
- [ ] Test has clear assertions
- [ ] Test cleans up after itself
- [ ] Test is fast (< 1 second)
- [ ] Test is isolated (no dependencies on other tests)
