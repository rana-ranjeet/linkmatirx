<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertiser_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['advertiser_id', 'website_id']);
            $table->index(['website_id', 'advertiser_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_favorites');
    }
};
