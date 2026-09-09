<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('offer_number', 50)->unique();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->decimal('original_unit_price', 15, 2);
            $table->decimal('offered_price', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('token_amount_offered', 15, 2)->default(0);
            $table->string('payment_plan_type', 50)->default('Construction Linked'); // Downpayment, Construction Linked, Time Linked
            
            $table->dateTime('valid_until');
            $table->dateTime('unit_lock_expires_at')->nullable();
            $table->string('status', 40)->default('Draft');
            // Draft, Pending Manager Approval, Pending Admin Approval, Approved, Rejected, Countered, Expired, Converted to Booking, Cancelled
            
            $table->text('approval_notes')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'lead_id']);
            $table->index(['company_id', 'unit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
