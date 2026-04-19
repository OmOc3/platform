# Milestone Plan

## Milestone 0
- Laravel 12 scaffold hardening
- Modular folder structure under `app/Modules` and `app/Shared`
- Project documentation
- Arabic RTL-ready design tokens and reusable layouts
- Sail / MySQL / Redis local development baseline

Status: complete

## Milestone 1
- Admin authentication
- Roles and permissions
- Settings module
- Grades module
- Tracks module
- Admins/managers module
- Audit logs foundation
- Reusable admin list and layout patterns

Status: complete

## Milestone 2
- Public home page foundation
- Student authentication
- Student profile and self-service history pages
- Complaints/suggestions submission
- Student CRM starter in admin

Status: complete

## Milestone 3
- Academic content catalog foundation
- Packages and books catalogs
- Cart and checkout preparation flow
- Forum foundation
- Mistakes center foundation
- Related admin management foundations

Status: complete

## Milestone 4
- Admin order management foundation
- Safe order status transitions
- Digital order fulfillment workflow
- Entitlement propagation from fulfilled digital orders
- Access unlock through direct lecture and package entitlements

Status: complete

## Milestone 5
- Exam questions and exam composition inside admin exam CRUD
- Exam attempts engine
- Student exam-taking flow
- Objective auto-grading
- Immediate results pages
- Automatic mistakes propagation from wrong answers
- Admin visibility into attempts and results

Status: complete

## Runtime and Verification Baseline
- المسار المحلي الرسمي للتشغيل والتحقق هو Sail + MySQL.
- التطبيق يستخدم قاعدة `platform`.
- الاختبارات تستخدم قاعدة `testing`.
- لم يعد `.runtime + SQLite` هو المسار الرسمي للتحقق المحلي.

## Next Milestones
- Payment gateway integration and settlement automation
- Shipping execution and physical order operations
- Essay/manual review and delayed result release
- Advanced exam analytics and richer student performance reporting
- Lecture delivery / LMS completion flows
- Center operations expansion
- Ticketing and support backend completion
- Payroll and operations domains
