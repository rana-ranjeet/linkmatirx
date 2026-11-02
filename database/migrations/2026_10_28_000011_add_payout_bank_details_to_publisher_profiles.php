<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publisher_profiles', function (Blueprint $table) {
            $table->text('payout_account_holder_name')->nullable();
            $table->string('payout_bank_name')->nullable();
            $table->text('payout_account_number')->nullable();
            $table->string('payout_ifsc_code', 11)->nullable();
            $table->string('payout_account_type', 10)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('publisher_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'payout_account_holder_name',
                'payout_bank_name',
                'payout_account_number',
                'payout_ifsc_code',
                'payout_account_type',
            ]);
        });
    }
};
