<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'payment_number')) {
                $table->string('payment_number', 50)->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('payments', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('booking_id')->constrained('customers')->onDelete('set null');
            }
            if (!Schema::hasColumn('payments', 'payment_mode')) {
                $table->string('payment_mode', 50)->default('Bank Transfer')->after('payment_method');
            }
            if (!Schema::hasColumn('payments', 'bank_cheque_number')) {
                $table->string('bank_cheque_number', 100)->nullable()->after('transaction_reference');
            }
            if (!Schema::hasColumn('payments', 'is_reversed')) {
                $table->boolean('is_reversed')->default(false)->after('status');
            }
            if (!Schema::hasColumn('payments', 'reversal_reason')) {
                $table->text('reversal_reason')->nullable()->after('is_reversed');
            }
            if (!Schema::hasColumn('payments', 'reversed_at')) {
                $table->dateTime('reversed_at')->nullable()->after('reversal_reason');
            }
            if (!Schema::hasColumn('payments', 'reversed_by_user_id')) {
                $table->foreignId('reversed_by_user_id')->nullable()->after('reversed_at')->constrained('users')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'payment_number',
                'customer_id',
                'payment_mode',
                'bank_cheque_number',
                'is_reversed',
                'reversal_reason',
                'reversed_at',
                'reversed_by_user_id',
            ]);
        });
    }
};
