# منصة الإتقان التعليمية

منصة تعليمية عربية أولًا لأكاديمية مدرس واحد، تجمع بين:

- موقع عام للتسويق والتعريف بالخدمة
- بوابة طالب
- لوحة إدارة وتشغيل
- كتالوج محاضرات ومراجعات واختبارات
- باقات وكتب وسلة شراء
- ملتقى أسئلة
- مركز أخطاء
- حضور ومتابعة سنتر
- شكاوى واقتراحات

## الغرض الحالي

هذا المستودع يمثل إعادة بناء production-grade فوق Laravel 12 مع معمارية `Modular Monolith`، مع الحفاظ على نفس الهيكل داخل:

- `app/Modules`
- `app/Shared`

## المعمارية

- Laravel 12
- Blade + Livewire 3
- Tailwind CSS
- MySQL 8 / Redis / Horizon
- Laravel Sail كبيئة التطوير المحلية الرسمية
- Spatie Permission للأدوار والصلاحيات
- واجهة Arabic RTL-first

الموديولات الحالية:

- `Identity`
- `Academic`
- `Students`
- `Commerce`
- `Centers`
- `Support`
- `Operations`
- `Shared`

## الحالة الحالية

### مكتمل الآن

- إدارة المشرفين والصلاحيات والإعدادات وسجل المراجعة
- الصفوف والمسارات
- الموقع العام والهوية العامة
- تسجيل ودخول الطالب واسترجاع كلمة المرور
- ملف الطالب وسجلات المدفوعات والكتب والحضور والشكاوى
- كتالوج المحاضرات والمراجعات والاختبارات
- كتالوج الباقات والكتب
- السلة وتجهيز draft orders
- إدارة الطلبات من لوحة الإدارة مع انتقالات حالة آمنة
- تفعيل الطلبات الرقمية ومنح الاستحقاقات تلقائيًا للشراء المباشر والباقات
- ملتقى الأسئلة
- مركز الأخطاء
- إدارة أقسام المنهج وأقسام المحاضرات والمحتوى والاختبارات
- إدارة الباقات والكتب
- أساس moderation للمنتدى
- محرك محاولات الاختبارات v1:
  - أسئلة اختيار من متعدد
  - بدء المحاولة واستكمالها وحفظ التقدم
  - إرسال المحاولة والتصحيح التلقائي
  - صفحة نتيجة فورية
  - مزامنة الأخطاء تلقائيًا مع مركز الأخطاء
  - شاشة قراءة لمحاولات الاختبارات داخل الإدارة

### مؤجل لاحقًا

- advanced proctoring
- essay/manual-review workflows
- delayed/manual result release
- advanced exam analytics
- payment gateway integration
- shipping execution
- ticketing/support backend completion
- payroll/operations completion

## التشغيل المحلي الرسمي

المسار المحلي الرسمي للمشروع هو Laravel Sail مع MySQL وRedis وMailpit.

### أول تشغيل

من جذر المشروع:

```bash
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail composer install
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm install
```

### التطوير اليومي

شغّل الخدمات:

```bash
./vendor/bin/sail up -d
```

شغّل Vite:

```bash
./vendor/bin/sail npm run dev
```

لو احتجت shell داخل الحاوية:

```bash
./vendor/bin/sail shell
```

### الاختبارات

```bash
./vendor/bin/sail artisan test
```

ولو أردت إعادة بناء قاعدة البيانات محليًا:

```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

## الحسابات التجريبية

- إدارة: `owner@example.edu` / `password`
- طالب مشترك: `student@example.edu` / `password`
- طالب pending: `pending.student@example.edu` / `password`

## ملاحظات مهمة

- التطبيق محليًا يعمل على قاعدة `platform`.
- الاختبارات تعمل على قاعدة `testing`.
- `.env.example` هو المصدر الرسمي للإعدادات المحلية.

## ملفات التوثيق

- [docs/architecture.md](C:/Users/om894/Documents/PLATFORM/docs/architecture.md)
- [docs/domain-map.md](C:/Users/om894/Documents/PLATFORM/docs/domain-map.md)
- [docs/assumptions.md](C:/Users/om894/Documents/PLATFORM/docs/assumptions.md)
- [docs/erd-summary.md](C:/Users/om894/Documents/PLATFORM/docs/erd-summary.md)
- [docs/milestone-plan.md](C:/Users/om894/Documents/PLATFORM/docs/milestone-plan.md)
- [docs/local-runtime.md](C:/Users/om894/Documents/PLATFORM/docs/local-runtime.md)
