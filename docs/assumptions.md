# Assumptions

## Confirmed Decisions
- Product scope is a single academy brand, not a multi-tenant SaaS.
- Default language is Arabic; English stays optional and secondary.
- Local development baseline is Docker Sail.
- Admin UI uses Laravel Blade-first pages with a path open for Livewire-heavy expansion as dependencies are installed.
- v1 commerce uses separate checkout flows for digital access and shipped books.
- Milestone 0/1 local acceptance uses a portable workspace-local PHP/Composer toolchain with SQLite because host PHP/Docker are not available on `PATH`.

## Uncertain Areas Implemented Flexibly
- Payment gateway is not finalized; checkout services must remain adapter-based.
- Shipping provider and checkout detail flow are not finalized; shipping requests are modeled independently from payment capture.
- Ticket lifecycle defaults to `open`, `assigned`, `waiting_customer`, `waiting_internal`, `resolved`, `closed`.
- “Blocked attempts” will be implemented later as configurable exam rules rather than hardcoded logic.
- Offers, FAQ/help list, and SMS integrations are placeholders until product rules are confirmed.

## Seed and Access Defaults
- Local seed data creates one super admin from environment variables:
  - `PLATFORM_SUPER_ADMIN_EMAIL`
  - `PLATFORM_SUPER_ADMIN_PASSWORD`
- Demo academic seed data includes baseline grades and tracks for secondary education flows.

## Technical Constraints in This Workspace
- The repository started empty and required a fresh Laravel 12 skeleton import.
- The host currently lacks system PHP, Composer, Docker, and MySQL binaries on `PATH`; source scaffolding proceeds regardless, and runtime execution depends on later environment provisioning.

## Near-Term Follow-Up
- Install Composer dependencies and publish package assets/config once PHP is available.
- Run migrations, seeders, and the feature test suite after the PHP runtime is provisioned.
- Keep Sail/MySQL/Redis documentation and config in sync even when local verification uses SQLite first.
