# ERD Summary

## Identity
- `admins` authenticate through the `admin` guard and receive roles/permissions.
- `settings` store grouped platform configuration.
- `audit_logs` store actor, subject, before/after payloads, and request metadata.

## Students
- `students` hold profile, segmentation, ownership, and center hooks.
- `student_status_histories` capture immutable status transitions over time.

## Academic Catalog and LMS
- `grades` have many `tracks`.
- `curriculum_sections` belong to `grades` and optionally `tracks`.
- `lecture_sections` belong to `grades`, optionally `tracks`, and optionally `curriculum_sections`.
- `lectures` belong to `grades`, optionally `tracks`, and may belong to curriculum/lecture sections.
- `lectures.product_id` links sellable digital content to the commerce root.
- `lecture_assets` store delivery resources per lecture.
- `lecture_checkpoints` store ordered learning milestones per lecture.
- `lecture_progress` stores one row per `student + lecture` for resume/completion state.
- `exams` belong to `grades`, optionally `tracks`, and may link to a `lecture`.

## Exam Engine
- `questions` store normalized question prompts, explanations, types, and metadata.
- `question_choices` belong to `questions` and store the available objective answers with one correct choice in v1.
- `exam_questions` connect `exams` to `questions` with ordering and per-question score.
- `exam_attempts` belong to `exams` and `students`, and store lifecycle timestamps, attempt number, score summary, and result metadata.
- `exam_attempt_answers` belong to `exam_attempts` and `questions`, and store the selected answer plus grading snapshots.

## Commerce
- `products` remain the sellable root entity.
- `packages` and `books` extend `products`.
- `package_items` link packages to digital items, currently lectures.
- `carts` and `cart_items` store the current student cart before checkout preparation.
- `orders` and `order_items` store draft, paid, shipping, fulfillment, refund, and history state, with `orders.meta` now carrying checkout/shipping snapshots.
- `payments` store per-order payment attempts, provider references, status, and settlement timestamps.
- `payment_webhook_receipts` store normalized webhook receipts and idempotency keys per provider event.
- `shipments` store structured shipping address snapshots, fee amounts, carrier references, and shipment state for book orders.
- `entitlements` store digital access grants and their sources.

## Center History
- `educational_centers` and `educational_groups` model the offline center structure.
- `attendance_sessions` represent publishable center lessons or exams.
- `attendance_records` represent per-student attendance and score history, and are now the operational source for both admin session recording and student attendance history.

## Support and Forum
- `complaints` unify complaint/suggestion intake using a typed field.
- `forum_threads` store the student-owned topic envelope.
- `forum_messages` store the actual posts and replies with morph authors (`students` or `admins`).
- `forum_attachments` store image/audio metadata per message.

## Mistakes Center
- `mistake_items` belong to a `student` and usually a `lecture`, with optional `exam` linkage.
- Each mistake stores question snapshot text, model/correct answer snapshots, explanation, score loss, and metadata.
- Wrong answers from graded exam attempts sync into `mistake_items` and update existing rows idempotently instead of creating duplicate spam.
