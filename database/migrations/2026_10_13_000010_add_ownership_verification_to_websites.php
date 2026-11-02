<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->string('verification_token', 64)->nullable()->unique();
            $table->string('verification_method', 32)->default('html_meta_tag');
            $table->string('verification_status', 20)->default('pending')->index();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('last_verification_attempt_at')->nullable();
            $table->foreignId('verification_override_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verification_override_at')->nullable();
            $table->string('verification_override_reason', 1000)->nullable();
        });

        DB::table('websites')->whereNull('verification_token')->orderBy('id')->chunkById(100, function ($websites) {
            foreach ($websites as $website) {
                DB::table('websites')->where('id', $website->id)->update([
                    'verification_token' => bin2hex(random_bytes(32)),
                ]);
            }
        });

        Schema::table('websites', function (Blueprint $table) {
            $table->string('verification_token', 64)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropConstrainedForeignId('verification_override_by');
            $table->dropUnique(['verification_token']);
            $table->dropIndex(['verification_status']);
            $table->dropColumn([
                'verification_token',
                'verification_method',
                'verification_status',
                'verified_at',
                'last_verification_attempt_at',
                'verification_override_at',
                'verification_override_reason',
            ]);
        });
    }
};
