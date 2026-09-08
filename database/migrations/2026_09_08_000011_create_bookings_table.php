<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('booking_number')->unique();
            $table->foreignId('unit_id')->constrained('units')->onDelete('restrict');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('sales_agent_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('booking_date');
            $table->decimal('agreed_price', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('booking_amount_paid', 15, 2)->default(0);
            $table->string('status', 30)->default('Confirmed'); // Draft, Confirmed, Agreement Signed, Cancelled, Completed
            $table->text('cancellation_reason')->nullable();
            $table->decimal('cancellation_refund_amount', 15, 2)->nullable();
            $table->text('terms_conditions')->nullable();
            $table->string('document_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'booking_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
