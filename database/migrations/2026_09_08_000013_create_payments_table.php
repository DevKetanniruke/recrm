<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('payment_schedule_id')->nullable()->constrained('payment_schedules')->onDelete('set null');
            $table->string('receipt_number')->unique();
            $table->decimal('amount_paid', 15, 2);
            $table->date('payment_date');
            $table->string('payment_method', 50)->default('Bank Transfer'); // Bank Transfer, Cheque, UPI, Cash, Credit Card
            $table->string('transaction_reference')->nullable();
            $table->string('status', 30)->default('Verified'); // Pending, Verified, Rejected
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('receipt_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'booking_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
