<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publisher_profiles', function (Blueprint $table) {
            $table->unsignedInteger('number_of_sites')->nullable();
            $table->string('do_follow_links', 8)->nullable();
            $table->string('owns_sites', 8)->nullable();
            $table->string('already_selling_articles', 8)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('publisher_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'number_of_sites',
                'do_follow_links',
                'owns_sites',
                'already_selling_articles',
            ]);
        });
    }
};
