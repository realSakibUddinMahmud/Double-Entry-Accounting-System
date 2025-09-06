## Master QA Plan — Double-Entry Accounting System

This document defines the QA strategy, scope, coverage targets, artifacts, and pass/fail gates for the Laravel multi-tenant accounting application in this repository.

### Repository and Framework Inventory

- **Repository root**: `/workspace/Double-Entry-Accounting-System`
- **Framework**: Laravel 12; **PHP**: ^8.2
  - `composer.json` requires:
    - `laravel/framework:^12.0`
    - `php:^8.2`
    - `spatie/laravel-multitenancy:^4.0`
    - `spatie/laravel-permission:^6.18`
    - `barryvdh/laravel-dompdf:^3.1`
    - `livewire/livewire:^3.6`
    - `kalnoy/nestedset:^6.0`
    - `league/flysystem-aws-s3-v3:^3.29`
- **Dev dependencies** (partial): `phpunit/phpunit:^11.5.3`, `mockery/mockery:^1.6`, `barryvdh/laravel-debugbar`, `nunomaduro/collision`, `laravel/pint`, `laravel/sail`.
- **Node build**: `vite`, Tailwind 4, Bootstrap 5, axios (see `package.json`).

### Environment and Database Configuration

- `.env.example` (present) defaults to `DB_CONNECTION=sqlite`, `SESSION_DRIVER=database`, `QUEUE_CONNECTION=database`, Redis and mailer keys.
- `config/database.php` defines connections:
  - `default` → `env('DB_CONNECTION','mysql')`
  - `tenant` (MySQL): uses `DATABASE_URL_TENANT` (to be provided at runtime)
  - `landlord` (MySQL): uses `DATABASE_URL` / `DB_*` keys
  - Also defines `sqlite`, `mysql`, `mariadb`, `pgsql`, `sqlsrv`
- Tenancy config in `config/multitenancy.php`:
  - Tenant finder: `Spatie\Multitenancy\TenantFinder\DomainTenantFinder`
  - Switch task: `Spatie\Multitenancy\Tasks\SwitchTenantDatabaseTask`
  - Tenant connection: `tenant`; Landlord connection: `landlord`

### Migrations, Seeders, Views

- Landlord migrations: `database/migrations/landlord/*` (users, cache, jobs, tenants, permissions, products, audits, user_otps, companies)
- Tenant migrations: `database/migrations/tenant/*` (users, cache, jobs, brands, stores, categories, units, products, taxes, product_store, images, custom_fields, suppliers, customers, purchases, purchase_items, sales, sale_items, stock_adjustments, product_stock_adjustments, companies, audits)
- Seeders: `database/seeders/*` including `DatabaseSeeder.php`, `RolePermissionSeeder.php`, `BrandSeeder.php`, `ProductSeeder.php`, `ProcedureSeeder.php`, `ViewSeeder.php`
- DB Views created by `Database\Seeders\ViewSeeder`:
  - `product_store_stock_view`
  - `store_product_current_cogs_avg`

### Routes and Controllers

- Entry: `routes/web.php` requires admin route files:
  - `routes/admin/*.php`: `home.php`, `store.php`, `brand.php`, `category.php`, `unit.php`, `product.php`, `tax.php`, `additional-field.php`, `permissions.php`, `roles.php`, `users.php`, `profile.php`, `suppliers.php`, `customers.php`, `purchase.php`, `sale.php`, `stock-adjustment.php`, `password-otp.php`, `report.php`, `company.php`, `settings.php`, `activity-log.php`, `extra.php`
- Controllers: `app/Http/Controllers/Admin/*`, `app/Http/Controllers/Auth/*`
- Reports and exports: `routes/admin/report.php` maps to `App\Http\Controllers\Admin\ReportController`
  - Export endpoints (PDF via DomPDF): `report.*.export`
- Embedded accounting package: `packages/Hilinkz/DEAccounting`
  - Service Provider registers routes, migrations, views, Livewire components
  - Route prefix: `/de-accounting/*`; route files include: `account.php`, `fund-transfer.php`, `payment.php`, `income-revenue.php`, `loan-investment.php`, `loan-invreturn.php`, `security-deposit.php`, `expense.php`, `journal.php`, `ledger.php`

### System Under Test (SUT) Map

