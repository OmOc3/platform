# Architecture Summary

## System Shape
- Single Laravel 12 modular monolith serving three surfaces: public website, student portal, and admin dashboard.
- Arabic-first, RTL-first UI with a shared token system and guard-aware layouts.
- API-ready service contracts for future mobile clients without splitting the codebase early.

## Module Boundaries
- `app/Modules/Identity`: admins, roles/permissions, settings, audit logs, admin auth, dashboard.
- `app/Modules/Students`: students, profiles, statuses, assignments, CRM foundations.
- `app/Modules/Academic`: grades, tracks, curriculum, lectures, exams, question bank.
- `app/Modules/Commerce`: products, packages, books, carts, orders, entitlements, shipping.
- `app/Modules/Support`: complaints, suggestions, tickets, support teams.
- `app/Modules/Centers`: centers, groups, publish days, attendance.
- `app/Modules/Operations`: shifts, payroll, internal reporting.
- `app/Shared`: contracts, base services, navigation, exports, UI utilities.

## Runtime and Infrastructure
- PHP 8.3, MySQL 8, Redis, Horizon, Livewire 3 + Blade, Tailwind CSS.
- Local development targets Docker Sail with MySQL, Redis, and Mailpit.
- Queued work is routed through Redis-backed queues; Horizon supervises async processing.

## Auth and Security
- Separate session guards/providers for `admin` and `student`.
- Policies gate all write operations and sensitive reads.
- Spatie Permission manages roles/permissions for admin users.
- Sensitive changes are logged through the `AuditLogger` contract into `audit_logs`.
- Recoverable master records use soft deletes; histories and transactions remain immutable.

## Application Patterns
- Controllers stay thin and delegate mutations to `Actions`.
- Query/list pages use dedicated `Queries` plus shared table UI primitives.
- Domain services are exposed through contracts such as `AccessResolver`, `CheckoutService`, `EntitlementGrantor`, `ExamAttemptService`, `LectureProgressService`, `PaymentProviderRegistry`, `ShippingFeeCalculator`, `AttendanceRecorder`, `TicketAssignmentService`, and `AuditLogger`.
- All business statuses are enum/config driven; no raw status strings in views.

## UI Composition Rules
- Public site: authoritative warm branding, full-bleed hero, proof-first sections, restrained accent color use.
- Admin shell: dense but readable utility layout, permission-aware sidebar, dashboard widgets, export hooks, search and filters.
- Student shell: mobile-first top navigation, purchase/access states, support entry point, clear empty states.

## Milestone 0 and 1 Scope
- Scaffold docs, routes, Docker/Sail defaults, module skeleton, shared contracts, and RTL layouts.
- Implement admin auth, roles/permissions seeding, settings, grades, tracks, admin management, audit logs, and dashboard widgets.
- Adopt Sail with MySQL as the official local verification and development path.

## Extension Rules
- New domains must live under `app/Modules/<Domain>` and expose policies, actions, and queries before adding controllers.
- New admin pages should reuse the shared table/form components before introducing custom page chrome.
- Integrations with payments, shipping, SMS, or storage must enter through contracts bound in the service container.
- Payment confirmation and shipment progression are modeled as explicit lifecycle actions and not embedded in controllers or Blade.
