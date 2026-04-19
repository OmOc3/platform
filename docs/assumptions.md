# Assumptions

## Confirmed Product Defaults
- المنصة تخص أكاديمية مدرس واحد الآن وليست multi-tenant.
- اللغة الأساسية هي العربية، والإنجليزية تظل ثانوية ويمكن توسيعها لاحقًا.
- بوابة الطالب ولوحة الإدارة تبنيان على Blade-first UI مع إبقاء Livewire جاهزًا للعناصر التفاعلية التي تستحقه.
- الطلاب من حالة `pending` يمكنهم دخول البوابة، لكن الوصول للمحتوى المدفوع يظل معتمدًا على الاستحقاقات الفعلية فقط.

## Commerce and Access Assumptions
- المحتوى الأكاديمي القابل للبيع يربط عبر `products` و`entitlements`.
- `reviews` تم نمذجتها كنوع داخل `lectures` باستخدام `ContentKind` بدل إنشاء موديل منفصل.
- الوصول للمحاضرات يمكن أن يأتي من:
  - شراء مباشر للمحاضرة
  - entitlement ناتج عن باقة تتضمن هذه المحاضرة
  - منحة إدارية
  - محتوى مجاني
- فحص تعارض شراء الباقة مبني حاليًا على تداخل المحاضرات المملوكة فعليًا أو المتاحة عبر باقات مفعلة، ويمكن توسيع القاعدة لاحقًا من `metadata`.
- الدفع الفعلي غير مفعل بعد؛ المرحلة الحالية تنشئ draft orders فقط.
- الكتب تظل منفصلة منطقيًا عن التدفق الرقمي حتى داخل السلة نفسها.

## Forum and Mistakes Assumptions
- المنتدى أقرب إلى Q&A/forum module وليس contact form.
- مرفقات المنتدى تحفظ في جداول مستقلة (`forum_attachments`) مع ملفات على disk `public`.
- moderation الحالية أساس فقط: حالة الموضوع، مستوى الظهور، ورد إداري.
- مركز الأخطاء يعتمد حاليًا على `mistake_items` المجمعة حسب المحاضرة، وليس على `mistake_groups` مستقل.
- بيانات الأخطاء الحالية seeded/demo، لكن الـ schema مصمم ليستقبل ingestion تلقائيًا من exam attempts لاحقًا.

## Technical and Runtime Assumptions
- Sail / MySQL / Redis / Horizon تظل target stack الرسمي.
- التحقق المحلي السريع يستخدم `.runtime` + SQLite.
- بعض المرفقات seeded كمسارات demo فقط لأغراض العرض البنيوي، بينما المرفقات الجديدة المرفوعة من النماذج تحفظ فعليًا.

## Deferred Decisions
- payment gateway النهائي لم يُحسم بعد.
- shipping fulfillment التفصيلي لم يُحسم بعد.
- blocked attempts ومنطق المحاولات الكاملة للاختبارات مؤجلان لمرحلة exams engine.
- ticketing/support backend الكامل وpayroll والعمليات الداخلية الأوسع لم تُنفذ بعد.
