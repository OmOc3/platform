# منصة الإتقان التعليمية

منصة تعليمية عربية أولًا مبنية لأكاديمية مدرس واحد، وتجمع بين:

- موقع عام للتسويق والتعريف بالخدمة
- بوابة طالب
- لوحة إدارة وتشغيل
- كتالوج محتوى أكاديمي
- باقات وكتب وسلة شراء
- ملتقى أسئلة
- مركز أخطاء
- تاريخ حضور السنتر
- شكاوى واقتراحات

## الغرض الحالي

هذا المستودع يمثل إعادة بناء تدريجية لمنصة تعليمية production-grade فوق Laravel 12 مع معمارية `Modular Monolith`، مع الحفاظ على نفس الهيكل القائم داخل:

- `app/Modules`
- `app/Shared`

## المعمارية

- Laravel 12
- Blade + Livewire
- Tailwind CSS
- MySQL 8 / Redis / Horizon
- Laravel Sail كبيئة التطوير المحلية الرسمية
- Roles / Permissions عبر Spatie Permission
- Arabic RTL-first UI

الموديولات الرئيسية الحالية:

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

- إدارة المشرفين والصلاحيات والإعدادات والـ audit logs
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

### لاحقًا

- محاولات الاختبارات ونتائجها الكاملة
- تدفق دفع فعلي وربط settlement/payment gateway
- شحن وتنفيذ طلبات الكتب الكامل
- LMS delivery متقدم
- ticketing/support backend الكامل
- payroll/operations الكامل

## التشغيل المحلي الرسمي

المسار المحلي الرسمي للمشروع هو Laravel Sail مع MySQL وRedis وMailpit. لا يُعتمد على `.runtime` كمسار تشغيل قاعدة بيانات محلي بعد الآن.

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

الاختبارات تعمل على MySQL أيضًا باستخدام قاعدة `testing` التي ينشئها Sail تلقائيًا:

```bash
./vendor/bin/sail artisan test
```

ولو أردت إعادة البناء الكامل لقاعدة البيانات محليًا:

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
