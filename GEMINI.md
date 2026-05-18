# Gemini Workspace Instructions

This file contains foundational mandates for all agents working in the Shukran System repository. These instructions take absolute precedence over general defaults.

## Core Stack & Environments

- **Framework:** Laravel 13 (PHP 8.3)
- **Frontend:** Blade, Vite 8, Tailwind CSS, Alpine.js, jQuery DataTables
- **Database:** MySQL/SQLite (refer to `.env`)
- **Testing:** PHPUnit 12
- **Code Style:** Laravel Pint

## Critical Workflows

### 1. Customer Management
- **Models:** `App\Models\Customer`, `App\Models\CustomerPackage`
- **Validation:** Always use FormRequest classes (e.g., `CustomerStoreRequest`).
- **DataTables:** Listing is handled via `App\DataTables\CustomerDataTable`.
- **Forms:** Create/Edit forms share `resources/views/customers/_form.blade.php`.

### 2. Financial & Subscription Logic
- **Wallet:** Customers have a `wallet_balance`. Wallet top-ups can automatically pay off outstanding subscription balances.
- **Packages:** `CustomerPackage` records track subscription history. Do not overwrite existing records; create new ones for changes.
- **Payments:** Tracked in the `payments` table with `incoming`/`outgoing` directions.

### 3. Localization
- **System:** Multi-language (Arabic/English).
- **Strings:** Use `__()` helper for all user-facing text.
- **Files:** `lang/ar.json`, `lang/en.json`.
- **Locale:** Managed via session and `SetLocale` middleware.

## Engineering Standards

- **Surgical Changes:** Only modify what is strictly necessary. Follow existing patterns.
- **Thin Controllers:** Move complex business logic (e.g., payroll calculations, package assignment) into Service classes.
- **Enums:** Use central enums in `app/Enums/` for status values (e.g., `CustomerStatus`, `PackageStatus`).
- **Type Safety:** Use PHP 8.3 type hinting for properties, arguments, and return types.
- **Testing:**
    - Every bug fix or new feature MUST have a test.
    - Use `RefreshDatabase` trait.
    - Prefer `#[Test]` attribute over `/** @test */` docblocks.
    - Keep test helper methods private or move to `TestCase` if shared.

## Operational Commands

- **Setup:** `composer run setup`
- **Development:** `composer run dev` (starts PHP server, queue, and Vite)
- **Tests:** `composer test` or `php artisan test`
- **Linting:** `./vendor/bin/pint`

## Directory Mapping

- **Routes:** `routes/web.php` (Main), `routes/auth.php` (Authentication)
- **Controllers:** `app/Http/Controllers/`
- **Models:** `app/Models/`
- **Migrations:** `database/migrations/`
- **Views:** `resources/views/`
- **DataTables:** `app/DataTables/`
- **Enums:** `app/Enums/`

---
*For architectural suggestions and future roadmap, see `docs/STRUCTURE_NOTES.md`.*
*For detailed domain maps, see `AGENTS.md`.*
