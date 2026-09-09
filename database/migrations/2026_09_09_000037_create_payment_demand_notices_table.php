<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_demand_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('demand_number', 50)->unique();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('cascade');
            $table->foreignId('payment_schedule_id')->nullable()->constrained('payment_schedules')->onDelete('set null');
            $table->date('demand_date');
            $table->date('due_date');
            $table->decimal('demand_amount', 15, 2);
            $table->decimal('penalty_amount', 15, 2)->default(0.00);
            $table->string('status', 30)->default('Sent'); // Draft, Sent, Partially Paid, Paid, Overdue, Cancelled
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'booking_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_demand_notices');
    }
};
