# ERD Summary

## Identity
- `admins` own many `audit_logs` and receive many `roles`/`permissions`.
- `settings` are key/value records grouped by domain.

## Students and CRM
- `students` own profile-like fields plus many `student_status_histories`, `student_assignments`, `orders`, `entitlements`, `exam_attempts`, `forum_threads`, `complaints`, `suggestions`, and `attendance_records`.

## Academic
- `grades` have many `tracks`.
- Future curriculum hierarchy continues through `curriculum_sections`, `lecture_sections`, `lectures`, `reviews`, and `summaries`.

## Commerce
- `products` wrap sellable content.
- `orders` have many `order_items`; successful fulfillment creates `entitlements`.
- Physical book orders later create `shipping_requests` and `financial_transactions`.

## Support and Community
- `forum_threads` have many `forum_messages` and `forum_attachments`.
- `tickets` belong to `ticket_types`, teams, and assignees.

## Centers and Operations
- `educational_centers` contain `educational_groups` and `center_publish_days`.
- `attendance_sessions` have many `attendance_records`.
- `shift_weeks` contain `shift_registrations`; `salary_cycles` contain `salary_lines`.