- Auth & Sessions
  - Routes: `routes/web.php` (`Auth::routes()`), `routes/admin/profile.php`, `routes/admin/password-otp.php`
  - Controllers: `app/Http/Controllers/Auth/*`, `app/Http/Controllers/Admin/ProfileController.php`
  - DB: landlord/tenant users tables; landlord user OTP table
- RBAC/Permissions
  - Routes: `routes/admin/permissions.php`, `routes/admin/roles.php`, `routes/admin/users.php`
  - Controllers: `PermissionController`, `RoleController`, `UserRoleController`
  - DB: permission tables in both landlord and tenant
- Catalog
  - Categories: `routes/admin/category.php`, `CategoryController.php`, tenant migrations for categories
  - Units: `routes/admin/unit.php`, `UnitController.php`, tenant migrations for units
  - Brands: `routes/admin/brand.php`, `BrandController.php`, tenant migrations for brands
  - Products: `routes/admin/product.php`, `ProductController.php`, tenant migrations for products
- Taxes
  - Routes: `routes/admin/tax.php`, `TaxController.php`, tenant migrations for taxes
- Stores
  - Routes: `routes/admin/store.php`, `StoreController.php`, tenant migrations for stores
- Suppliers and Customers
  - Routes: `routes/admin/suppliers.php`, `routes/admin/customers.php`
  - Controllers: `SupplierController.php`, `CustomerController.php`
  - DB: tenant suppliers/customers tables
- Purchases
  - Routes: `routes/admin/purchase.php`, `PurchaseController.php`, `PurchasePaymentController.php`
  - DB: tenant purchases/purchase_items tables
- Sales
  - Routes: `routes/admin/sale.php`, `SaleController.php`, `SalePaymentController.php`
  - DB: tenant sales/sale_items tables
- Stock Adjustments
  - Routes: `routes/admin/stock-adjustment.php`, `StockAdjustmentController.php`
  - DB: tenant stock_adjustments/product_stock_adjustments tables
- Images (polymorphic)
  - DB: tenant images table
- Custom fields (polymorphic)
  - DB: tenant custom_fields tables
- Audits
  - Routes: `routes/admin/activity-log.php`, `ActivityLogController.php`
  - DB: landlord and tenant audits tables
- Reports/Views/Exports
  - Routes: `routes/admin/report.php`, controller: `Admin\ReportController`
  - Views: `resources/views/admin/reports/*`
  - Exports: DomPDF via `PDF::loadView`
  - Views seeded: `product_store_stock_view`, `store_product_current_cogs_avg`
- Accounting package (double entry)
  - Routes under `/de-accounting/*`: accounts, fund-transfers, payments, income-revenues, loan-investments/returns, security-deposits, expenses, journals, ledgers
  - DB: package migrations under `packages/Hilinkz/DEAccounting/migrations/*`

---

## Objectives

- Validate multi-tenant isolation for all CRUD and reporting flows.
- Ensure correctness of inventory math, taxes, and accounting postings across purchases and sales.
- Assert RBAC enforcement and auditability across sensitive actions.
- Verify report accuracy and export integrity (PDF) with deterministic datasets.

## Scope

- Unit tests for pure functions and calculators.
- Feature/Integration tests for HTTP endpoints and DB side effects across modules listed in SUT Map.
- End-to-end browser tests (Laravel Dusk) for critical admin journeys.
- Validation suites (valid/invalid/boundary) for create/update forms and JSON APIs.
- Security tests: RBAC, tenant isolation, input hardening.
- Performance tests with realistic seed volume; profiling of report queries.
- Migration safety: fresh install and idempotent up/down cycles.

## Out of Scope

- External payment gateways or third-party integrations beyond storage drivers.
- Non-functional UI cosmetics not impacting flows or accessibility.

## Risks and Mitigations

- Tenant DB switching misconfiguration (`DATABASE_URL_TENANT`) → Provide explicit `.env` templates and pre-flight migrate checks.
- Report performance with large datasets → Add indexes where needed; set query time budgets; seed scale dataset.
- Precision/rounding issues (taxes/COGS averages) → Boundary tests on decimal precision and currency normalization.
- RBAC matrix drift → Centralize permission seeding and add negative tests for unauthorized access.

## Acceptance Criteria

