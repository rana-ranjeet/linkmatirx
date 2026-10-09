<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 120)->unique();
            $table->timestamps();
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->foreignId('assigned_writer_id')->nullable()->after('author_id')->constrained('users')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->after('assigned_writer_id')->constrained('article_categories')->nullOnDelete();
            $table->text('content_brief')->nullable()->after('excerpt');
            $table->string('target_keyword', 255)->nullable()->after('content');
            $table->string('featured_image_path')->nullable()->after('target_keyword');
            $table->json('internal_links')->nullable()->after('featured_image_path');
            $table->string('content_format', 12)->default('plain')->after('internal_links');
            $table->string('review_note', 2000)->nullable()->after('status');
            $table->string('status', 32)->default('draft')->change();
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_writer_id');
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn([
                'target_keyword',
                'featured_image_path',
                'internal_links',
                'content_format',
                'review_note',
            ]);
            $table->string('status', 16)->default('draft')->change();
        });

        Schema::dropIfExists('article_categories');
    }
};
