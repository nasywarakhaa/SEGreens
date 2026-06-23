<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('payment_provider', 30)->nullable()->after('payment_status_code');
            $table->string('payment_reference', 80)->nullable()->index()->after('payment_provider');
            $table->string('payment_method', 50)->nullable()->after('payment_reference');
            $table->string('payment_channel', 50)->nullable()->after('payment_method');
            $table->string('payment_token', 120)->nullable()->after('payment_channel');
            $table->text('payment_redirect_url')->nullable()->after('payment_token');
            $table->timestamp('paid_at')->nullable()->after('payment_redirect_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['payment_reference']);
            $table->dropColumn([
                'payment_provider',
                'payment_reference',
                'payment_method',
                'payment_channel',
                'payment_token',
                'payment_redirect_url',
                'paid_at',
            ]);
        });
    }
};
