<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->string('milestone_name'); // Booking Deposit, Foundation Completion, 1st Floor Slab, Possession, etc.
            $table->date('due_date');
            $table->decimal('amount_due', 15, 2);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->string('status', 30)->default('Pending'); // Pending, Partially Paid, Paid, Overdue
            $table->decimal('penalty_amount', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['company_id', 'booking_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_schedules');
    }
};