- All SUT modules have feature tests covering happy path and error cases (>= 80% controller coverage).
- Unit-tested calculators/helpers (> 90% coverage for units).
- E2E journeys pass consistently with screenshots.
- Tenancy isolation tests prove no cross-tenant data visibility or modification.
- Reports’ numeric outputs match expected math in seeded scenarios.
- Migrations: `migrate:fresh` passes; up/down cycles clean without residue.

## Test Taxonomy and Coverage Goals

- Unit
  - Math helpers for tax, totals, inventory conversions, and any pure calculators.
  - Coverage: > 90% lines and branches for units.
- Feature/Integration
  - All `routes/admin/*.php` and `/de-accounting/*` endpoints.
  - Assert HTTP status, JSON or HTML structure, DB state (has/missing), and side-effects (inventory, taxes, audits, permissions).
  - Coverage: target > 80% of controllers and policies.
- E2E (Dusk)
  - Admin login → seed minimal catalog → supplier/customer → one purchase → one sale → open report page → verify rendered values vs DB.
- Validation
  - Valid/invalid/boundary for all forms/APIs: max lengths, negative/zero quantities, extreme decimals, FK violations, duplicate SKUs, etc.
- Security
  - RBAC restrictions: 403 on unauthorized attempts; no DB changes.
  - Tenant isolation: two-tenant setup with strict separation.
  - Input hardening: injection attempts in text fields should be escaped or rejected.
- Performance
  - Seed 100k products, 1M sale_items; time critical queries and exports.
  - Set SLOs (e.g., report queries < 2s for P50, < 5s P95 on test hardware).
- Migration Safety
  - `migrate:fresh` on clean DB and on populated DB snapshots with restores.
  - Verify up/down idempotency.

## Environments and Data

- Runtime DB: MySQL 8 (preferred). SQLite used only for very short unit/feature tests when speed is essential.
- Landlord DB (e.g., `landlord_master`), Tenant DB(s) (e.g., `tenant_demo`).
- Seeders: `DatabaseSeeder`, `RolePermissionSeeder`, `ViewSeeder` executed per environment notes.

## Artifacts

- JUnit XML: `junit.xml`
- Coverage HTML: `storage/coverage/index.html`
- Dusk screenshots/videos: `storage/dusk/`
- Exported PDFs/CSVs (from tests): stored under `storage/app/testing/exports/` (to be configured in tests)
- Seeded SQL dumps for reproducibility: `artifacts/db_dumps/*.sql`
- Final QA report: `docs/qa/QA_REPORT.md` and `docs/qa/QA_REPORT.pdf`

## CI Pass/Fail Gates

- All test suites (Unit, Feature, E2E) must pass.
- Coverage thresholds: Units >= 90%; overall >= 75% (adjustable as suites mature).
- No critical or high-severity defects open.
- Artifacts uploaded on every CI run.

## Execution Plan (Phases Overview)

1) Repository audit (this document) and SUT Map complete.
2) Environment setup (MySQL) and smoke run; prepare `.env` for landlord/tenant.
3) Automated testing foundation: `.env.testing`, coverage config, initial factories/builders.
4) Test case generation and implementation per module (Feature + Unit).
5) E2E tests with Laravel Dusk (critical journeys).
6) Report/Export testing (PDF/CSV), including headers and content checks.
7) Performance and migration safety tests (seed scale + migrate cycles).
8) CI with GitHub Actions for full pipeline and artifact uploads.
9) Final QA report and PDF publication.
10) One full local pipeline run to bundle artifacts in `/artifacts`.

## Preconditions, Steps, and Expectations (Per-Test Template)

Each automated test case will explicitly define:

- Preconditions: data state, tenant context, authenticated role, feature flags.
- Steps: HTTP requests or browser actions with ordered steps.
- Inputs: valid, invalid, and boundary values.
- Expected HTTP status and JSON/HTML response structure.
- Expected DB state: `assertDatabaseHas` / `assertDatabaseMissing` with exact tables and columns.
- Side-effects: inventory counts, tax totals, RBAC checks, audit records, and report/view updates.

## Notes and Assumptions

- Tenant identification is domain-based via `DomainTenantFinder`; tests will prepare domains in landlord `tenants` table, and use appropriate host headers or config to select tenant.
- PDF generation via DomPDF is synchronous; tests will assert headers (`Content-Type: application/pdf`) and non-zero byte responses, plus selected text matching.

