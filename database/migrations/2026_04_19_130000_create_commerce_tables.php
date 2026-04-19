<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('kind');
            $table->string('slug')->unique();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('teaser')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('price_amount')->default(0);
            $table->string('currency', 3)->default('EGP');
            $table->string('thumbnail_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('packages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('billing_cycle_label')->nullable();
            $table->unsignedInteger('lecture_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        Schema::create('books', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('author_name')->nullable();
            $table->unsignedInteger('page_count')->nullable();
            $table->string('cover_badge')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('kind');
            $table->string('status');
            $table->unsignedInteger('subtotal_amount')->default(0);
            $table->unsignedInteger('total_amount')->default(0);
            $table->string('currency', 3)->default('EGP');
            $table->timestamp('placed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_kind');
            $table->string('product_name_snapshot');
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('unit_price_amount')->default(0);
            $table->unsignedInteger('total_price_amount')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('entitlements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->foreignId('granted_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('source');
            $table->string('status')->default('active');
            $table->string('item_name_snapshot');
            $table->unsignedInteger('price_amount')->default(0);
            $table->string('currency', 3)->default('EGP');
            $table->timestamp('granted_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entitlements');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('books');
        Schema::dropIfExists('packages');
        Schema::dropIfExists('products');
    }
};
