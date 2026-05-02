# Structure Notes

This file captures architecture suggestions to revisit as the system grows.

## Current Direction

- The project has a healthy Laravel shape: routes call controllers, validation lives in FormRequests, relationships live in models, DataTables own listing behavior, and Blade partials are reused for create/edit forms.
- Employee CRUD is especially clean with dedicated requests, enums, views, and focused tests.
- Customer package history is now explicit and should stay that way instead of overwriting financial records silently.

## Recommended Next Improvements

1. Add authorization policies.
   - Add `CustomerPolicy`, `EmployeePolicy`, and later package/payroll policies.
   - Avoid relying on `auth` alone once users have different roles.

2. Move growing business logic into services.
   - Package assignment rules can move from `CustomerController` into a service such as `CustomerPackageAssignmentService`.
   - Payroll calculations should live in a service such as `PayrollCalculator` before production payroll workflows are built.

3. Expand enum coverage.
   - Existing enums are a good start.
   - Consider enums for package status, customer package status, payment status, customer type, gender, payroll status, and adjustment type.

4. Keep controllers thin.
   - Controllers should gather request data, call services when needed, and return responses.
   - Validation stays in FormRequests.
   - Relationship and query behavior stays in models or query-focused classes.

5. Keep destructive delete behavior conservative.
   - Prefer soft deletes, restricted deletes, or nullable foreign keys for customer, employee, payroll, and package history.
   - Avoid cascade deletes for business records unless the data is truly disposable.

6. Watch DataTable complexity.
   - Simple formatted cells are fine in DataTable classes.
   - Move complex repeated HTML into Blade components or partials when the table cells become hard to read.

## Priority Order

1. Policies and roles.
2. Services for package assignment and payroll.
3. More enums for fixed business values.
4. More feature tests around customer packages, employee lifecycle, and payroll.
