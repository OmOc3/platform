# Assumptions

## Confirmed Product Defaults
- المنصة تخص أكاديمية مدرس واحد الآن وليست multi-tenant.
- اللغة الأساسية هي العربية، والإنجليزية تظل ثانوية ويمكن توسيعها لاحقًا.
- بوابة الطالب ولوحة الإدارة تبنيان على Blade-first UI مع إبقاء Livewire جاهزًا للعناصر التفاعلية التي تستحقه.
- الطلاب من حالة `pending` يمكنهم دخول البوابة، لكن الوصول للمحتوى المدفوع يظل معتمدًا على الاستحقاقات الفعلية فقط.

## Commerce and Access Assumptions
- المحتوى الأكاديمي القابل للبيع يربط عبر `products` و`entitlements`.
- `reviews` ممثلة كنوع داخل `lectures` باستخدام `ContentKind` بدل موديل منفصل.
- الوصول للمحاضرات والاختبارات يأتي من:
  - محتوى مجاني
  - entitlement مباشر
  - entitlement ناتج عن باقة
  - منحة إدارية
- شراء الباقة يمنح entitlement للباقة نفسها، بينما فتح المحاضرات المدرجة تحتها يعتمد على `AccessResolver`.
- checkout يقسم السلة إلى طلب رقمي وطلب كتب مستقلين، ولا يوجد mixed order موحد في v1.
- v1 الحالي يدعم:
  - payment attempts مرتبطة بالطلبات
  - payment provider abstraction
  - webhook-based confirmation
  - finalization تلقائي للطلبات الرقمية
  - shipment foundation لطلبات الكتب
- المزود الحالي هو `fake` provider فقط حتى يتم ربط PSP حقيقي لاحقًا.
- refund state handling حقيقي ومؤمَّن، لكن refund execution الخارجي ما زال fake/manual-friendly.

## Exam Engine Assumptions
- v1 يدعم فقط objective multiple-choice questions.
- النتيجة تظهر مباشرة بعد الإرسال؛ delayed/manual result release مؤجل.
- الحد الافتراضي للمحاولات هو محاولة واحدة، ويمكن تعديله من `exams.metadata.max_attempts`.
- `exams.question_count` حقل denormalized تتم مزامنته من `exam_questions` ولم يعد يدويًا authoritative.
- عند التصحيح، يتم حفظ snapshot كافٍ داخل `exam_attempt_answers.answer_meta` حتى لا تعتمد شاشة النتيجة بالكامل على التغييرات اللاحقة في بنك الأسئلة.
- إعادة إرسال نفس المحاولة بعد التصحيح تعامل كـ safe no-op.

## Forum and Mistakes Assumptions
- المنتدى أقرب إلى Q&A/forum module وليس contact form.
- مرفقات المنتدى تحفظ في `forum_attachments` مع ملفات على disk `public`.
- moderation الحالية أساس فقط: حالة الموضوع، مستوى الظهور، ورد إداري.
- مركز الأخطاء لا يُستبدل؛ بل يستقبل الآن أخطاء حقيقية من نتائج الامتحانات عبر `mistake_items`.
- مزامنة الأخطاء من الامتحانات idempotent على مستوى `student + exam + question_reference`.

## Technical and Runtime Assumptions
- Sail / MySQL / Redis / Horizon هي بيئة التطوير المحلية والرسمية للمشروع.
- التطبيق محليًا يعمل على قاعدة `platform`.
- الاختبارات تعمل على MySQL باستخدام قاعدة `testing`.
- لا يُعتمد على `.runtime` كمسار تشغيل قاعدة بيانات محلي بعد الآن.
- البيانات التجريبية تتضمن الآن:
  - أمثلة exam attempts فعلية
  - lecture progress examples
  - payment attempts pending
  - digital paid/fulfilled flows
  - book shipment flows
  - attendance sessions قابلة للتحديث من الإدارة مع أمثلة درجات وحضور
- `AttendanceRecorder` أصبح مسؤولًا عن upsert سجلات الحضور والدرجات مع audit logging على تغييرات الإدارة.

## Deferred Decisions
- real payment gateway integration لم يُحسم بعد.
- shipping fulfillment عبر carrier خارجي لم يُنفذ بعد.
- bulk attendance import/export workflows لم تُنفذ بعد.
- advanced proctoring مؤجل.
- essay/manual review workflows مؤجلة.
- advanced exam analytics مؤجلة.
- ticketing/support backend الكامل وpayroll والعمليات الأوسع لم تُنفذ بعد.
