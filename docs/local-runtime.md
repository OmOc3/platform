# Local Development Runtime

## Default Local Stack

المسار المحلي الرسمي للمشروع هو:

- Laravel Sail
- MySQL 8
- Redis
- Mailpit

لا يُعتمد على `.runtime` كمسار تشغيل قاعدة بيانات رسمي بعد الآن.

## First Run

من جذر المشروع:

```bash
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail composer install
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm install
```

## Daily Development

تشغيل الخدمات:

```bash
./vendor/bin/sail up -d
```

تشغيل الواجهة:

```bash
./vendor/bin/sail npm run dev
```

فتح shell داخل الحاوية:

```bash
./vendor/bin/sail shell
```

## Local Verification

إعادة تهيئة قاعدة البيانات:

```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

تشغيل الاختبارات:

```bash
./vendor/bin/sail artisan test
```

## Testing Database

- التطبيق يستخدم قاعدة `platform`
- الاختبارات تستخدم قاعدة `testing`

ويتم إنشاء قاعدة `testing` تلقائيًا عبر Sail.

## Commerce Environment

- `COMMERCE_PAYMENT_PROVIDER=fake`
- `COMMERCE_FAKE_PAYMENT_EXPIRES_MINUTES=30`
- `COMMERCE_SHIPPING_FEE_CAIRO=35`
- `COMMERCE_SHIPPING_FEE_ALEXANDRIA=45`
- `COMMERCE_SHIPPING_FEE_DEFAULT=60`
