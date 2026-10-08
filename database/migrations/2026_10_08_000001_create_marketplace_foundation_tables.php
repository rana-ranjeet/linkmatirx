<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertiser_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('company_name')->nullable();
            $table->string('phone', 32)->nullable();
            $table->timestamps();
        });

        Schema::create('publisher_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('business_name')->nullable();
            $table->string('phone', 32)->nullable();
            $table->timestamps();
        });

        Schema::create('website_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('website_categories')->nullOnDelete();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->timestamps();
        });

        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publisher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('website_categories')->nullOnDelete();
            $table->string('domain', 253)->unique();
            $table->text('description')->nullable();
            $table->char('country_code', 2)->nullable()->index();
            $table->string('language_code', 10)->nullable()->index();
            $table->string('status', 20)->default('pending')->index();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('guest_post_price_cents')->nullable();
            $table->unsignedBigInteger('link_insertion_price_cents')->nullable();
            $table->char('currency', 3)->default('USD');
            $table->unsignedTinyInteger('domain_rating')->nullable();
            $table->unsignedTinyInteger('domain_authority')->nullable();
            $table->unsignedInteger('organic_traffic')->nullable();
            $table->unsignedInteger('referring_domains')->nullable();
            $table->string('metrics_provider', 100)->nullable();
            $table->timestamp('metrics_updated_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'is_active', 'category_id']);
            $table->index(['guest_post_price_cents', 'link_insertion_price_cents']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('websites');
        Schema::dropIfExists('website_categories');
        Schema::dropIfExists('publisher_profiles');
        Schema::dropIfExists('advertiser_profiles');
    }
};
