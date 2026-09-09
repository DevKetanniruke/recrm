<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'project_id')) {
                $table->foreignId('project_id')->nullable()->after('customer_id')->constrained('projects')->onDelete('restrict');
            }
            if (!Schema::hasColumn('bookings', 'lead_id')) {
                $table->foreignId('lead_id')->nullable()->after('project_id')->constrained('leads')->onDelete('set null');
            }
            if (!Schema::hasColumn('bookings', 'unit_ids')) {
                $table->json('unit_ids')->nullable()->after('unit_id');
            }
            if (!Schema::hasColumn('bookings', 'quoted_price')) {
                $table->decimal('quoted_price', 15, 2)->default(0)->after('agreed_price');
            }
            if (!Schema::hasColumn('bookings', 'charges')) {
                $table->json('charges')->nullable()->after('quoted_price');
            }
            if (!Schema::hasColumn('bookings', 'payment_mode')) {
                $table->string('payment_mode', 50)->nullable()->after('booking_amount_paid');
            }
            if (!Schema::hasColumn('bookings', 'payment_reference')) {
                $table->string('payment_reference', 100)->nullable()->after('payment_mode');
            }
            if (!Schema::hasColumn('bookings', 'confirmed_at')) {
                $table->dateTime('confirmed_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('bookings', 'cancelled_at')) {
                $table->dateTime('cancelled_at')->nullable()->after('confirmed_at');
            }
            if (!Schema::hasColumn('bookings', 'cancelled_by_user_id')) {
                $table->foreignId('cancelled_by_user_id')->nullable()->after('cancelled_at')->constrained('users')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'project_id',
                'lead_id',
                'unit_ids',
                'quoted_price',
                'charges',
                'payment_mode',
                'payment_reference',
                'confirmed_at',
                'cancelled_at',
                'cancelled_by_user_id',
            ]);
        });
    }
};
