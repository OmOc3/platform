<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table): void {
            $table->unsignedInteger('access_period_days')->nullable()->after('lecture_count');
            $table->json('metadata')->nullable()->after('is_featured');
        });

        Schema::table('books', function (Blueprint $table): void {
            $table->unsignedInteger('stock_quantity')->default(25)->after('page_count');
            $table->string('availability_status')->default('in_stock')->after('cover_badge');
            $table->json('metadata')->nullable()->after('availability_status');
        });

        Schema::create('package_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->string('item_type');
            $table->unsignedBigInteger('item_id');
            $table->string('item_name_snapshot')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['item_type', 'item_id']);
        });

        Schema::create('carts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('students')->cascadeOnDelete();
            $table->string('currency', 3)->default('EGP');
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('unit_price_amount')->default(0);
            $table->unsignedInteger('total_price_amount')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['cart_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('package_items');

        Schema::table('books', function (Blueprint $table): void {
            $table->dropColumn(['stock_quantity', 'availability_status', 'metadata']);
        });

        Schema::table('packages', function (Blueprint $table): void {
            $table->dropColumn(['access_period_days', 'metadata']);
        });
    }
};
