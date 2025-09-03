# Double Entry Accounting System - Test Plan

## Risk-Based Testing Strategy

### High-Risk Areas (Priority 1)
- **Ledger Math**: Journal entry balancing, trial balance calculations
- **Posting Process**: Draft → Posted → Locked state transitions
- **Reversals**: Negating entries, audit trail preservation
- **Permissions**: Authorization gates for posting and sensitive operations
- **Monetary Precision**: Decimal arithmetic, no float rounding drift

### Medium-Risk Areas (Priority 2)
- **Inventory Integration**: Stock updates with GL entries
- **Multi-entity/Currency**: Data isolation and currency consistency
- **Reporting**: Trial balance, financial statements accuracy
- **Date Ranges**: Period validation and closing
- **API Endpoints**: Authentication, validation, idempotency

### Low-Risk Areas (Priority 3)
- **UI Components**: Basic CRUD operations
- **File Attachments**: Document uploads
- **User Management**: Role assignments
- **Configuration**: System settings

## Accounting Invariants (Non-Negotiable)

### Core Double-Entry Rules
1. **Balanced Entries**: `Σ(debits) == Σ(credits)` for every journal entry
2. **Trial Balance Zero**: Net of all accounts over any period equals zero
3. **Posting Validation**: No entries without valid account, open period, proper currency
4. **Reversal Integrity**: Reversals create exact negating lines + preserve audit trail
5. **Decimal Precision**: Use decimal(15,2) or integer cents, never float
6. **Entity Isolation**: Multi-tenant data separation
7. **Inventory Consistency**: Sales reduce inventory + create COGS entries

## Test Coverage Map

### Domain Models & Services
| Component | Unit Tests | Feature Tests | Browser Tests |
|-----------|------------|---------------|---------------|
| `DeAccount` | Balance calculations | Account CRUD | - |
| `DeAccountTransaction` | Amount validation | Transaction posting | - |
| `DeJournal` | Entry balancing | Journal workflows | Create/Post flow |
| `Posting Service` | Business logic | HTTP endpoints | - |
| `Trial Balance` | Math calculations | Report generation | View reports |
| `Reversals` | Negation logic | Reversal process | - |
| `Permissions` | Policy rules | Access control | Role-based UI |

### API Endpoints
| Endpoint | Authentication | Validation | Business Logic |
|----------|---------------|------------|----------------|
| `POST /journals` | ✓ | Entry balance | Double-entry rules |
| `POST /journals/{id}/post` | ✓ | State validation | Posting workflow |
| `POST /journals/{id}/reverse` | ✓ | Reversal rules | Audit trail |
| `GET /reports/trial-balance` | ✓ | Date ranges | Balance calculation |

### UI Workflows (Dusk)
1. **Journal Entry Flow**: Login → Create balanced entry → Post → Verify
2. **Permission Boundaries**: Limited user cannot access posting
3. **Trial Balance Report**: Navigate to reports → Generate → Verify totals

## Test Environment

### Database
- **Unit Tests**: SQLite in-memory for speed
- **Feature Tests**: SQLite in-memory with `RefreshDatabase`
- **CI Environment**: MySQL/PostgreSQL for production realism

### Test Data
- **Factories**: Realistic but deterministic data
- **Seeders**: Complete chart of accounts for different scenarios
- **Fixtures**: Frozen time, consistent currencies, balanced entries

## Coverage Targets
- **Line Coverage**: 70% minimum
- **Branch Coverage**: 80% for domain logic
- **Critical Path**: 100% for posting, balancing, reversals

## Test Automation Strategy

### Local Development
```bash
php artisan test --parallel          # Fast feedback
php artisan test --coverage         # Coverage analysis
php artisan dusk                    # Browser tests
```

### CI Pipeline
- PHP 8.2 & 8.3 matrix
- Multiple database engines
- Parallel test execution
- Coverage artifact generation
- Nightly browser tests

## Known Gaps & Future Work
- Performance testing under load
- Stress testing with large datasets
- Security penetration testing
- Backup/restore testing
- Multi-currency exchange rate fluctuations

## Success Criteria
- ✅ Green test suite (local + CI)
- ✅ All 7 accounting invariants enforced by tests
- ✅ 20+ Unit tests covering math and business logic
- ✅ 20+ Feature tests covering workflows and APIs
- ✅ 3+ Browser tests for critical user journeys
- ✅ Permission matrix fully tested
- ✅ Zero tolerance for float arithmetic in monetary calculations