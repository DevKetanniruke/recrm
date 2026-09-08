<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('lead_id')->nullable()->constrained('leads')->onDelete('set null');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('pan_number')->nullable();
            $table->string('tax_id')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->string('kyc_status', 30)->default('Pending'); // Pending, Verified, Rejected
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
