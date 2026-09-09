<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            $table->foreignId('payment_schedule_id')->constrained('payment_schedules')->onDelete('cascade');
            $table->decimal('allocated_amount', 15, 2);
            $table->dateTime('allocated_at');
            $table->timestamps();

            $table->index(['company_id', 'payment_id', 'payment_schedule_id'], 'pay_alloc_comp_pay_sched_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
    }
};
