<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_schedules', 'description')) {
                $table->text('description')->nullable()->after('milestone_name');
            }
            if (!Schema::hasColumn('payment_schedules', 'percentage')) {
                $table->decimal('percentage', 5, 2)->default(0.00)->after('description');
            }
            if (!Schema::hasColumn('payment_schedules', 'milestone_code')) {
                $table->string('milestone_code', 50)->default('OTHER')->after('percentage');
            }
            if (!Schema::hasColumn('payment_schedules', 'outstanding_amount')) {
                $table->decimal('outstanding_amount', 15, 2)->default(0.00)->after('amount_paid');
            }
            if (!Schema::hasColumn('payment_schedules', 'overdue_days')) {
                $table->integer('overdue_days')->default(0)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_schedules', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'percentage',
                'milestone_code',
                'outstanding_amount',
                'overdue_days',
            ]);
        });
    }
};
