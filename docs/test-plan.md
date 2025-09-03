# Double Entry Accounting System - Test Plan

## Risk-Based Testing Strategy

### Critical Risk Areas (High Priority)
1. **Double-Entry Accounting Invariants** - Core business logic
2. **Financial Data Integrity** - Money calculations, balances
3. **Multi-Tenant Isolation** - Data segregation
4. **Permission Boundaries** - Authorization controls
5. **Inventory Valuation** - COGS calculations, stock tracking

### Medium Risk Areas
1. **API Endpoints** - Data validation, error handling
2. **UI Workflows** - Critical user journeys
3. **Performance** - Query optimization, N+1 prevention

### Low Risk Areas
1. **Static Content** - Views, styling
2. **Non-critical Features** - Reports, exports

## Coverage Map

### Unit Tests (Domain Logic)
| Component | Test Focus | Risk Level |
|-----------|------------|------------|
| `DeJournal` | Balance validation, posting logic | **HIGH** |
| `DeAccount` | Account type rules, hierarchy | **HIGH** |
| `DeAccountTransaction` | Debit/credit calculations | **HIGH** |
| Money Math | Precision, rounding, currency | **HIGH** |
| Inventory Models | Stock calculations, COGS | **MEDIUM** |

### Feature Tests (Integration)
| Component | Test Focus | Risk Level |
|-----------|------------|------------|
| Journal API | CRUD, validation, posting | **HIGH** |
| Account API | Chart management, permissions | **HIGH** |
| Purchase/Sale | Inventory integration, GL posting | **HIGH** |
| Permissions | Role-based access control | **HIGH** |
| Multi-tenancy | Data isolation | **HIGH** |
| Reports | Trial balance, P&L accuracy | **MEDIUM** |

### Browser Tests (E2E)
| Workflow | Test Focus | Risk Level |
|----------|------------|------------|
| Login → Create Journal → Post | Critical user journey | **HIGH** |
| Purchase → Inventory Update | Business process | **HIGH** |
| Sale → COGS Calculation | Financial accuracy | **HIGH** |
| Permission Denial | Security boundaries | **MEDIUM** |

## Accounting Invariants (Non-Negotiable)

### 1. Balance Invariant
```php
// Every journal entry must be balanced
Σ(debits) == Σ(credits) within transaction currency
```

### 2. Trial Balance Invariant  
```php
// Trial balance over any closed period nets to zero
TrialBalance(date_range) == 0
```

### 3. Posting Integrity
```php
// No posting of invalid entries
- Missing account references
- Closed periods
- Invalid currency/precision
```

### 4. Reversal Integrity
```php
// Reversals must negate original exactly
ReversalEntry.amount == -OriginalEntry.amount
OriginalEntry.locked == true
```

### 5. Multi-Currency Consistency
```php
// All lines in transaction must use same currency
Transaction.lines.all(currency) == Transaction.currency
```

### 6. Inventory Integration
```php
// Sale reduces inventory and creates COGS entry
Sale.post() → Inventory.quantity -= sale_quantity
Sale.post() → COGS_GL_entry.created
```

## Test Data Strategy

### Factories Required
- `DeAccountFactory` - Chart of accounts
- `DeJournalFactory` - Balanced journal entries
- `DeAccountTransactionFactory` - Debit/credit transactions
- `ProductFactory` - Inventory items
- `PurchaseFactory` / `SaleFactory` - Business transactions
- `UserFactory` - With roles and permissions

### Seeders Required
- `TestChartOfAccountsSeeder` - Standard COA
- `TestPermissionsSeeder` - Role/permission matrix
- `TestInventorySeeder` - Sample products and stock

## Performance Targets

### Query Limits
- Journal list view: ≤ 12 queries
- Account tree view: ≤ 8 queries  
- Trial balance report: ≤ 15 queries
- Purchase/Sale forms: ≤ 10 queries

### Response Times
- API endpoints: < 200ms
- Report generation: < 2s
- Page loads: < 1s

## Test Environment

### Database
- **Unit Tests**: SQLite in-memory
- **Feature Tests**: SQLite with RefreshDatabase
- **CI**: MySQL/PostgreSQL for realism

### Dependencies
- **PHPUnit**: Core testing framework
- **Laravel Dusk**: Browser testing
- **Faker**: Test data generation
- **Mockery**: Service mocking

## Coverage Targets

### Minimum Coverage
- **Unit Tests**: 20+ tests
- **Feature Tests**: 20+ tests  
- **Browser Tests**: 3+ critical flows
- **Overall Coverage**: 70% lines, 80% statements

### Critical Path Coverage
- **Accounting Logic**: 95%+ coverage
- **Permission Gates**: 100% coverage
- **Money Calculations**: 100% coverage
- **Inventory Integration**: 90%+ coverage

## Test Execution

### Local Development
```bash
# Run all tests
php artisan test --parallel

# Run specific suites
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
php artisan dusk

# With coverage
php artisan test --coverage
```

### CI Pipeline
- **Trigger**: Push to main, PR creation
- **Matrix**: PHP 8.2, 8.3
- **Services**: MySQL, Redis
- **Artifacts**: Coverage reports, test results

## Known Gaps & Limitations

### Current Gaps
1. No existing test coverage
2. Missing factories for accounting models
3. No browser testing setup
4. No CI pipeline

### Future Enhancements
1. Property-based testing for money calculations
2. Load testing for concurrent users
3. Security testing for permission bypasses
4. Integration testing with external systems

## Success Criteria

### Must Have
- ✅ All accounting invariants enforced by tests
- ✅ Green test suite in CI
- ✅ Permission matrix fully tested
- ✅ Money calculations use integer cents

### Should Have  
- ✅ Performance benchmarks met
- ✅ Browser tests for critical flows
- ✅ Comprehensive error handling tests
- ✅ Multi-tenant isolation verified

### Could Have
- ✅ Property-based testing
- ✅ Load testing
- ✅ Security penetration testing
- ✅ Accessibility testing
