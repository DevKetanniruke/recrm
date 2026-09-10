<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('channel_partner_id')->constrained('channel_partners')->onDelete('cascade');
            $table->foreignId('commission_structure_id')->nullable()->constrained('commission_structures')->onDelete('set null');
            $table->decimal('agreement_value', 15, 2);
            $table->decimal('commission_percentage', 5, 2)->default(0.00);
            $table->decimal('calculated_commission_amount', 15, 2);
            $table->decimal('approved_commission_amount', 15, 2)->default(0.00);
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->decimal('balance_amount', 15, 2)->default(0.00);
            $table->string('status', 30)->default('Pending'); // Pending, Eligible, Approved, Payable, Paid, Cancelled
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'channel_partner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
