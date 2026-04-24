# Shukran System Reference

This file is a quick workflow map for AI assistants and developers working in this repository. Read it before making changes so the code stays aligned with the current Laravel structure.

## Project Shape

- Framework: Laravel 13, PHP 8.3.
- Frontend: Blade, Vite, Tailwind CSS, Alpine.js, jQuery DataTables.
- Auth scaffolding: Laravel Breeze.
- Main business area today: authenticated customer management.
- Localization: user locale is stored in session through `/locale/{locale}`. Supported locales live in `config/locales.php`; translation strings are in `lang/en.json` and `lang/ar.json`.

## Important Commands

- Install/setup: `composer run setup`
- Start full local dev stack: `composer run dev`
- Frontend only: `npm run dev`
- Build assets: `npm run build`
- Run tests: `composer test`
- Format PHP when needed: `vendor/bin/pint`

## Main Workflow

1. User visits `/` and sees `resources/views/welcome.blade.php`.
2. Authenticated users can access `/dashboard`, profile routes, and customer routes.
3. Customer list route `customers.index` uses `App\DataTables\CustomerDataTable`.
4. Customer create/edit pages share `resources/views/customers/_form.blade.php`.
5. Store/update validation is handled by:
   - `app/Http/Requests/CustomerStoreRequest.php`
   - `app/Http/Requests/CustomerUpdateRequest.php`
6. The controller saves only customer fields through `customerData()`.
7. Customer profile route `customers.show` loads customer relations and renders `resources/views/customers/show.blade.php`.

## Customer Domain

Primary model: `app/Models/Customer.php`

Customer fields include:

- Contact: `first_name`, `last_name`, `email`, `phone`, `address`, `country_id`
- Status and source: `status`, `source`, `customer_type`
- Classification: `level_id`, `category_id`
- Placement: `placement_month`, `tester_id`, `old_instructor_id`
- Ownership: `created_by`
- Notes: `notes`

Customer relationships:

- `creator()` belongs to `User` through `created_by`
- `level()` belongs to `Level`
- `category()` belongs to `Category`
- `country()` belongs to `Country`
- `tester()` belongs to `User` through `tester_id`
- `oldInstructor()` belongs to `User` through `old_instructor_id`
- `customerPackages()` has many `CustomerPackage`

Customer status values are defined in `app/Enums/CustomerStatus.php`:

- `active`
- `inactive`

## Package Domain

Primary models:

- `app/Models/Package.php`
- `app/Models/CustomerPackage.php`

Packages are plan templates. Important fields:

- `name`
- `levels_count`
- `price`
- `status` (`active`, `inactive`)

Customer packages are assigned package records for a specific customer. Important fields:

- `customer_id`
- `package_id`
- `price`
- `discount`
- `final_price`
- `paid_amount`
- `remaining_amount`
- `payment_date`
- `payment_status` (`unpaid`, `partial`, `paid`)
- `start_date`
- `end_date`
- `status` (`active`, `completed`, `cancelled`)
- `created_by`

Important current behavior:

- The customer form has a `package_id` select.
- Both customer request classes validate `package_id`, then remove it in `customerData()`.
- `CustomerController::store()` and `CustomerController::update()` currently create/update only the `customers` table.
- Customer package history is displayed on the customer show page through `customerPackages.package` and `customerPackages.creator`.
- If implementing package assignment from the form, add explicit create/update logic for `CustomerPackage` instead of expecting `Customer::create()` or `Customer::update()` to save `package_id`.

## Reference Data

Reference tables:

- `categories`: supports parent/child categories through `parent_id`
- `levels`: simple name list
- `countries`: loaded from `database/data/countries.json`
- `packages`: active/inactive package templates
- `users`: used for creators, testers, and old instructors

Seeders live in `database/seeders`.

## UI Locations

- App layout: `resources/views/layouts/app.blade.php`
- Navigation/sidebar data: `config/sidebar.php`
- Customer index: `resources/views/customers/index.blade.php`
- Customer create: `resources/views/customers/create.blade.php`
- Customer edit: `resources/views/customers/edit.blade.php`
- Shared customer form: `resources/views/customers/_form.blade.php`
- Customer show/profile: `resources/views/customers/show.blade.php`
- DataTable actions component: `resources/views/components/datatable-actions.blade.php`

## Coding Notes

- Follow existing Laravel conventions: controller gathers form data, request validates, model owns relationships.
- Use FormRequest classes for validation when adding or changing customer inputs.
- Keep enum-like values centralized when possible, similar to `CustomerStatus`.
- Use eager loading in show/index flows when adding relationship data to avoid N+1 queries.
- Keep Arabic/English user-facing strings wrapped in `__()` and update `lang/en.json` and `lang/ar.json` if adding fixed labels.
- Do not remove existing user changes without checking first.

## Common Next Feature Path

To make package selection on the customer form actually create or update a customer package:

1. Decide the assignment behavior:
   - create a new package history row each time the selected package changes, or
   - update the latest active customer package.
2. Add package assignment logic after the customer is saved in `CustomerController::store()` and `CustomerController::update()`.
3. Calculate `price`, `discount`, `final_price`, `paid_amount`, `remaining_amount`, and `payment_status` consistently.
4. Consider a dedicated request method, service, or private controller method if the logic becomes more than a few lines.
5. Add focused tests for store/update behavior.
